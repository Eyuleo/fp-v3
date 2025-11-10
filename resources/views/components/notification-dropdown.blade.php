@props(['notifications' => []])

<div x-data="{
    open: false,
    unreadCount: 0,
    notifications: [],
    loading: false,
    init() {
        this.fetchUnreadCount();
        this.fetchNotifications();
        setInterval(() => this.fetchUnreadCount(), 30000);
    },
    fetchUnreadCount() {
        fetch('{{ route('notifications.unread-count') }}')
            .then(response => response.json())
            .then(data => this.unreadCount = data.count)
            .catch(error => console.error('Error fetching unread count:', error));
    },
    fetchNotifications() {
        if (this.loading) return;
        this.loading = true;
        fetch('{{ route('notifications.recent') }}')
            .then(response => response.json())
            .then(data => {
                this.notifications = data.notifications || [];
                this.loading = false;
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                this.loading = false;
            });
    },
    markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            }
        })
        .then(response => response.json())
        .then(() => {
            this.fetchUnreadCount();
            this.fetchNotifications();
        })
        .catch(error => console.error('Error marking notification as read:', error));
    },
    markAllAsRead() {
        fetch('{{ route('notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            }
        })
        .then(response => response.json())
        .then(() => {
            this.fetchUnreadCount();
            this.fetchNotifications();
        })
        .catch(error => console.error('Error marking all as read:', error));
    }
}"
@click.outside="open = false"
class="relative">
    <!-- Trigger Button -->
    <button @click="open = !open; if(open) fetchNotifications()"
            class="relative text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <span x-show="unreadCount > 0"
              x-text="unreadCount"
              class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px]">
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
         style="display: none;">

        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            <button @click="markAllAsRead()"
                    x-show="unreadCount > 0"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Mark all as read
            </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading && notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm">Loading notifications...</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="mt-2 text-sm">No notifications yet</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div @click="markAsRead(notification.id); window.location.href = notification.action_url"
                     :class="notification.read_at ? 'bg-white' : 'bg-blue-50'"
                     class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div :class="notification.read_at ? 'bg-gray-400' : 'bg-blue-600'"
                                 class="w-2 h-2 rounded-full mt-2"></div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="notification.time_ago"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 text-center">
            <a href="{{ route('notifications.index') }}"
               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>
