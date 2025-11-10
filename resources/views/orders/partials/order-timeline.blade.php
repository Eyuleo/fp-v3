@props(['order'])

<div class="flow-root">
    <ul role="list" class="-mb-8">
        <!-- Order Placed -->
        <li>
            <div class="relative pb-8">
                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                <div class="relative flex space-x-3">
                    <div>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 ring-8 ring-white">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Order Placed</p>
                            <p class="text-sm text-gray-500">Order created and payment received</p>
                        </div>
                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                            <time datetime="{{ $order->created_at }}">{{ $order->created_at->format('M d, Y H:i') }}</time>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <!-- Order Accepted / In Progress -->
        @if($order->isInProgress() || $order->isDelivered() || $order->isRevisionRequested() || $order->isCompleted())
            <li>
                <div class="relative pb-8">
                    @if(!$order->isCompleted())
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Accepted</p>
                                <p class="text-sm text-gray-500">Student accepted the order and started working</p>
                                @if($order->delivery_date)
                                    <p class="text-xs text-gray-500 mt-1">Expected delivery: {{ $order->delivery_date->format('M d, Y') }}</p>
                                @endif
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $order->updated_at }}">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @elseif($order->isPending())
            <li>
                <div class="relative pb-8">
                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-500 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Awaiting Acceptance</p>
                                <p class="text-sm text-gray-500">Waiting for student to accept the order</p>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endif

        <!-- Work Delivered -->
        @if($order->isDelivered() || $order->isRevisionRequested() || $order->isCompleted())
            <li>
                <div class="relative pb-8">
                    @if(!$order->isCompleted())
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-500 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Work Delivered</p>
                                <p class="text-sm text-gray-500">Student submitted the completed work</p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $order->updated_at }}">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endif

        <!-- Revision Requested -->
        @if($order->isRevisionRequested())
            <li>
                <div class="relative pb-8">
                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Revision Requested</p>
                                <p class="text-sm text-gray-500">Client requested changes ({{ $order->revision_count }}/{{ \App\Models\Order::MAX_REVISIONS }})</p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $order->updated_at }}">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endif

        <!-- Order Completed -->
        @if($order->isCompleted())
            <li>
                <div class="relative pb-8">
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Completed</p>
                                <p class="text-sm text-gray-500">Order approved and payment released</p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $order->updated_at }}">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endif

        <!-- Order Cancelled -->
        @if($order->isCancelled())
            <li>
                <div class="relative pb-8">
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500 ring-8 ring-white">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Cancelled</p>
                                <p class="text-sm text-gray-500">{{ $order->cancelled_reason }}</p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $order->updated_at }}">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endif
    </ul>
</div>
