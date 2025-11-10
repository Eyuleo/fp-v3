<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Service Moderation
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.services.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Title or description..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.services.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Services Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($services as $service)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('services.show', $service->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                        {{ $service->title }}
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                    {{ Str::limit($service->description, 60) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $service->student->first_name }} {{ $service->student->last_name }}</div>
                                <div class="text-sm text-gray-500">{{ $service->student->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $service->category->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($service->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $service->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if(!$service->is_active)
                                        <form method="POST" action="{{ route('admin.services.approve', $service) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                    @else
                                        <button
                                            onclick="openDisableModal({{ $service->id }}, '{{ $service->title }}')"
                                            class="text-red-600 hover:text-red-900">
                                            Disable
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No services found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $services->links() }}
            </div>
        </div>
    </div>

    <!-- Disable Service Modal -->
    <div x-data="{ open: false, serviceId: null, serviceTitle: '' }"
         @open-disable-modal.window="open = true; serviceId = $event.detail.serviceId; serviceTitle = $event.detail.serviceTitle"
         x-show="open"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="open = false" class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Disable Service</h3>
                <p class="text-sm text-gray-600 mb-4">
                    You are about to disable: <strong x-text="serviceTitle"></strong>
                </p>

                <form :action="`{{ route('admin.services.index') }}/${serviceId}/disable`" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for disabling:</label>
                        <textarea name="reason" id="reason" rows="4" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Explain why this service is being disabled..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                            Disable Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDisableModal(serviceId, serviceTitle) {
            window.dispatchEvent(new CustomEvent('open-disable-modal', {
                detail: { serviceId, serviceTitle }
            }));
        }
    </script>
</x-admin-layout>
