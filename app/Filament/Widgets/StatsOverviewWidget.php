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
        $totalProperties = Property::where('is_deleted', false)->count();
        $realEstateCount = Property::where('is_deleted', false)->where('asset_type', 'Property')->count();
        $vehicleCount = Property::where('is_deleted', false)->where('asset_type', 'Vehicle')->count();
        
        $activeProperties = Property::where('status', 'Active')->where('is_deleted', false)->count();
        $pendingProperties = Property::where('status', 'Under Review')->where('is_deleted', false)->count();
        $bankAuctionCount = Property::where('is_bank_auction', true)->where('is_deleted', false)->count();
        $totalLeads = BuyerLead::count();

        // Staff productivity today (visible to owner/admins)
        $todayStaffProperties = Property::whereDate('created_at', \Carbon\Carbon::today())
            ->whereNotNull('created_by_user_id')
            ->count();

        return [
            Stat::make('Total Portfolio', number_format($totalProperties))
                ->description("{$realEstateCount} Properties • {$vehicleCount} Vehicles")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([5, 12, 19, 28, 38, 48, $totalProperties ?: 50]),

            Stat::make('Live on Mobile App', number_format($activeProperties))
                ->description($pendingProperties > 0 ? "{$pendingProperties} under review pending approval" : '100% listings active & verified')
                ->descriptionIcon($pendingProperties > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-badge')
                ->color($pendingProperties > 0 ? 'warning' : 'success')
                ->chart([4, 10, 16, 22, 30, 42, $activeProperties ?: 45]),

            Stat::make('Bank Auction Assets', number_format($bankAuctionCount))
                ->description('SARFAESI & Seized Vehicles')
                ->descriptionIcon('heroicon-m-scale')
                ->color('warning')
                ->chart([2, 5, 8, 14, 20, 26, $bankAuctionCount ?: 28]),

            Stat::make('Today Staff Entries', number_format($todayStaffProperties))
                ->description('Attributed to employee login')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart([1, 3, 6, 9, 13, 18, $todayStaffProperties ?: 5]),

            Stat::make('Buyer Inquiries', number_format($totalLeads))
                ->description('Direct In-App Leads Forwarded')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('success')
                ->chart([3, 8, 14, 20, 29, 36, $totalLeads ?: 40]),
        ];
    }
}

