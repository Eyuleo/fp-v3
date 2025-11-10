<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Welcome back, {{ auth()->user()->first_name }}!</h3>
                    <p class="text-gray-600">Browse services and manage your orders.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Browse Services -->
                <a href="{{ route('services.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Browse Services</h4>
                                <p class="text-sm text-gray-600">Find services you need</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- My Orders -->
                <a href="{{ route('orders.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">My Orders</h4>
                                <p class="text-sm text-gray-600">View and manage orders</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- My Profile -->
                <a href="{{ route('profile.show') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">My Profile</h4>
                                <p class="text-sm text-gray-600">Update your information</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
