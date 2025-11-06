<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

final class ClientDriverStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s'; // auto refresh every minute

    protected function getStats(): array
    {
        if (! Auth::user()?->hasRole('Admin')) {
            return [];
        }

        // Total clients count (excluding soft deleted)
        $totalClients = Client::count();

        // Total drivers count
        $totalDrivers = User::role('Driver')->count();

        // Active clients count (clients where associated user is active)
        $activeClients = Client::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->count();

        // Active drivers count
        $activeDrivers = User::role('Driver')
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Total Clients', number_format($totalClients))
                ->description('All registered clients')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Active Clients', number_format($activeClients))
                ->description('Clients with active accounts')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Total Drivers', number_format($totalDrivers))
                ->description('All registered drivers')
                ->descriptionIcon('heroicon-o-truck')
                ->color('info'),

            Stat::make('Active Drivers', number_format($activeDrivers))
                ->description('Drivers with active accounts')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}

