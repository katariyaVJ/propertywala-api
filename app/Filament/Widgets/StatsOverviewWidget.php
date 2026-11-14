<?php

namespace App\Filament\Widgets;

use App\Models\BuyerLead;
use App\Models\ProMembership;
use App\Models\Property;
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
        $totalLeads = BuyerLead::count();
        $totalUsers = User::count();
        $activePro = ProMembership::where('status', 'Active')->count();

        return [
            Stat::make('Total Properties', $totalProperties)
                ->description("{$activeProperties} Published & Active")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary')
                ->chart([3, 7, 12, 18, 24, 30, $totalProperties ?: 35]),

            Stat::make('Buyer Inquiries', $totalLeads)
                ->description('Forwarded to Verified Sellers')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('success')
                ->chart([2, 5, 10, 15, 22, 28, $totalLeads ?: 32]),

            Stat::make('Registered Users', $totalUsers)
                ->description('Owners, Buyers & Verified Agents')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([5, 12, 20, 25, 30, 42, $totalUsers ?: 48]),

            Stat::make('Pro Subscriptions', $activePro)
                ->description('Active Revenue Members')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning')
                ->chart([1, 2, 4, 6, 8, 12, $activePro ?: 15]),
        ];
    }
}

