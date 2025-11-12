<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BookingStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Today's bookings
        $todayBookings = Booking::whereDate('created_at', today())->count();
        $yesterdayBookings = Booking::whereDate('created_at', today()->subDay())->count();
        $todayChange = $yesterdayBookings > 0 
            ? round((($todayBookings - $yesterdayBookings) / $yesterdayBookings) * 100) 
            : 0;

        // Active bookings (in progress)
        $activeBookings = Booking::whereIn('status', ['assigned', 'picked_up', 'in_transit'])->count();
        
        // Pending bookings
        $pendingBookings = Booking::where('status', 'pending')->count();
        
        // Delivered today
        $deliveredToday = Booking::where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();
        
        // This month's bookings
        $thisMonthBookings = Booking::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $lastMonthBookings = Booking::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        $monthChange = $lastMonthBookings > 0 
            ? round((($thisMonthBookings - $lastMonthBookings) / $lastMonthBookings) * 100) 
            : 0;

        // Revenue this month
        $revenueThisMonth = Booking::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        return [
            Stat::make('Today\'s Bookings', $todayBookings)
                ->description($todayChange > 0 ? "{$todayChange}% increase" : ($todayChange < 0 ? "{$todayChange}% decrease" : 'No change'))
                ->descriptionIcon($todayChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayChange > 0 ? 'success' : ($todayChange < 0 ? 'danger' : 'gray'))
                ->chart([7, 3, 4, 5, 6, 3, $todayBookings]),

            Stat::make('Active Deliveries', $activeBookings)
                ->description('In progress right now')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url('/admin/bookings?tableFilters[status][values][0]=assigned&tableFilters[status][values][1]=picked_up&tableFilters[status][values][2]=in_transit'),

            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting assignment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url('/admin/bookings?tableFilters[status][values][0]=pending'),

            Stat::make('Delivered Today', $deliveredToday)
                ->description('Completed deliveries')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('This Month', $thisMonthBookings)
                ->description($monthChange > 0 ? "{$monthChange}% from last month" : ($monthChange < 0 ? "{$monthChange}% from last month" : 'Same as last month'))
                ->descriptionIcon($monthChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthChange > 0 ? 'success' : ($monthChange < 0 ? 'danger' : 'gray')),

            Stat::make('Revenue (This Month)', '$' . number_format($revenueThisMonth, 2))
                ->description('Total bookings value')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
