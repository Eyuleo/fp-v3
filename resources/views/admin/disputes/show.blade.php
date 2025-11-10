<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Dispute #{{ $dispute->id }} - Order #{{ $dispute->order_id }}
            </h2>
            <a href="{{ route('admin.disputes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Disputes
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Dispute Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Dispute Status -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dispute Status</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <div class="mt-1">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    {{ $dispute->status === 'open' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $dispute->status === 'resolved' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $dispute->status === 'released' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $dispute->status === 'refunded' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($dispute->status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Opened:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($dispute->resolved_at)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Resolved:</span>
                                <p class="text-sm text-gray-900">{{ $dispute->resolved_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Resolved By:</span>
                                <p class="text-sm text-gray-900">{{ $dispute->resolvedBy->first_name }} {{ $dispute->resolvedBy->last_name }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Parties Involved -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Parties Involved</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Student:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->order->student->first_name }} {{ $dispute->order->student->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $dispute->order->student->email }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Client:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->order->client->first_name }} {{ $dispute->order->client->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $dispute->order->client->email }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Opened By:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->openedBy->first_name }} {{ $dispute->openedBy->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($dispute->openedBy->role) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Information</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Service:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->order->service->title }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Price:</span>
                            <p class="text-sm text-gray-900">${{ number_format($dispute->order->price, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Order Status:</span>
                            <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $dispute->order->status)) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Payment Status:</span>
                            <p class="text-sm text-gray-900">{{ $dispute->order->payment ? ucfirst($dispute->order->payment->status) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evidence and Resolution -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Dispute Reason -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dispute Reason</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $dispute->reason }}</p>
                </div>

                <!-- Order Messages -->
                @if($dispute->order->messages->count() > 0)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Messages</h3>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach($dispute->order->messages as $message)
                                <div class="border-l-4 {{ $message->sender_id === $dispute->order->student_id ? 'border-blue-500' : 'border-green-500' }} pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $message->sender->first_name }} {{ $message->sender->last_name }}
                                            </span>
                                            <span class="text-xs text-gray-500">({{ ucfirst($message->sender->role) }})</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $message->created_at->format('M d, H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1">{{ $message->content }}</p>
                                    @if($message->attachment_path)
                                        <a href="{{ route('messages.download', $message) }}" class="text-xs text-blue-600 hover:text-blue-900 mt-1 inline-block">
                                            📎 Attachment
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Resolution Notes (if resolved) -->
                @if($dispute->resolution_notes)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resolution Notes</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $dispute->resolution_notes }}</p>
                    </div>
                @endif

                <!-- Resolution Form (if open) -->
                @if($dispute->isOpen())
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resolve Dispute</h3>
                        <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                            @csrf

                            <!-- Resolution Type -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Type <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="resolution_type" value="release" required class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Release payment to student (Student wins)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="resolution_type" value="refund" required class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Full refund to client (Client wins)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="resolution_type" value="partial" required class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" x-model="resolutionType">
                                        <span class="ml-2 text-sm text-gray-700">Partial resolution (Split payment)</span>
                                    </label>
                                </div>
                                @error('resolution_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Partial Amount (conditional) -->
                            <div class="mb-4" x-data="{ resolutionType: '' }">
                                <div x-show="resolutionType === 'partial'">
                                    <label for="partial_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Amount to pay student <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="partial_amount"
                                        id="partial_amount"
                                        step="0.01"
                                        min="0"
                                        max="{{ $dispute->order->price }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Order total: ${{ number_format($dispute->order->price, 2) }}</p>
                                    @error('partial_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Resolution Notes -->
                            <div class="mb-6">
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Resolution Notes <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    name="resolution_notes"
                                    id="resolution_notes"
                                    rows="4"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Explain your decision and reasoning..."></textarea>
                                @error('resolution_notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                    Resolve Dispute
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
