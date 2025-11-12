<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillingStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Monthly Revenue (from actual payments this month) - Convert all to USD
        $monthlyPayments = InvoicePayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->with(['invoice', 'invoice.currency'])
            ->get();
        
        $monthlyRevenue = $monthlyPayments->sum(function ($payment) {
            $invoice = $payment->invoice;
            
            // If invoice is already in USD, no conversion needed
            if ($invoice->currency_code === 'USD') {
                return (float)$payment->amount;
            }
            
            // Get exchange rate from invoice, or from currency table if not set properly
            $exchangeRate = (float)$invoice->exchange_rate;
            
            // If exchange rate is 1.0 but currency is not USD, fetch from currency table
            if ($exchangeRate <= 1.0 && $invoice->currency_code !== 'USD') {
                $currency = $invoice->currency;
                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
            }
            
            // To convert TO USD: divide by exchange rate
            // Example: 5000 AUD / 1.5316 = 3264.55 USD
            return $exchangeRate > 0 ? (float)$payment->amount / $exchangeRate : 0;
        });

        // Last month's revenue for comparison - Convert all to USD
        $lastMonthPayments = InvoicePayment::whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->with(['invoice', 'invoice.currency'])
            ->get();
        
        $lastMonthRevenue = $lastMonthPayments->sum(function ($payment) {
            $invoice = $payment->invoice;
            
            if ($invoice->currency_code === 'USD') {
                return (float)$payment->amount;
            }
            
            $exchangeRate = (float)$invoice->exchange_rate;
            
            // If exchange rate is 1.0 but currency is not USD, fetch from currency table
            if ($exchangeRate <= 1.0 && $invoice->currency_code !== 'USD') {
                $currency = $invoice->currency;
                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
            }
            
            return $exchangeRate > 0 ? (float)$payment->amount / $exchangeRate : 0;
        });

        $revenueChange = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) 
            : 0;
        
        $paymentsCount = $monthlyPayments->count();

        // Outstanding Invoices - Convert all to USD
        $outstandingInvoices = Invoice::whereIn('status', ['pending', 'overdue'])
            ->with('currency')
            ->get();
        
        $outstandingAmount = $outstandingInvoices->sum(function ($invoice) {
            // Calculate remaining balance
            $remaining = (float)$invoice->total_amount - (float)$invoice->amount_paid;
            
            // If USD, no conversion needed
            if ($invoice->currency_code === 'USD') {
                return $remaining;
            }
            
            // Get exchange rate
            $exchangeRate = (float)$invoice->exchange_rate;
            
            // If exchange rate is 1.0 but currency is not USD, fetch from currency table
            if ($exchangeRate <= 1.0 && $invoice->currency_code !== 'USD') {
                $currency = $invoice->currency;
                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
            }
            
            return $exchangeRate > 0 ? $remaining / $exchangeRate : 0;
        });
        
        $pendingCount = $outstandingInvoices->count();

        // Total Invoices (ALL TIME) - Convert to USD
        $allInvoices = Invoice::with('currency')->get();
        $totalInvoicesAmount = $allInvoices->sum(function ($invoice) {
            if ($invoice->currency_code === 'USD') {
                return (float)$invoice->total_amount;
            }
            
            $exchangeRate = (float)$invoice->exchange_rate;
            if ($exchangeRate <= 1.0) {
                $currency = $invoice->currency;
                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
            }
            
            return $exchangeRate > 0 ? (float)$invoice->total_amount / $exchangeRate : 0;
        });

        $totalCount = $allInvoices->count();

        // Paid Amount (using payment records from this month)
        $paidAmount = $monthlyRevenue; // Already calculated from payments
        $paidCount = $paymentsCount; // Already calculated
        $paidChange = $revenueChange; // Already calculated

        return [
            Stat::make('Total', '$' . number_format((float)$totalInvoicesAmount, 2) . ' USD')
                ->description("{$totalCount} invoices (all time)")
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([1, 3, 5, 8, 12, 15, $totalCount])
                ->color('info'),

            Stat::make('Paid', '$' . number_format((float)$paidAmount, 2) . ' USD')
                ->description($paidChange >= 0 
                    ? "+{$paidChange}% from last month ({$paidCount} payments this month)" 
                    : "{$paidChange}% from last month ({$paidCount} payments this month)")
                ->descriptionIcon($paidChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([3, 5, 8, 10, 12, 15, $paidCount])
                ->color('success'),

            Stat::make('Pending', '$' . number_format((float)$outstandingAmount, 2) . ' USD')
                ->description("{$pendingCount} invoices awaiting payment")
                ->descriptionIcon('heroicon-m-clock')
                ->chart([10, 8, 7, 6, 5, 4, $pendingCount])
                ->color('warning'),
        ];
    }
}

