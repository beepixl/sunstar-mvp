<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'currency_code',
        'exchange_rate',
        'booking_id',
        'created_by',
        'status',
        'invoice_date',
        'due_date',
        'paid_date',
        'amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'payment_method',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'exchange_rate' => 'decimal:10',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    // Currency Helper Methods
    public function getCurrencySymbol(): string
    {
        return $this->currency?->symbol ?? '$';
    }

    public function formatAmount(float $amount): string
    {
        return $this->getCurrencySymbol() . number_format($amount, 2);
    }

    // PDF-safe currency formatting (uses codes for problematic symbols)
    public function formatAmountForPdf(float $amount): string
    {
        // Currencies with symbols that don't render well in DomPDF
        $problematicSymbols = ['₹', '¥', '₩', '₽', '฿'];
        $symbol = $this->getCurrencySymbol();
        
        // If symbol is problematic, use currency code instead
        if (in_array($symbol, $problematicSymbols)) {
            return $this->currency_code . ' ' . number_format($amount, 2);
        }
        
        return $symbol . number_format($amount, 2);
    }

    // Payment Methods
    public function recalculatePayments(): void
    {
        // Refresh the invoice to ensure we have the latest data
        $this->refresh();
     
        
        // Calculate total from payments table
        $totalPaid = $this->payments()->sum('amount');
        
        // Update amount_paid field
        $this->amount_paid = $totalPaid;
        
        // Update status based on payment
        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_date = now();
        } elseif ($totalPaid > 0) {
            $this->status = 'pending'; // Partially paid
        } elseif ($this->due_date < now() && $this->status !== 'paid') {
            $this->status = 'overdue';
        }
        
        // Save to database (without triggering model events to avoid loops)
        $this->saveQuietly();
        
        // Also force update using query builder to ensure database is updated
        \DB::table('invoices')
            ->where('id', $this->id)
            ->update([
                'amount_paid' => $totalPaid,
                'status' => $this->status,
                'paid_date' => $this->paid_date,
                'updated_at' => now(),
            ]);
    }

    public function getRemainingBalance(): float
    {
        $paid = $this->payments()->sum('amount');
        return max($this->total_amount - $paid, 0);
    }

    // PDF Generation Methods
    public function generatePdf(): string
    {
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this]);
        
        // Create directory if it doesn't exist
        $directory = 'invoices/' . now()->year . '/' . now()->month;
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        
        // Generate filename
        $filename = $directory . '/' . $this->invoice_number . '.pdf';
        
        // Save PDF
        Storage::put($filename, $pdf->output());
        
        // Update pdf_path in database
        $this->update(['pdf_path' => $filename]);
        
        return $filename;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdf_path ? Storage::path($this->pdf_path) : null;
    }

    public function downloadPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->pdf_path || !Storage::exists($this->pdf_path)) {
            $this->generatePdf();
        }
        
        return Storage::download($this->pdf_path, $this->invoice_number . '.pdf');
    }

    public function streamPdf()
    {
        if (!$this->pdf_path || !Storage::exists($this->pdf_path)) {
            // Generate on-the-fly if not exists
            $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this]);
            return $pdf->stream($this->invoice_number . '.pdf');
        }
        
        return response()->file(Storage::path($this->pdf_path));
    }

    // Auto-generate invoice number
    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $lastInvoice = static::orderBy('id', 'desc')->first();
                $nextNumber = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 4)) + 1 : 1;
                $invoice->invoice_number = 'INV-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
