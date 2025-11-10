<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Analytics Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Date Range Filter -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.analytics') }}" class="flex items-end space-x-4">
                <div class="flex-1">
                    <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="from_date" id="from_date" value="{{ $fromDate }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex-1">
                    <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="{{ $toDate }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Apply
                </button>
            </form>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- GMV -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">GMV</dt>
                            <dd class="text-2xl font-bold text-gray-900">${{ number_format($gmv, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Active Students -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Students</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($activeStudents) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Platform Commission -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Commission</dt>
                            <dd class="text-2xl font-bold text-gray-900">${{ number_format($totalCommission, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- On-time Delivery Rate -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">On-time Delivery Rate</h3>
                <div class="flex items-baseline">
                    <p class="text-3xl font-bold text-gray-900">{{ $onTimeDeliveryRate }}%</p>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $onTimeDeliveryRate }}%"></div>
                </div>
            </div>

            <!-- Dispute Rate -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Dispute Rate</h3>
                <div class="flex items-baseline">
                    <p class="text-3xl font-bold text-gray-900">{{ $disputeRate }}%</p>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ min($disputeRate, 100) }}%"></div>
                </div>
            </div>

            <!-- Order Status Breakdown -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Order Status</h3>
                <div class="space-y-2">
                    @foreach(['completed' => 'Completed', 'in_progress' => 'In Progress', 'pending' => 'Pending', 'cancelled' => 'Cancelled'] as $status => $label)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $label }}:</span>
                            <span class="font-medium text-gray-900">{{ $ordersByStatus[$status] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Performing Students -->
        @if($topStudents->count() > 0)
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performing Students</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Earnings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topStudents as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.users.show', $student) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $student->completed_orders }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($student->total_earnings ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($student->averageRating(), 1) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Recent Orders -->
        @if($recentOrders->count() > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Orders</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit($order->service->title, 30) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->student->first_name }} {{ $order->student->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->client->first_name }} {{ $order->client->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($order->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
