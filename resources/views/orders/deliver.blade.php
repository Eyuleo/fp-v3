<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Deliver Work - Order #{{ $order->id }}
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
                        <p class="text-gray-700"><strong>Client:</strong> {{ $order->client->full_name }}</p>
                        <p class="text-gray-700"><strong>Delivery Date:</strong> {{ $order->delivery_date->format('M d, Y') }}</p>
                        @if($order->isLate())
                            <p class="text-red-600 font-semibold mt-2">⚠️ This order is past the delivery date</p>
                        @endif
                    </div>

                    <!-- Requirements -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Client Requirements</h3>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-line">{{ $order->requirements }}</p>
                        </div>
                    </div>

                    <!-- Delivery Form -->
                    <form method="POST" action="{{ route('orders.deliver.submit', $order) }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Delivery Note -->
                        <div class="mb-6">
                            <label for="delivery_note" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Note (Optional)
                            </label>
                            <textarea
                                id="delivery_note"
                                name="delivery_note"
                                rows="5"
                                maxlength="1000"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Add any notes or instructions for the client..."
                            >{{ old('delivery_note') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
                            @error('delivery_note')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Files (Optional)
                            </label>
                            <input
                                type="file"
                                id="files"
                                name="files[]"
                                multiple
                                accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                            <p class="mt-1 text-sm text-gray-500">
                                Accepted formats: PDF, DOC, DOCX, ZIP, JPG, PNG. Max 25MB per file.
                            </p>
                            @error('files.*')
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
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                Deliver Work
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
