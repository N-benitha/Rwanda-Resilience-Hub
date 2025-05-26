<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Admin Sidebar -->
            <div class="flex gap-8">
                <div class="w-64 bg-amber-700 rounded-lg shadow-lg p-4 h-fit">
                    <div class="mb-6">
                        <button onclick="closeSidebar()" class="text-white float-right hover:text-amber-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div class="clear-both"></div>
                    </div>

                    <nav class="space-y-2">
                        <a href="#" class="block text-white hover:bg-amber-600 rounded-lg p-3 transition-colors duration-200">
                            Web Monitor
                        </a>
                        <a href="#" class="block text-white bg-amber-600 rounded-lg p-3 font-semibold">
                            User Management
                        </a>
                        <a href="#" class="block text-white hover:bg-amber-600 rounded-lg p-3 transition-colors duration-200">
                            Permission Management
                        </a>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="flex-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                        <!-- Header -->
                        <div class="bg-amber-700 text-white p-6 rounded-t-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-amber-600 p-2 rounded">
                                        <svg class="w-6 h-6 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="text-xl font-bold">Rwanda Resilience Hub</h1>
                                    </div>
                                </div>
                                <div class="bg-amber-800 px-4 py-2 rounded-full">
                                    <span class="text-sm font-medium">User</span>
                                </div>
                            </div>
                        </div>

                        <!-- User List -->
                        <div class="p-6">
                            <div class="bg-amber-100 dark:bg-amber-900 rounded-lg p-4 mb-6">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-200">All Users</h2>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-amber-700 dark:text-amber-300">{{ $users->count() ?? 20 }} users</span>
                                        <button onclick="addUser()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            <span>ADD USER</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Users List -->
                            <div class="space-y-3">
                                @if(isset($users) && $users->count() > 0)
                                    @foreach($users as $user)
                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ $user->name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 text-xs rounded-full">
                                                {{ ucfirst($user->user_type ?? 'civilian') }}
                                            </span>
                                            <button onclick="editUser({{ $user->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">
                                                Edit
                                            </button>
                                            <button onclick="removeUser({{ $user->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <!-- Sample Users -->
                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">TM</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Tessa Mike</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">tessa.mike@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 text-xs rounded-full">Admin</span>
                                            <button onclick="editUser(1)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(1)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">OF</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Oyuzuzo Frank</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">frank.oyuzuzo@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs rounded-full">Civilian</span>
                                            <button onclick="editUser(2)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(2)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">KJ</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Kamanzi Jess</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">jess.kamanzi@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200 text-xs rounded-full">Government</span>
                                            <button onclick="editUser(3)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(3)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">MJ</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Murinzi Jade</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">jade.murinzi@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs rounded-full">Civilian</span>
                                            <button onclick="editUser(4)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(4)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">BE</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Bazimpaka Esther</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">esther.bazimpaka@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs rounded-full">Civilian</span>
                                            <button onclick="editUser(5)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(5)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center">
                                                <span class="text-amber-800 dark:text-amber-200 font-semibold text-sm">HP</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Hozo Pascal</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">pascal.hozo@example.com</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200 text-xs rounded-full">Government</span>
                                            <button onclick="editUser(6)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Edit</button>
                                            <button onclick="removeUser(6)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200">Remove</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Edit User Permissions</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name:</label>
                    <input type="text" id="editUserName" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white" placeholder="User Name">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User Type:</label>
                    <select id="userTypeSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select User Type</option>
                        <option value="government">Government/Admin</option>
                        <option value="civilian">Civilian</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button onclick="closeEditModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button onclick="saveUserChanges()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentEditingUserId = null;

        function closeSidebar() {
            window.location.href = '{{ route("admin.dashboard") }}';
        }

        function addUser() {
            // In a real application, this would open an add user modal or form
            alert('Add User functionality would be implemented here');
        }

        function editUser(userId) {
            currentEditingUserId = userId;
            document.getElementById('editUserModal').classList.remove('hidden');
            
            // In a real application, you would fetch user data and populate the form
            if (userId === 1) {
                document.getElementById('editUserName').value = 'Tessa Mike';
                document.getElementById('userTypeSelect').value = 'government';
            }
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            currentEditingUserId = null;
        }

        function saveUserChanges() {
            const name = document.getElementById('editUserName').value;
            const userType = document.getElementById('userTypeSelect').value;
            
            if (!name || !userType) {
                alert('Please fill in all fields');
                return;
            }
            
            // In a real application, you would send an AJAX request to update the user
            console.log('Updating user', currentEditingUserId, 'with', { name, userType });
            
            alert('User updated successfully!');
            closeEditModal();
            
            // Optionally reload the page or update the UI
            // location.reload();
        }

        function removeUser(userId) {
            if (confirm('Are you sure you want to remove this user?')) {
                // In a real application, you would send an AJAX request to delete the user
                console.log('Removing user', userId);
                alert('User removed successfully!');
                
                // Optionally reload the page or remove the user from the UI
                // location.reload();
            }
        }
    </script>
    @endpush
</x-app-layout>