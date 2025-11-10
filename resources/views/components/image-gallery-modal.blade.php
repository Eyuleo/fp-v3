@props(['images' => [], 'title' => 'Image Gallery'])

<div x-data="{
    showGallery: false,
    currentIndex: 0,
    images: @js($images),
    get currentImage() {
        return this.images[this.currentIndex] || null;
    },
    openGallery(index = 0) {
        this.currentIndex = index;
        this.showGallery = true;
        document.body.classList.add('overflow-hidden');
    },
    closeGallery() {
        this.showGallery = false;
        document.body.classList.remove('overflow-hidden');
    },
    nextImage() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
    },
    prevImage() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
    }
}"
@keydown.escape.window="showGallery && closeGallery()"
@keydown.arrow-right.window="showGallery && nextImage()"
@keydown.arrow-left.window="showGallery && prevImage()">
    <!-- Trigger Slot -->
    <div @click="openGallery(0)">
        {{ $slot }}
    </div>

    <!-- Modal -->
    <div x-show="showGallery"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="showGallery"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeGallery()"
             class="absolute inset-0 bg-black bg-opacity-90"></div>

        <!-- Modal Content -->
        <div x-show="showGallery"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-5xl">

            <!-- Close Button -->
            <button @click="closeGallery()"
                    class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Image Container -->
            <div class="relative bg-black rounded-lg overflow-hidden">
                <img :src="currentImage?.url"
                     :alt="currentImage?.alt || '{{ $title }}'"
                     class="w-full h-auto max-h-[80vh] object-contain">

                <!-- Navigation Arrows -->
                <template x-if="images.length > 1">
                    <div>
                        <!-- Previous Button -->
                        <button @click="prevImage()"
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-3 rounded-full transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Next Button -->
                        <button @click="nextImage()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-3 rounded-full transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Image Counter -->
                <template x-if="images.length > 1">
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm">
                        <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                    </div>
                </template>
            </div>

            <!-- Image Title/Caption -->
            <template x-if="currentImage?.caption">
                <div class="mt-4 text-center text-white">
                    <p x-text="currentImage.caption" class="text-sm"></p>
                </div>
            </template>
        </div>
    </div>
</div>
