@props([
    'name',
    'label' => null,
    'maxlength' => null,
    'rows' => 4,
    'required' => false,
    'placeholder' => ''
])

<div x-data="{
    content: '{{ old($name, $slot ?? '') }}',
    get characterCount() {
        return this.content.length;
    },
    get remaining() {
        return {{ $maxlength ?? 0 }} - this.characterCount;
    },
    get isNearLimit() {
        return {{ $maxlength ? 'this.remaining <= 50 && this.remaining > 0' : 'false' }};
    },
    get isOverLimit() {
        return {{ $maxlength ? 'this.remaining < 0' : 'false' }};
    }
}"
class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        x-model="content"
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($required) required @endif
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500']) }}
    >{{ old($name, $slot ?? '') }}</textarea>

    <div class="flex justify-between items-center text-xs">
        @error($name)
            <p class="text-red-600">{{ $message }}</p>
        @else
            <div></div>
        @enderror

        @if($maxlength)
            <div :class="{
                'text-gray-500': !isNearLimit && !isOverLimit,
                'text-yellow-600': isNearLimit,
                'text-red-600': isOverLimit
            }">
                <span x-text="characterCount"></span> / <span>{{ $maxlength }}</span>
                <template x-if="isNearLimit">
                    <span class="ml-1">(<span x-text="remaining"></span> remaining)</span>
                </template>
                <template x-if="isOverLimit">
                    <span class="ml-1 font-semibold">Over limit!</span>
                </template>
            </div>
        @endif
    </div>
</div>
