<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
    <a href="{{ route('services.show', $service->slug) }}" class="block">
        <!-- Sample Work Image -->
        @if($service->sampleWorkIsImage())
            <div class="w-full h-48 bg-gray-200 overflow-hidden">
                <img src="{{ $service->sample_work_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-6">
            <!-- Category Badge -->
            <div class="mb-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $service->category->name }}
                </span>
                @if(!$service->is_active)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                        Inactive
                    </span>
                @endif
            </div>

            <!-- Title -->
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                {{ $service->title }}
            </h3>

            <!-- Description -->
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                {{ $service->description }}
            </p>

            <!-- Student Info -->
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold mr-2">
                    {{ $service->student->initials }}
                </div>
                <div class="text-sm">
                    <p class="text-gray-900 font-medium">{{ $service->student->full_name }}</p>
                    @if($service->student->average_rating > 0)
                        <x-rating-stars :rating="$service->student->average_rating" size="sm" :show-number="true" />
                    @endif
                </div>
            </div>

            <!-- Tags -->
            @if($service->tags && count($service->tags) > 0)
                <div class="mb-4 flex flex-wrap gap-1">
                    @foreach(array_slice($service->tags, 0, 3) as $tag)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $tag }}
                        </span>
                    @endforeach
                    @if(count($service->tags) > 3)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            +{{ count($service->tags) - 3 }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Price and Delivery -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div>
                    <span class="text-2xl font-bold text-gray-900">{{ number_format($service->price, 2) }}</span>
                    <span class="text-sm text-gray-600"> ETB</span>
                </div>
                <div class="text-sm text-gray-600">
                    <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $service->delivery_days }} day{{ $service->delivery_days > 1 ? 's' : '' }}
                </div>
            </div>
        </div>
    </a>
</div>
