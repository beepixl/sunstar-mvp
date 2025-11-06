<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Credit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

final class CreditSummaryWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s'; // auto refresh every minute
   

    protected function getStats(): array
    {
        if (! Auth::user()?->hasRole('Admin')) {
            return [];
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $creditsAdded = (float) (Credit::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('transaction_type', 'add')
            ->sum('amount') ?? 0);

        $creditsDeducted = (float) (Credit::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('transaction_type', 'deduct')
            ->sum('amount') ?? 0);

        $netCredits = $creditsAdded - $creditsDeducted;

        return [
            Stat::make('Credits Added (This Month)', '$' . number_format($creditsAdded, 2))
                ->description('All added credits this month')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('success'),

            Stat::make('Credits Deducted (This Month)', '$' . number_format($creditsDeducted, 2))
                ->description('All deductions this month')
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('danger'),

            Stat::make('Net Change', '$' . number_format($netCredits, 2))
                ->description('Added - Deducted')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($netCredits >= 0 ? 'success' : 'danger'),
        ];
    }
}
