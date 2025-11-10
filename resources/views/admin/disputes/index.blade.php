<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Dispute Resolution
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.disputes.index') }}" class="flex items-end space-x-4">
                <div class="flex-1">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.disputes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Disputes Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($disputes as $dispute)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('orders.show', $dispute->order) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    #{{ $dispute->order_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $dispute->order->service->title }}</div>
                                <div class="text-sm text-gray-500">${{ number_format($dispute->order->price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dispute->openedBy->first_name }} {{ $dispute->openedBy->last_name }}</div>
                                <div class="text-sm text-gray-500">{{ ucfirst($dispute->openedBy->role) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">{{ $dispute->reason }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $dispute->status === 'open' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $dispute->status === 'resolved' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $dispute->status === 'released' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $dispute->status === 'refunded' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($dispute->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dispute->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.disputes.show', $dispute) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $dispute->isOpen() ? 'Resolve' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No disputes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $disputes->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
