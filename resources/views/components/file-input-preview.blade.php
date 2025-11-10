@props([
    'name',
    'label' => null,
    'accept' => null,
    'maxSize' => null,
    'required' => false,
    'multiple' => false
])

<div x-data="{
    files: [],
    isDragging: false,
    handleFiles(fileList) {
        this.files = Array.from(fileList).map(file => ({
            name: file.name,
            size: this.formatFileSize(file.size),
            type: file.type,
            preview: this.getPreview(file)
        }));
    },
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    getPreview(file) {
        if (file.type.startsWith('image/')) {
            return URL.createObjectURL(file);
        }
        return null;
    },
    removeFile(index) {
        this.files.splice(index, 1);
        const input = this.$refs.fileInput;
        if (this.files.length === 0) {
            input.value = '';
        }
    },
    handleDrop(e) {
        this.isDragging = false;
        const dt = e.dataTransfer;
        const files = dt.files;
        this.$refs.fileInput.files = files;
        this.handleFiles(files);
    }
}"
class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif

    <!-- File Input (Hidden) -->
    <input
        type="file"
        id="{{ $name }}"
        name="{{ $name }}"
        x-ref="fileInput"
        @change="handleFiles($event.target.files)"
        {{ $attributes->merge(['class' => 'hidden']) }}
        @if($accept) accept="{{ $accept }}" @endif
        @if($multiple) multiple @endif
        @if($required) required @endif
    />

    <!-- Drop Zone -->
    <div @click="$refs.fileInput.click()"
         @dragover.prevent="isDragging = true"
         @dragleave.prevent="isDragging = false"
         @drop.prevent="handleDrop"
         :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white'"
         class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition hover:border-blue-400 hover:bg-gray-50">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <p class="mt-2 text-sm text-gray-600">
            <span class="font-semibold text-blue-600">Click to upload</span> or drag and drop
        </p>
        @if($accept || $maxSize)
            <p class="mt-1 text-xs text-gray-500">
                @if($accept)
                    {{ str_replace(',', ', ', $accept) }}
                @endif
                @if($maxSize)
                    (Max {{ $maxSize }})
                @endif
            </p>
        @endif
    </div>

    <!-- File Preview -->
    <template x-if="files.length > 0">
        <div class="space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                        <!-- Image Preview or File Icon -->
                        <template x-if="file.preview">
                            <img :src="file.preview" :alt="file.name" class="w-12 h-12 object-cover rounded">
                        </template>
                        <template x-if="!file.preview">
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </template>

                        <!-- File Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                            <p class="text-xs text-gray-500" x-text="file.size"></p>
                        </div>
                    </div>

                    <!-- Remove Button -->
                    <button type="button"
                            @click.stop="removeFile(index)"
                            class="ml-3 text-red-600 hover:text-red-800 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </template>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
