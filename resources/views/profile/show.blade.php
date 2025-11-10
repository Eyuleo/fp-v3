<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->full_name }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Profile Header -->
                    <div class="flex items-start gap-6 mb-6">
                        <div class="flex-shrink-0">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="h-32 w-32 rounded-full object-cover border-4 border-gray-200">
                        </div>
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900">{{ $user->full_name }}</h1>

                            @if($user->isStudent())
                                <div class="mt-2 flex items-center gap-4">
                                    @if($user->review_count > 0)
                                        <div class="flex items-center">
                                            <span class="text-2xl font-semibold text-yellow-500">{{ $user->average_rating }}</span>
                                            <svg class="w-6 h-6 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            <span class="ml-2 text-gray-600">({{ $user->review_count }} {{ Str::plural('review', $user->review_count) }})</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500">No reviews yet</span>
                                    @endif
                                </div>
                            @endif

                            @if($user->university)
                                <p class="mt-2 text-gray-600">
                                    <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                    </svg>
                                    {{ $user->university }}
                                </p>
                            @endif

                            @if(auth()->id() === $user->id)
                                <div class="mt-4">
                                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit Profile
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bio Section -->
                    @if($user->bio)
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">About</h2>
                            <p class="text-gray-700 whitespace-pre-line">{{ $user->bio }}</p>
                        </div>
                    @endif

                    @if($user->isStudent())
                        <!-- Portfolio Section -->
                        @if($user->portfolio_paths && count($user->portfolio_paths) > 0)
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-3">Portfolio</h2>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($user->portfolio_paths as $portfolioPath)
                                        @php
                                            $extension = pathinfo($portfolioPath, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                                        @endphp

                                        <div class="border border-gray-200 rounded-lg p-2 hover:shadow-md transition">
                                            @if($isImage)
                                                <a href="{{ asset('storage/' . $portfolioPath) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $portfolioPath) }}" alt="Portfolio item" class="w-full h-32 object-cover rounded">
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $portfolioPath) }}" target="_blank" class="flex flex-col items-center justify-center h-32 bg-gray-50 rounded hover:bg-gray-100">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="mt-2 text-xs text-gray-600 uppercase">{{ $extension }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($user->isStudent())
                        <!-- Reviews Section -->
                        @if($user->relationLoaded('reviewsReceived') && $user->reviewsReceived->count() > 0)
                            <div class="mt-8">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Reviews</h2>
                                <div class="space-y-4">
                                    @foreach($user->reviewsReceived as $review)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center">
                                                    <div class="flex text-yellow-400">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-5 h-5 fill-current text-gray-300" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </div>
                                                <span class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                            </div>
                                            @if($review->text)
                                                <p class="text-gray-700 mt-2">{{ $review->text }}</p>
                                            @endif
                                            @if($review->student_reply)
                                                <div class="mt-3 pl-4 border-l-2 border-blue-500">
                                                    <p class="text-sm font-semibold text-gray-900">Response from {{ $user->first_name }}:</p>
                                                    <p class="text-sm text-gray-700 mt-1">{{ $review->student_reply }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
