<?php

namespace App\Filament\Widgets;

use App\Models\BuyerLead;
use App\Models\Property;
use App\Models\ServiceProfessional;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalProperties = Property::count();
        $activeProperties = Property::where('status', 'Active')->count();
        $pendingProperties = Property::where('status', 'Under Review')->count();
        $pendingExperts = ServiceProfessional::where('status', 'Pending')->count();
        $totalUsers = User::count();
        $totalLeads = BuyerLead::count();

        return [
            Stat::make('Total Properties', $totalProperties)
                ->description($pendingProperties > 0 ? "{$pendingProperties} awaiting admin approval" : 'All listings reviewed')
                ->descriptionIcon($pendingProperties > 0 ? 'heroicon-m-clock' : 'heroicon-m-home-modern')
                ->color($pendingProperties > 0 ? 'warning' : 'primary')
                ->chart([3, 7, 12, 18, 24, 30, $totalProperties ?: 35]),

            Stat::make('Active Listings', $activeProperties)
                ->description('Live on Mobile App')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([2, 6, 10, 15, 20, 26, $activeProperties ?: 30]),

            Stat::make('Total Users', $totalUsers)
                ->description($pendingExperts > 0 ? "{$pendingExperts} expert apps pending" : 'Registered Owners & Buyers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([5, 12, 20, 25, 30, 42, $totalUsers ?: 48]),

            Stat::make('Buyer Inquiries', $totalLeads)
                ->description('Direct Leads Generated')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('warning')
                ->chart([2, 5, 10, 15, 22, 28, $totalLeads ?: 32]),
        ];
    }
}

