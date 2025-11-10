<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($threads->isEmpty())
                        <p class="text-gray-500 text-center py-8">No messages yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($threads as $thread)
                                @php
                                    $otherUser = $thread->sender_id === auth()->id()
                                        ? $thread->receiver
                                        : $thread->sender;
                                    $isUnread = $thread->receiver_id === auth()->id() && !$thread->is_read;
                                @endphp
                                <a href="{{ route('messages.show', [
                                    'user_id' => $otherUser->id,
                                    'order_id' => $thread->order_id,
                                    'service_id' => $thread->service_id,
                                ]) }}"
                                   class="block p-4 border rounded-lg hover:bg-gray-50 transition {{ $isUnread ? 'bg-blue-50 border-blue-300' : 'border-gray-200' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold text-gray-900">
                                                    {{ $otherUser->first_name }} {{ $otherUser->last_name }}
                                                </h3>
                                                @if($isUnread)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-600 text-white">
                                                        New
                                                    </span>
                                                @endif
                                            </div>
                                            @if($thread->order_id)
                                                <p class="text-sm text-gray-500">Order #{{ $thread->order_id }}</p>
                                            @elseif($thread->service_id)
                                                <p class="text-sm text-gray-500">Service Inquiry</p>
                                            @endif
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                {{ Str::limit($thread->content, 100) }}
                                            </p>
                                        </div>
                                        <span class="text-xs text-gray-400 ml-4">
                                            {{ $thread->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
