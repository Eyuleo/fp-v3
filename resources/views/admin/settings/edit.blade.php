<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Platform Settings
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <!-- Commission Rate -->
                <div class="mb-6">
                    <label for="commission_rate" class="block text-sm font-medium text-gray-700 mb-2">
                        Commission Rate <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <input
                            type="number"
                            name="commission_rate"
                            id="commission_rate"
                            step="0.01"
                            min="0"
                            max="1"
                            value="{{ old('commission_rate', $settings['commission_rate']->value ?? 0.15) }}"
                            required
                            class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('commission_rate') border-red-500 @enderror">
                        <span class="ml-2 text-sm text-gray-600">(0.15 = 15%)</span>
                    </div>
                    @error('commission_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Platform commission deducted from student earnings</p>
                </div>

                <!-- Order Timeout Hours -->
                <div class="mb-6">
                    <label for="order_timeout_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Order Timeout (Hours) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="order_timeout_hours"
                        id="order_timeout_hours"
                        min="1"
                        max="168"
                        value="{{ old('order_timeout_hours', $settings['order_timeout_hours']->value ?? 48) }}"
                        required
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('order_timeout_hours') border-red-500 @enderror">
                    @error('order_timeout_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Time before pending orders are auto-cancelled</p>
                </div>

                <!-- Auto Approve Days -->
                <div class="mb-6">
                    <label for="auto_approve_days" class="block text-sm font-medium text-gray-700 mb-2">
                        Auto Approve (Days) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="auto_approve_days"
                        id="auto_approve_days"
                        min="1"
                        max="30"
                        value="{{ old('auto_approve_days', $settings['auto_approve_days']->value ?? 5) }}"
                        required
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('auto_approve_days') border-red-500 @enderror">
                    @error('auto_approve_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Days before delivered orders are auto-approved</p>
                </div>

                <!-- Max Revisions -->
                <div class="mb-6">
                    <label for="max_revisions" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Revisions <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="max_revisions"
                        id="max_revisions"
                        min="0"
                        max="10"
                        value="{{ old('max_revisions', $settings['max_revisions']->value ?? 2) }}"
                        required
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('max_revisions') border-red-500 @enderror">
                    @error('max_revisions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum number of revision requests per order</p>
                </div>

                <!-- Max Portfolio Size -->
                <div class="mb-6">
                    <label for="max_portfolio_size_mb" class="block text-sm font-medium text-gray-700 mb-2">
                        Max Portfolio File Size (MB) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="max_portfolio_size_mb"
                        id="max_portfolio_size_mb"
                        min="1"
                        max="100"
                        value="{{ old('max_portfolio_size_mb', $settings['max_portfolio_size_mb']->value ?? 10) }}"
                        required
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('max_portfolio_size_mb') border-red-500 @enderror">
                    @error('max_portfolio_size_mb')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum file size for portfolio uploads</p>
                </div>

                <!-- Max Attachment Size -->
                <div class="mb-6">
                    <label for="max_attachment_size_mb" class="block text-sm font-medium text-gray-700 mb-2">
                        Max Attachment Size (MB) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="max_attachment_size_mb"
                        id="max_attachment_size_mb"
                        min="1"
                        max="100"
                        value="{{ old('max_attachment_size_mb', $settings['max_attachment_size_mb']->value ?? 25) }}"
                        required
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('max_attachment_size_mb') border-red-500 @enderror">
                    @error('max_attachment_size_mb')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum file size for message attachments</p>
                </div>

                <!-- Warning Notice -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Changing these settings will affect all future orders and operations. Existing orders will continue with their original settings.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Values Info -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-blue-900 mb-3">Current Settings Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-blue-700 font-medium">Commission Rate:</span>
                    <span class="text-blue-900 ml-2">{{ number_format(($settings['commission_rate']->value ?? 0.15) * 100, 1) }}%</span>
                </div>
                <div>
                    <span class="text-blue-700 font-medium">Order Timeout:</span>
                    <span class="text-blue-900 ml-2">{{ $settings['order_timeout_hours']->value ?? 48 }} hours</span>
                </div>
                <div>
                    <span class="text-blue-700 font-medium">Auto Approve:</span>
                    <span class="text-blue-900 ml-2">{{ $settings['auto_approve_days']->value ?? 5 }} days</span>
                </div>
                <div>
                    <span class="text-blue-700 font-medium">Max Revisions:</span>
                    <span class="text-blue-900 ml-2">{{ $settings['max_revisions']->value ?? 2 }}</span>
                </div>
                <div>
                    <span class="text-blue-700 font-medium">Portfolio Size:</span>
                    <span class="text-blue-900 ml-2">{{ $settings['max_portfolio_size_mb']->value ?? 10 }} MB</span>
                </div>
                <div>
                    <span class="text-blue-700 font-medium">Attachment Size:</span>
                    <span class="text-blue-900 ml-2">{{ $settings['max_attachment_size_mb']->value ?? 25 }} MB</span>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
