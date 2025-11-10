<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Review') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Order Information -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-lg mb-2">Order Details</h3>
                        <p class="text-gray-700"><strong>Service:</strong> {{ $review->order->service->title }}</p>
                        <p class="text-gray-700"><strong>Student:</strong> {{ $review->order->student->full_name }}</p>
                        <p class="text-gray-700"><strong>Price:</strong> ${{ number_format($review->order->price, 2) }}</p>
                    </div>

                    @if(!$review->canBeEdited())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800">
                                <strong>Note:</strong> Reviews can only be edited within 24 hours of submission. This review was posted {{ $review->created_at->diffForHumans() }}.
                            </p>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-blue-800">
                                <strong>Time remaining to edit:</strong> {{ 24 - $review->created_at->diffInHours(now()) }} hours
                            </p>
                        </div>
                    @endif

                    <!-- Review Form -->
                    <form method="POST" action="{{ route('reviews.update', $review) }}" x-data="reviewForm()">
                        @csrf
                        @method('PUT')

                        <!-- Rating -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rating <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-2">
                                <template x-for="star in 5" :key="star">
                                    <button
                                        type="button"
                                        @click="rating = star"
                                        class="text-3xl focus:outline-none transition-colors"
                                        :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'"
                                        :disabled="!canEdit"
                                    >
                                        ★
                                    </button>
                                </template>
                                <span x-show="rating > 0" class="ml-2 text-gray-600" x-text="rating + '/5'"></span>
                            </div>
                            <input type="hidden" name="rating" x-model="rating">
                            @error('rating')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Review Text -->
                        <div class="mb-6">
                            <label for="text" class="block text-sm font-medium text-gray-700 mb-2">
                                Your Review (Optional)
                            </label>
                            <textarea
                                id="text"
                                name="text"
                                rows="5"
                                maxlength="1000"
                                x-model="text"
                                :disabled="!canEdit"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Share your experience with this service..."
                            >{{ old('text', $review->text) }}</textarea>
                            <div class="mt-1 flex justify-between text-sm text-gray-500">
                                <span>Maximum 1000 characters</span>
                                <span x-text="text.length + '/1000'"></span>
                            </div>
                            @error('text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <a
                                href="{{ route('orders.show', $review->order) }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                                :disabled="rating === 0 || !canEdit"
                                :class="{ 'opacity-50 cursor-not-allowed': rating === 0 || !canEdit }"
                            >
                                Update Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function reviewForm() {
            return {
                rating: {{ old('rating', $review->rating) }},
                text: '{{ old('text', $review->text) }}',
                canEdit: {{ $review->canBeEdited() ? 'true' : 'false' }}
            }
        }
    </script>
</x-app-layout>
