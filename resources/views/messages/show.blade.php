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
                <div class="border-t border-gray-200 p-6" x-data="{
                    sending: false,
                    messageContent: '',
                    async sendMessage(event) {
                        if (this.sending || !this.messageContent.trim()) return;

                        this.sending = true;
                        const formData = new FormData(event.target);

                        // Optimistically add message to thread
                        const messageThread = document.querySelector('.message-thread');
                        if (messageThread) {
                            const tempMessage = document.createElement('div');
                            tempMessage.className = 'flex justify-end mb-4 opacity-50';
                            tempMessage.innerHTML = `
                                <div class='max-w-xs lg:max-w-md'>
                                    <div class='bg-blue-600 text-white rounded-lg px-4 py-2'>
                                        <p class='text-sm whitespace-pre-line'>${this.messageContent}</p>
                                    </div>
                                    <p class='text-xs text-gray-500 mt-1 text-right'>Sending...</p>
                                </div>
                            `;
                            messageThread.appendChild(tempMessage);
                            messageThread.scrollTop = messageThread.scrollHeight;
                        }

                        try {
                            const response = await fetch(event.target.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                // Clear form
                                this.messageContent = '';
                                event.target.reset();

                                // Reload page to show actual message
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                alert('Failed to send message. Please try again.');
                                if (messageThread) {
                                    messageThread.removeChild(messageThread.lastChild);
                                }
                            }
                        } catch (error) {
                            console.error('Error sending message:', error);
                            alert('Failed to send message. Please try again.');
                            if (messageThread) {
                                messageThread.removeChild(messageThread.lastChild);
                            }
                        } finally {
                            this.sending = false;
                        }
                    }
                }">
                    <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="sendMessage">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <input type="hidden" name="service_id" value="{{ $serviceId }}">

                        <div class="space-y-4">
                            <div>
                                <x-textarea-counter
                                    name="content"
                                    label="Message"
                                    :maxlength="2000"
                                    :rows="4"
                                    required
                                    placeholder="Type your message..."
                                    x-model="messageContent" />
                            </div>

                            <div>
                                <x-file-input-preview
                                    name="attachment"
                                    label="Attachment (optional, max 25MB)"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.txt"
                                    max-size="25MB" />
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="sending || !messageContent.trim()"
                                    :class="sending || !messageContent.trim() ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg x-show="sending" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="sending ? 'Sending...' : 'Send Message'"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
