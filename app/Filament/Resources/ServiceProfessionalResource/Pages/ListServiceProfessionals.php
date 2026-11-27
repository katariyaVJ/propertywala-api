<?php

namespace App\Filament\Resources\ServiceProfessionalResource\Pages;

use App\Filament\Resources\ServiceProfessionalResource;
use App\Models\ServiceProfessional;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceProfessionals extends ListRecords
{
    protected static string $resource = ServiceProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Professional')
                ->icon('heroicon-m-user-plus'),
        ];
    }

    public function getTabs(): array
    {
        $pendingCount = ServiceProfessional::where('status', 'Pending')->count();

        return [
            'pending' => Tab::make('Pending Review')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Pending'))
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning'),
            'approved' => Tab::make('Approved & Active')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Approved')),
            'rejected' => Tab::make('Rejected')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Rejected')),
            'all' => Tab::make('All Applications')
                ->icon('heroicon-m-rectangle-stack'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'pending';
    }
}

