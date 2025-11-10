<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Order #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        currentStatus: '{{ $order->status }}',
        polling: true,
        init() {
            // Poll order status every 10 seconds
            setInterval(() => {
                if (this.polling && !['completed', 'cancelled'].includes(this.currentStatus)) {
                    this.checkOrderStatus();
                }
            }, 10000);
        },
        async checkOrderStatus() {
            try {
                const response = await fetch('{{ route('orders.status', $order) }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.status !== this.currentStatus) {
                    // Status changed, reload page to show updates
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error checking order status:', error);
            }
        }
    }">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($order->isPending() && !$order->payment)
                <div class="mb-4 rounded-md bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">Payment is being processed. Please wait a moment...</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Status Card -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                                    @if($order->isPending()) bg-yellow-100 text-yellow-800
                                    @elseif($order->isInProgress()) bg-blue-100 text-blue-800
                                    @elseif($order->isDelivered()) bg-purple-100 text-purple-800
                                    @elseif($order->isRevisionRequested()) bg-orange-100 text-orange-800
                                    @elseif($order->isCompleted()) bg-green-100 text-green-800
                                    @elseif($order->isCancelled()) bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>

                            @include('orders.partials.order-timeline', ['order' => $order])
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Service Details</h3>
                            <div class="flex items-start space-x-4">
                                <div class="flex-1">
                                    <a href="{{ route('services.show', $order->service->slug) }}" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                        {{ $order->service->title }}
                                    </a>
                                    <p class="mt-1 text-sm text-gray-600">{{ $order->service->category->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-900">${{ number_format($order->price, 2) }}</p>
                                    <p class="text-xs text-gray-500">Service price</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Requirements</h3>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="whitespace-pre-line text-sm text-gray-700">{{ $order->requirements }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Activity & Deliveries -->
                    @if($order->messages->count() > 0)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Activity</h3>
                                <div class="space-y-4">
                                    @foreach($order->messages->sortBy('created_at') as $message)
                                        @php
                                            $isDelivery = str_contains($message->content, 'delivered') || str_contains($message->content, 'Delivery file');
                                            $isRevision = str_contains($message->content, 'Revision requested');
                                            $isRequirement = str_contains($message->content, 'Requirements file');
                                        @endphp
                                        <div class="rounded-lg border p-4 {{ $isDelivery ? 'bg-green-50 border-green-200' : ($isRevision ? 'bg-orange-50 border-orange-200' : ($isRequirement ? 'bg-purple-50 border-purple-200' : 'bg-gray-50 border-gray-200')) }}">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $message->sender_id === $order->student_id ? 'bg-blue-600' : 'bg-gray-600' }} text-xs font-semibold text-white">
                                                        {{ $message->sender->initials }}
                                                    </div>
                                                    <div class="ml-2">
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            {{ $message->sender->full_name }}
                                                            @if($message->sender_id === $order->student_id)
                                                                <span class="text-xs text-gray-500">(Student)</span>
                                                            @else
                                                                <span class="text-xs text-gray-500">(Client)</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-xs text-gray-500">{{ $message->created_at->format('M d, Y H:i') }}</p>
                                                    </div>
                                                </div>
                                                @if($isDelivery)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                        📦 Delivery
                                                    </span>
                                                @elseif($isRevision)
                                                    <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">
                                                        🔄 Revision Request
                                                    </span>
                                                @elseif($isRequirement)
                                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                                        📋 Requirements
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $message->content }}</p>
                                            @if($message->attachment_path)
                                                <div class="mt-3 pt-3 border-t {{ $isDelivery ? 'border-green-200' : ($isRevision ? 'border-orange-200' : ($isRequirement ? 'border-purple-200' : 'border-gray-200')) }}">
                                                    <a href="{{ route('messages.download', $message) }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        Download File
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    @if(!$order->isCancelled() && !$order->isCompleted())
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-semibold text-gray-900">Actions</h3>
                                <div class="flex flex-wrap gap-3">
                                    @if($order->isPending() && auth()->id() === $order->student_id)
                                        <form method="POST" action="{{ route('orders.accept', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                                Accept Order
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('orders.decline', $order) }}" class="inline" id="decline-order-form">
                                            @csrf
                                            <button type="button"
                                                    @click="$dispatch('open-confirm-decline-order')"
                                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                                Decline Order
                                            </button>
                                        </form>
                                    @endif

                                    @if(($order->isInProgress() || $order->isRevisionRequested()) && auth()->id() === $order->student_id)
                                        <a href="{{ route('orders.deliver', $order) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                            Deliver Work
                                        </a>
                                    @endif

                                    @if($order->isDelivered() && auth()->id() === $order->client_id)
                                        <form method="POST" action="{{ route('orders.approve', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                                Approve & Complete
                                            </button>
                                        </form>
                                        @if($order->canRequestRevision())
                                            <a href="{{ route('orders.request-revision', $order) }}" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                                                Request Revision ({{ $order->revision_count }}/{{ \App\Models\Order::MAX_REVISIONS }})
                                            </a>
                                        @endif
                                    @endif

                                    @if($order->isPending() && auth()->id() === $order->client_id)
                                        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="inline" id="cancel-order-form">
                                            @csrf
                                            <button type="button"
                                                    @click="$dispatch('open-confirm-cancel-order')"
                                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                                Cancel Order
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Review Prompt -->
                    @if($order->isCompleted() && auth()->id() === $order->client_id && !$order->review)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border-2 border-blue-200">
                            <div class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">How was your experience?</h3>
                                        <p class="mt-1 text-sm text-gray-600">Share your feedback to help other clients and support the student community.</p>
                                        <div class="mt-4">
                                            <a href="{{ route('reviews.create', $order) }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                                Write a Review
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Display Review if exists -->
                    @if($order->review)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-semibold text-gray-900">Review</h3>
                                <div class="space-y-4">
                                    <!-- Client Review -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                                                    {{ $order->review->reviewer->initials }}
                                                </div>
                                                <div class="ml-3">
                                                    <p class="font-medium text-gray-900">{{ $order->review->reviewer->full_name }}</p>
                                                    <div class="flex items-center">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <span class="text-lg {{ $i <= $order->review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                        @endfor
                                                        <span class="ml-2 text-sm text-gray-600">{{ $order->review->rating }}/5</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $order->review->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if($order->review->text)
                                            <p class="mt-2 text-sm text-gray-700 pl-13">{{ $order->review->text }}</p>
                                        @endif
                                        @if(auth()->id() === $order->review->reviewer_id && $order->review->canBeEdited())
                                            <div class="mt-2 pl-13">
                                                <a href="{{ route('reviews.edit', $order->review) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                                    Edit Review ({{ $order->review->created_at->diffInHours(now()) }}h remaining)
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Student Reply -->
                                    @if($order->review->student_reply)
                                        <div class="ml-8 pl-4 border-l-2 border-gray-200">
                                            <div class="flex items-center mb-2">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-xs font-semibold text-white">
                                                    {{ $order->student->initials }}
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-sm font-medium text-gray-900">{{ $order->student->full_name }} <span class="text-gray-500">(Student Reply)</span></p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700 pl-10">{{ $order->review->student_reply }}</p>
                                        </div>
                                    @elseif(auth()->id() === $order->student_id)
                                        <div class="ml-8 pl-4 border-l-2 border-gray-200">
                                            <form method="POST" action="{{ route('reviews.reply', $order->review) }}">
                                                @csrf
                                                <label for="student_reply" class="block text-sm font-medium text-gray-700 mb-2">Reply to this review</label>
                                                <textarea
                                                    id="student_reply"
                                                    name="student_reply"
                                                    rows="3"
                                                    maxlength="1000"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                    placeholder="Share your response..."
                                                    required
                                                ></textarea>
                                                <div class="mt-2">
                                                    <button type="submit" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700">
                                                        Post Reply
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Participants -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Participants</h3>

                            <!-- Student -->
                            <div class="mb-4">
                                <p class="mb-2 text-xs font-medium uppercase text-gray-500">Student</p>
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                                        {{ $order->student->initials }}
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('profile.public', $order->student->id) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                            {{ $order->student->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ $order->student->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Client -->
                            <div>
                                <p class="mb-2 text-xs font-medium uppercase text-gray-500">Client</p>
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600 text-sm font-semibold text-white">
                                        {{ $order->client->initials }}
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('profile.public', $order->client->id) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                            {{ $order->client->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ $order->client->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs font-medium uppercase text-gray-500">Order ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">#{{ $order->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($order->delivery_date)
                                    <div>
                                        <dt class="text-xs font-medium uppercase text-gray-500">Expected Delivery</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $order->delivery_date->format('M d, Y') }}</dd>
                                        @if($order->isLate())
                                            <span class="mt-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                Late
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-xs font-medium uppercase text-gray-500">Price</dt>
                                    <dd class="mt-1 text-sm text-gray-900">${{ number_format($order->price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase text-gray-500">Commission</dt>
                                    <dd class="mt-1 text-sm text-gray-900">${{ number_format($order->commission, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase text-gray-500">Student Earnings</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">${{ number_format($order->net_amount, 2) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Dialogs -->
    @if($order->isPending() && auth()->id() === $order->student_id)
        <x-confirm-dialog
            name="decline-order"
            title="Decline Order"
            message="Are you sure you want to decline this order? The client will receive a full refund."
            confirm-text="Decline Order"
            on-confirm="document.getElementById('decline-order-form').submit()" />
    @endif

    @if($order->isPending() && auth()->id() === $order->client_id)
        <x-confirm-dialog
            name="cancel-order"
            title="Cancel Order"
            message="Are you sure you want to cancel this order? You will receive a full refund."
            confirm-text="Cancel Order"
            on-confirm="document.getElementById('cancel-order-form').submit()" />
    @endif
</x-app-layout>
