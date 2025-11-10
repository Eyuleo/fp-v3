<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Request Revision - Order #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Order Information -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-lg mb-2">Order Details</h3>
                        <p class="text-gray-700"><strong>Service:</strong> {{ $order->service->title }}</p>
                        <p class="text-gray-700"><strong>Student:</strong> {{ $order->student->full_name }}</p>
                        <p class="text-gray-700"><strong>Revisions Used:</strong> {{ $order->revision_count }}/{{ \App\Models\Order::MAX_REVISIONS }}</p>
                    </div>

                    <!-- Info Box -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-800">
                                    Please provide clear and specific feedback about what needs to be revised.
                                    You have {{ \App\Models\Order::MAX_REVISIONS - $order->revision_count }} revision(s) remaining.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Revision Form -->
                    <form method="POST" action="{{ route('orders.request-revision.submit', $order) }}">
                        @csrf

                        <!-- Feedback -->
                        <div class="mb-6">
                            <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">
                                Revision Feedback <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="feedback"
                                name="feedback"
                                rows="8"
                                maxlength="1000"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe what needs to be changed or improved..."
                            >{{ old('feedback') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters. Be specific about what needs to be revised.</p>
                            @error('feedback')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <a
                                href="{{ route('orders.show', $order) }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition"
                            >
                                Request Revision
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
