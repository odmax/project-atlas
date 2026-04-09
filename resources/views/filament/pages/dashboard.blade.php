<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format(\App\Models\User::count()) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Connectors</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format(\App\Models\Connector::where('is_active', true)->count()) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Linked Accounts</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format(\App\Models\LinkedAccount::count()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- User Employment Status Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Employment Status</h3>
                <div class="space-y-3">
                    @php
                        $employmentStats = \App\Models\User::select('employment_status', \DB::raw('count(*) as count'))
                            ->groupBy('employment_status')
                            ->pluck('count', 'employment_status')
                            ->toArray();
                        $totalUsers = array_sum($employmentStats);
                    @endphp
                    @foreach(['active' => 'Active', 'contractor' => 'Contractor', 'intern' => 'Intern', 'terminated' => 'Terminated'] as $key => $label)
                        @php
                            $count = $employmentStats[$key] ?? 0;
                            $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- User Lifecycle Status Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Lifecycle Status</h3>
                <div class="space-y-3">
                    @php
                        $lifecycleStats = \App\Models\User::select('lifecycle_status', \DB::raw('count(*) as count'))
                            ->groupBy('lifecycle_status')
                            ->pluck('count', 'lifecycle_status')
                            ->toArray();
                        $totalUsers = array_sum($lifecycleStats);
                    @endphp
                    @foreach(['onboarding' => 'Onboarding', 'active' => 'Active', 'offboarding' => 'Offboarding', 'suspended' => 'Suspended'] as $key => $label)
                        @php
                            $count = $lifecycleStats[$key] ?? 0;
                            $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100) : 0;
                            $colors = ['onboarding' => 'blue', 'active' => 'green', 'offboarding' => 'orange', 'suspended' => 'red'];
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-{{ $colors[$key] }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Connectors & Linked Accounts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Connector Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connectors by Type</h3>
                <div class="space-y-3">
                    @php
                        $connectorTypes = \App\Models\Connector::select('type', \DB::raw('count(*) as count'))
                            ->groupBy('type')
                            ->pluck('count', 'type')
                            ->toArray();
                        $totalConnectors = array_sum($connectorTypes);
                    @endphp
                    @forelse($connectorTypes as $type => $count)
                        @php
                            $percentage = $totalConnectors > 0 ? round(($count / $totalConnectors) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400 uppercase">{{ $type }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No connectors configured</p>
                    @endforelse
                </div>
            </div>

            {{-- Linked Accounts by Connector --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Linked Accounts by Connector</h3>
                <div class="space-y-3">
                    @php
                        $byConnector = \App\Models\LinkedAccount::select('connector_id', \DB::raw('count(*) as count'))
                            ->groupBy('connector_id')
                            ->pluck('count', 'connector_id')
                            ->toArray();
                        $connectorNames = \App\Models\Connector::pluck('name', 'id')->toArray();
                        $totalLinked = array_sum($byConnector);
                    @endphp
                    @forelse($byConnector as $connectorId => $count)
                        @php
                            $percentage = $totalLinked > 0 ? round(($count / $totalLinked) * 100) : 0;
                            $name = $connectorNames[$connectorId] ?? 'Unknown';
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">{{ $name }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No linked accounts found</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Activity Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Sync Activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Connector</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Synced</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $recentActivity = \App\Models\LinkedAccount::with('connector')
                                ->orderBy('last_synced_at', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($recentActivity as $account)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $account->external_username }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $account->connector->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$account->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No recent activity
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
