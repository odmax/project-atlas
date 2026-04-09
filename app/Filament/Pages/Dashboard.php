<?php

namespace App\Filament\Pages;

use App\Models\Connector;
use App\Models\LinkedAccount;
use App\Models\User;
use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static ?string $title = 'Dashboard';
    
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getStats(): array
    {
        $totalUsers = User::count();
        $totalConnectors = Connector::count();
        $activeConnectors = Connector::where('is_active', true)->count();
        $totalLinkedAccounts = LinkedAccount::count();
        $activeLinkedAccounts = LinkedAccount::where('status', 'active')->count();
        $suspendedLinkedAccounts = LinkedAccount::where('status', 'suspended')->count();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description('All users in the system')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Active Connectors', number_format($activeConnectors))
                ->description($totalConnectors . ' total connectors')
                ->icon('heroicon-o-server')
                ->color('success'),
            Stat::make('Linked Accounts', number_format($activeLinkedAccounts))
                ->description($totalLinkedAccounts . ' total (' . $suspendedLinkedAccounts . ' suspended)')
                ->icon('heroicon-o-link')
                ->color($activeLinkedAccounts > 0 ? 'success' : 'gray'),
        ];
    }

    public function getUserStats()
    {
        $employmentStats = User::select('employment_status', DB::raw('count(*) as count'))
            ->groupBy('employment_status')
            ->pluck('count', 'employment_status')
            ->toArray();

        $lifecycleStats = User::select('lifecycle_status', DB::raw('count(*) as count'))
            ->groupBy('lifecycle_status')
            ->pluck('count', 'lifecycle_status')
            ->toArray();

        return [
            'employment' => $employmentStats,
            'lifecycle' => $lifecycleStats,
        ];
    }

    public function getConnectorStats()
    {
        $connectorTypes = Connector::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return $connectorTypes;
    }

    public function getLinkedAccountStats()
    {
        $byConnector = LinkedAccount::select('connector_id', DB::raw('count(*) as count'))
            ->groupBy('connector_id')
            ->pluck('count', 'connector_id')
            ->toArray();

        $connectorNames = Connector::pluck('name', 'id')->toArray();

        $formattedData = [];
        foreach ($byConnector as $connectorId => $count) {
            $formattedData[$connectorNames[$connectorId] ?? 'Unknown'] = $count;
        }

        return $formattedData;
    }

    public function getSyncActivity()
    {
        $recentActivity = LinkedAccount::orderBy('last_synced_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($account) {
                return [
                    'username' => $account->external_username,
                    'connector' => $account->connector->name ?? 'N/A',
                    'status' => $account->status,
                    'last_synced' => $account->last_synced_at?->diffForHumans() ?? 'Never',
                ];
            });

        return $recentActivity;
    }
}
