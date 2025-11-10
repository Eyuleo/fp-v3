<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Create Category
            </h2>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Categories
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Optional description for this category</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
