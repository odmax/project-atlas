<?php

namespace App\Filament\Widgets;

use App\Models\SyncJob;
use App\Services\SyncJobService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobMonitorWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        try {
            $service = app(SyncJobService::class);
            $stats = $service->getJobStats();
        } catch (\Exception $e) {
            return [
                Stat::make('Failed Jobs', 0)
                    ->description('Jobs that need attention')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('danger'),
                Stat::make('Running', 0)
                    ->description('Currently processing')
                    ->descriptionIcon('heroicon-o-play')
                    ->color('info'),
                Stat::make('Queued for Retry', 0)
                    ->description('Ready to retry')
                    ->descriptionIcon('heroicon-o-arrow-path')
                    ->color('warning'),
                Stat::make('Completed', 0)
                    ->description('Successfully finished')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
            ];
        }

        return [
            Stat::make('Failed Jobs', $stats['failed'])
                ->description('Jobs that need attention')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('Running', $stats['running'])
                ->description('Currently processing')
                ->descriptionIcon('heroicon-o-play')
                ->color('info'),
            Stat::make('Queued for Retry', $stats['queued_for_retry'])
                ->description('Ready to retry')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning'),
            Stat::make('Completed', $stats['completed'])
                ->description('Successfully finished')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
