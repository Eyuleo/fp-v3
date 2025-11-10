<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse($notifications as $notification)
                        <div class="border-b border-gray-200 last:border-0 py-4 {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 {{ $notification->read_at ? '' : 'font-semibold' }}">
                                        {{ $notification->data['message'] ?? 'New notification' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    @if(!$notification->read_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-600 text-white">
                                            New
                                        </span>
                                    @endif
                                    <a href="{{ route('notifications.mark-read', $notification->id) }}"
                                       class="text-sm text-blue-600 hover:text-blue-800">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No notifications yet.</p>
                    @endforelse

                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
