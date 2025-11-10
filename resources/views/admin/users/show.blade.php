<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                User Details: {{ $user->first_name }} {{ $user->last_name }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Users
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Info Card -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-center">
                        @if($user->avatar_path)
                            <img class="h-24 w-24 rounded-full mx-auto" src="{{ Storage::url($user->avatar_path) }}" alt="">
                        @else
                            <div class="h-24 w-24 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-2xl mx-auto">
                                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                            </div>
                        @endif
                        <h3 class="mt-4 text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="mt-2">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'student' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'client' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6 space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">University:</span>
                            <p class="text-sm text-gray-900">{{ $user->university ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Phone:</span>
                            <p class="text-sm text-gray-900">{{ $user->phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Registered:</span>
                            <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Email Verified:</span>
                            <p class="text-sm text-gray-900">{{ $user->email_verified_at ? 'Yes' : 'No' }}</p>
                        </div>
                        @if($user->isStudent() && $user->stripe_connect_account_id)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Stripe Connected:</span>
                                <p class="text-sm text-gray-900">Yes</p>
                            </div>
                        @endif
                    </div>

                    @if($user->bio)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <span class="text-sm font-medium text-gray-500">Bio:</span>
                            <p class="mt-2 text-sm text-gray-900">{{ $user->bio }}</p>
                        </div>
                    @endif

                    <!-- Actions -->
                    @if($user->role !== 'admin')
                        <div class="mt-6 border-t border-gray-200 pt-6 space-y-2">
                            @if($user->is_active)
                                <form method="POST" action="{{ route('admin.users.suspend', $user) }}" onsubmit="return confirm('Are you sure you want to suspend this user?');">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                                        Suspend User
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.reinstate', $user) }}">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                                        Reinstate User
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity and Stats -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($user->isStudent())
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Services</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->services->count() }}</div>
                        </div>
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Orders Completed</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->ordersAsStudent->where('status', 'completed')->count() }}</div>
                        </div>
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Average Rating</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($user->averageRating(), 1) }}</div>
                        </div>
                    @else
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Orders Placed</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->ordersAsClient->count() }}</div>
                        </div>
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->ordersAsClient->where('status', 'completed')->count() }}</div>
                        </div>
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">Reviews Given</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->reviews->count() }}</div>
                        </div>
                    @endif
                </div>

                <!-- Services (for students) -->
                @if($user->isStudent() && $user->services->count() > 0)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Services</h3>
                        <div class="space-y-3">
                            @foreach($user->services as $service)
                                <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                                    <div>
                                        <a href="{{ route('services.show', $service->slug) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                            {{ $service->title }}
                                        </a>
                                        <div class="text-sm text-gray-500">
                                            ${{ number_format($service->price, 2) }} • {{ $service->delivery_days }} days
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Recent Orders -->
                @php
                    $recentOrders = $user->isStudent()
                        ? $user->ordersAsStudent()->latest()->take(5)->get()
                        : $user->ordersAsClient()->latest()->take(5)->get();
                @endphp

                @if($recentOrders->count() > 0)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Orders</h3>
                        <div class="space-y-3">
                            @foreach($recentOrders as $order)
                                <div class="flex justify-between items-center p-3 border border-gray-200 rounded">
                                    <div>
                                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                            Order #{{ $order->id }}
                                        </a>
                                        <div class="text-sm text-gray-500">
                                            {{ $order->service->title }} • ${{ number_format($order->price, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
