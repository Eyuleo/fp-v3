<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $service->title }}
            </h2>
            @auth
                @if(auth()->user()->id === $service->student_id || auth()->user()->isAdmin())
                    <div class="flex space-x-2">
                        <a href="{{ route('services.edit', $service->slug) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('services.toggle-active', $service->slug) }}" class="inline" id="toggle-service-form">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                    @click="$dispatch('open-confirm-toggle-service')"
                                    class="inline-flex items-center px-4 py-2 {{ $service->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ $service->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <x-confirm-dialog
                            name="toggle-service"
                            :title="$service->is_active ? 'Deactivate Service' : 'Activate Service'"
                            :message="$service->is_active ? 'Are you sure you want to deactivate this service? It will be hidden from search results.' : 'Are you sure you want to activate this service? It will be visible in search results.'"
                            :confirm-text="$service->is_active ? 'Deactivate' : 'Activate'"
                            :confirm-class="$service->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'"
                            on-confirm="document.getElementById('toggle-service-form').submit()" />
                    </div>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <!-- Status Badge -->
                            @if(!$service->is_active)
                                <div class="mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        Inactive
                                    </span>
                                </div>
                            @endif

                            <!-- Category and Tags -->
                            <div class="mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $service->category->name }}
                                </span>
                                @if($service->tags)
                                    @foreach($service->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-2">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Description</h3>
                                <p class="text-gray-700 whitespace-pre-line">{{ $service->description }}</p>
                            </div>

                            <!-- Sample Work -->
                            @if($service->sample_work_path)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Sample Work</h3>
                                    @if($service->sampleWorkIsImage())
                                        <x-image-gallery-modal :images="[['url' => $service->sample_work_url, 'alt' => $service->title . ' sample work', 'caption' => $service->title]]" :title="$service->title">
                                            <div class="mt-2 cursor-pointer group relative">
                                                <img src="{{ $service->sample_work_url }}" alt="{{ $service->title }} sample work" class="max-w-full h-auto rounded-lg shadow-md transition group-hover:opacity-90">
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition bg-black bg-opacity-30 rounded-lg">
                                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </x-image-gallery-modal>
                                    @else
                                        <p class="text-sm text-gray-600">{{ basename($service->sample_work_path) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">File available upon request</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Pricing Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <div class="text-3xl font-bold text-gray-900 mb-2">
                                {{ number_format($service->price, 2) }} ETB
                            </div>
                            <div class="text-sm text-gray-600 mb-4">
                                <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $service->delivery_days }} day{{ $service->delivery_days > 1 ? 's' : '' }} delivery
                            </div>

                            @auth
                                @if(auth()->user()->isClient() && $service->is_active)
                                    <a href="{{ route('orders.create', $service) }}" class="block w-full text-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Order Now
                                    </a>
                                    <a href="{{ route('messages.show', ['user_id' => $service->student_id, 'service_id' => $service->id]) }}" class="block w-full text-center mt-2 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Contact Seller
                                    </a>
                                @elseif(!$service->is_active)
                                    <div class="text-center text-gray-500 text-sm">
                                        This service is currently unavailable
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Login to Order
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Student Info Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">About the Seller</h3>
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold mr-3">
                                    {{ $service->student->initials }}
                                </div>
                                <div>
                                    <a href="{{ route('profile.public', $service->student->id) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                        {{ $service->student->full_name }}
                                    </a>
                                    @if($service->student->university)
                                        <p class="text-sm text-gray-600">{{ $service->student->university }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($service->student->average_rating > 0)
                                <div class="mb-2">
                                    <x-rating-stars :rating="$service->student->average_rating" size="lg" />
                                    <span class="text-sm text-gray-600 ml-1">
                                        ({{ $service->student->review_count }} {{ Str::plural('review', $service->student->review_count) }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
