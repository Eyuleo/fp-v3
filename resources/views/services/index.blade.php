<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Services') }}
            </h2>
            @auth
                @if(auth()->user()->isStudent() && auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create Service
                    </a>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ showFilters: {{ request()->hasAny(['category', 'min_price', 'max_price', 'max_delivery_days', 'min_rating', 'sort']) ? 'true' : 'false' }} }">
                <div class="p-6">
                    <form method="GET" action="{{ route('services.index') }}" class="space-y-4">
                        <!-- Search Bar -->
                        <div class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services..." class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <button type="button"
                                    @click="showFilters = !showFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Search
                            </button>
                        </div>

                        <!-- Filters -->
                        <div x-show="showFilters"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Category Filter -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" id="category" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div>
                                <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Min Price (ETB)</label>
                                <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" step="0.01" min="0" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Max Price (ETB)</label>
                                <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" step="0.01" min="0" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            </div>

                            <!-- Delivery Time -->
                            <div>
                                <label for="max_delivery_days" class="block text-sm font-medium text-gray-700 mb-1">Max Delivery (days)</label>
                                <input type="number" name="max_delivery_days" id="max_delivery_days" value="{{ request('max_delivery_days') }}" min="1" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            </div>
                        </div>

                        <!-- Sort and Rating Filter -->
                        <div x-show="showFilters"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="flex flex-wrap gap-4 items-end">
                            <!-- Minimum Rating -->
                            <div class="flex-1 min-w-[200px]">
                                <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-1">Min Rating</label>
                                <select name="min_rating" id="min_rating" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">Any Rating</option>
                                    <option value="4" {{ request('min_rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                                    <option value="3" {{ request('min_rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                                    <option value="2" {{ request('min_rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="flex-1 min-w-[200px]">
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select name="sort" id="sort" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                                </select>
                            </div>

                            <!-- Clear Filters -->
                            <div>
                                <a href="{{ route('services.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Count -->
            @if($services->total() > 0)
                <div class="mb-4 text-sm text-gray-600">
                    Showing {{ $services->firstItem() }} to {{ $services->lastItem() }} of {{ $services->total() }} results
                </div>
            @endif

            <!-- Services Grid -->
            @if($services->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-lg">No services found matching your criteria.</p>
                        @auth
                            @if(auth()->user()->isStudent())
                                <a href="{{ route('services.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                    Create your first service
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($services as $service)
                        @include('services.partials.service-card', ['service' => $service])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
