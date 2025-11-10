<div class="space-y-4 max-h-[600px] overflow-y-auto" id="message-thread">
    @forelse($messages as $message)
        @php
            $isSender = $message->sender_id === auth()->id();
        @endphp
        <div class="flex {{ $isSender ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[70%]">
                <div class="flex items-end gap-2 {{ $isSender ? 'flex-row-reverse' : 'flex-row' }}">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-semibold text-gray-700">
                            {{ substr($message->sender->first_name, 0, 1) }}{{ substr($message->sender->last_name, 0, 1) }}
                        </div>
                    </div>

                    <!-- Message Bubble -->
                    <div>
                        <div class="rounded-lg px-4 py-2 {{ $isSender ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                            <p class="text-sm whitespace-pre-wrap break-words">{{ $message->content }}</p>

                            @if($message->attachment_path)
                                <div class="mt-2 pt-2 border-t {{ $isSender ? 'border-blue-500' : 'border-gray-300' }}">
                                    <a href="{{ route('messages.download', $message) }}"
                                       class="flex items-center gap-2 text-sm {{ $isSender ? 'text-blue-100 hover:text-white' : 'text-blue-600 hover:text-blue-800' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span>{{ basename($message->attachment_path) }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Timestamp -->
                        <p class="text-xs text-gray-500 mt-1 {{ $isSender ? 'text-right' : 'text-left' }}">
                            {{ $message->created_at->format('M d, Y g:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No messages yet. Start the conversation!</p>
    @endforelse
</div>

<script>
    // Auto-scroll to bottom on page load
    document.addEventListener('DOMContentLoaded', function() {
        const messageThread = document.getElementById('message-thread');
        if (messageThread) {
            messageThread.scrollTop = messageThread.scrollHeight;
        }
    });
</script>
