<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Place Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Service Summary -->
                    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h3 class="mb-2 text-lg font-semibold">{{ $service->title }}</h3>
                        <p class="mb-2 text-sm text-gray-600">by {{ $service->student->full_name }}</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Delivery Time: {{ $service->delivery_days }} days</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Service Price</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($service->price, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Form -->
                    <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <!-- Requirements -->
                        <div class="mb-6">
                            <label for="requirements" class="mb-2 block text-sm font-medium text-gray-700">
                                Order Requirements <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="requirements"
                                name="requirements"
                                rows="8"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Please provide detailed requirements for your order..."
                                required
                            >{{ old('requirements') }}</textarea>
                            @error('requirements')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Minimum 10 characters, maximum 5000 characters</p>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label for="requirements_file" class="mb-2 block text-sm font-medium text-gray-700">
                                Attach File (Optional)
                            </label>
                            <input
                                type="file"
                                id="requirements_file"
                                name="requirements_file"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                                accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip"
                            >
                            @error('requirements_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Accepted formats: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP (Max 25MB)</p>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <h4 class="mb-3 font-semibold text-gray-900">Price Breakdown</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Service Price</span>
                                    <span class="font-medium">${{ number_format($service->price, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Platform Fee (15%)</span>
                                    <span class="font-medium">${{ number_format($commission, 2) }}</span>
                                </div>
                                <div class="border-t border-gray-300 pt-2">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-900">Total</span>
                                        <span class="text-xl font-bold text-gray-900">${{ number_format($totalPrice, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-gray-500">
                                Your payment will be held in escrow until the order is completed. The student will receive ${{ number_format($service->price - $commission, 2) }} upon successful delivery.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('services.show', $service->slug) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                ← Back to Service
                            </a>
                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    Continue to Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
