@props(['rating' => 0, 'maxRating' => 5, 'showNumber' => true, 'size' => 'base'])

@php
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = $maxRating - $fullStars - ($hasHalfStar ? 1 : 0);

    $sizeClasses = [
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['base'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <div class="flex {{ $sizeClass }}">
        {{-- Full stars --}}
        @for ($i = 0; $i < $fullStars; $i++)
            <span class="text-yellow-400">★</span>
        @endfor

        {{-- Half star --}}
        @if ($hasHalfStar)
            <span class="relative inline-block">
                <span class="text-gray-300">★</span>
                <span class="absolute inset-0 overflow-hidden text-yellow-400" style="width: 50%;">★</span>
            </span>
        @endif

        {{-- Empty stars --}}
        @for ($i = 0; $i < $emptyStars; $i++)
            <span class="text-gray-300">★</span>
        @endfor
    </div>

    @if ($showNumber)
        <span class="ml-1 text-sm text-gray-600">
            {{ number_format($rating, 1) }}
        </span>
    @endif
</div>
