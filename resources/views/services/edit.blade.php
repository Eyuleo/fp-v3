<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('services.update', $service->slug) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Service Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $service->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="5" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" required>{{ old('description', $service->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!-- Tags -->
                        <div class="mb-4">
                            <x-input-label for="tags" :value="__('Tags (comma-separated)')" />
                            <x-text-input id="tags" class="block mt-1 w-full" type="text" name="tags" :value="old('tags', is_array($service->tags) ? implode(', ', $service->tags) : '')" placeholder="e.g., web design, logo, branding" />
                            <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Separate tags with commas</p>
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price" :value="__('Price (ETB)')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" min="0" name="price" :value="old('price', $service->price)" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Delivery Days -->
                        <div class="mb-4">
                            <x-input-label for="delivery_days" :value="__('Delivery Time (days)')" />
                            <x-text-input id="delivery_days" class="block mt-1 w-full" type="number" min="1" max="365" name="delivery_days" :value="old('delivery_days', $service->delivery_days)" required />
                            <x-input-error :messages="$errors->get('delivery_days')" class="mt-2" />
                        </div>

                        <!-- Sample Work -->
                        <div class="mb-4">
                            <x-input-label for="sample_work" :value="__('Sample Work (optional)')" />
                            @if($service->sample_work_path)
                                <p class="text-sm text-gray-600 mb-2">Current file: {{ basename($service->sample_work_path) }}</p>
                            @endif
                            <input id="sample_work" type="file" name="sample_work" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip" />
                            <x-input-error :messages="$errors->get('sample_work')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Max 10MB. Accepted formats: PDF, DOC, DOCX, JPG, PNG, ZIP</p>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('services.show', $service->slug) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Service') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
