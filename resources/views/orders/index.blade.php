<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('My Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($orders->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(auth()->user()->isClient())
                                    Get started by ordering a service.
                                @else
                                    You haven't received any orders yet.
                                @endif
                            </p>
                            @if(auth()->user()->isClient())
                                <div class="mt-6">
                                    <a href="{{ route('services.index') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                        Browse Services
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Order
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Service
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            @if(auth()->user()->isStudent())
                                                Client
                                            @else
                                                Student
                                            @endif
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Date
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($orders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                #{{ $order->id }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <a href="{{ route('services.show', $order->service->slug) }}" class="hover:text-blue-600">
                                                    {{ Str::limit($order->service->title, 40) }}
                                                </a>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                @if(auth()->user()->isStudent())
                                                    {{ $order->client->full_name }}
                                                @else
                                                    {{ $order->student->full_name }}
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    @if($order->isPending()) bg-yellow-100 text-yellow-800
                                                    @elseif($order->isInProgress()) bg-blue-100 text-blue-800
                                                    @elseif($order->isDelivered()) bg-purple-100 text-purple-800
                                                    @elseif($order->isRevisionRequested()) bg-orange-100 text-orange-800
                                                    @elseif($order->isCompleted()) bg-green-100 text-green-800
                                                    @elseif($order->isCancelled()) bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                ${{ number_format($order->price, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ $order->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
