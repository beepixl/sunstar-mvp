<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync currency exchange rates from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting currency rates sync...');

        try {
            // Using exchangerate-api.io (free tier, no API key required)
            // Alternatively use: https://api.exchangerate-api.com/v4/latest/USD
            $response = Http::timeout(30)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->get('https://open.er-api.com/v6/latest/USD');

            if (!$response->successful()) {
                $this->error('Failed to fetch currency rates from API');
                Log::error('Currency sync failed: ' . $response->body());
                return 1;
            }

            $data = $response->json();

            if (!isset($data['rates'])) {
                $this->error('Invalid API response format');
                return 1;
            }

            $rates = $data['rates'];
            $synced = 0;
            $created = 0;

            // Popular currencies to sync
            $currenciesToSync = [
                'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
                'EUR' => ['name' => 'Euro', 'symbol' => '€'],
                'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
                'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
                'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
                'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
                'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
                'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
                'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$'],
                'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
                'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF'],
                'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
                'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
                'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
                'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
            ];

            foreach ($currenciesToSync as $code => $info) {
                if (!isset($rates[$code])) {
                    $this->warn("Rate not found for {$code}, skipping...");
                    continue;
                }

                $currency = Currency::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $info['name'],
                        'symbol' => $info['symbol'],
                        'exchange_rate' => $rates[$code],
                        'is_active' => true,
                        'last_synced_at' => now(),
                    ]
                );

                if ($currency->wasRecentlyCreated) {
                    $created++;
                    $this->line("✓ Created {$code}: {$rates[$code]}");
                } else {
                    $synced++;
                    $this->line("✓ Updated {$code}: {$rates[$code]}");
                }
            }

            $this->newLine();
            $this->info("✅ Currency sync completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Created', $created],
                    ['Updated', $synced],
                    ['Total', $created + $synced],
                ]
            );

            Log::info("Currency rates synced successfully. Created: {$created}, Updated: {$synced}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error syncing currency rates: ' . $e->getMessage());
            Log::error('Currency sync error: ' . $e->getMessage());
            return 1;
        }
    }
}
