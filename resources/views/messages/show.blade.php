<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conversation with') }} {{ $otherUser->first_name }} {{ $otherUser->last_name }}
            </h2>
            <a href="{{ route('messages.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Messages
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Message Thread -->
                <div class="p-6">
                    @include('messages.partials.message-thread', ['messages' => $messages])
                </div>

                <!-- Message Form -->
                <div class="border-t border-gray-200 p-6">
                    <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <input type="hidden" name="service_id" value="{{ $serviceId }}">

                        <div class="space-y-4">
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                    Message
                                </label>
                                <textarea
                                    id="content"
                                    name="content"
                                    rows="4"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Type your message...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                    Attachment (optional, max 25MB)
                                </label>
                                <input
                                    type="file"
                                    id="attachment"
                                    name="attachment"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.txt"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
