@extends('layouts.snippets')

@section('title', 'User Management')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('admin.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        <i class="fas fa-users mr-3 text-indigo-600 dark:text-indigo-400"></i>User Management
                    </h1>
                </div>
                <p class="ml-12 text-gray-600 dark:text-gray-400 transition-colors duration-200">
                    Manage user accounts and permissions
                </p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                <i class="fas fa-user-plus mr-2"></i>
                New User
            </a>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name or email..."
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div class="min-w-[150px]">
                    <select name="status" 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="min-w-[150px]">
                    <select name="role" 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.users') }}" 
                       class="px-4 py-2 bg-gray-500 dark:bg-gray-600 text-white rounded-lg hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider max-w-[300px]">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Teams</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Snippets</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider min-w-[180px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center max-w-[300px]">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="ml-4 min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $user->name }}">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate" title="{{ $user->email }}">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_disabled)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-600 text-white">
                                        <i class="fas fa-ban mr-1.5"></i>Disabled
                                    </span>
                                @elseif($user->invitation_token)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-600 text-white">
                                        <i class="fas fa-clock mr-1.5"></i>Pending
                                    </span>
                                @elseif($user->invitation_accepted_at)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-600 text-white">
                                        <i class="fas fa-check mr-1.5"></i>Active
                                    </span>
                                @else
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-600 text-white">
                                        <i class="fas fa-check mr-1.5"></i>Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_super_admin)
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-purple-600 text-white">
                                        <i class="fas fa-shield-alt mr-1.5"></i>Super Admin
                                    </span>
                                @else
                                    <span class="px-3 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->teams_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->snippets_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="inline-flex shadow-sm" role="group">
                                    @if($user->invitation_token)
                                        <form action="{{ route('admin.users.resendInvitation', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Resend Invitation" class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 dark:bg-blue-500 text-white hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-200 rounded-l-lg border-r border-blue-700 dark:border-blue-400">
                                                <i class="fas fa-envelope text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(!$user->is_disabled)
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           title="Edit User"
                                           class="inline-flex items-center justify-center w-10 h-10 bg-indigo-600 dark:bg-indigo-500 text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors duration-200 {{ $user->invitation_token ? 'border-r border-indigo-700 dark:border-indigo-400' : ($user->id === auth()->id() ? 'rounded-lg' : 'rounded-l-lg border-r border-indigo-700 dark:border-indigo-400') }}">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                    @endif
                                    
                                    @if($user->id !== auth()->id())
                                        @if($user->is_disabled)
                                            <form action="{{ route('admin.users.enable', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Enable User" class="inline-flex items-center justify-center w-10 h-10 bg-green-600 dark:bg-green-500 text-white hover:bg-green-700 dark:hover:bg-green-600 transition-colors duration-200 border-r border-green-700 dark:border-green-400">
                                                    <i class="fas fa-check text-sm"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.disable', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Disable User" class="inline-flex items-center justify-center w-10 h-10 bg-orange-600 dark:bg-orange-500 text-white hover:bg-orange-700 dark:hover:bg-orange-600 transition-colors duration-200 border-r border-orange-700 dark:border-orange-400">
                                                    <i class="fas fa-ban text-sm"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <button type="button"
                                                title="Delete User"
                                                @click="$dispatch('open-delete-user-modal', {
                                                    userId: {{ $user->id }},
                                                    userName: {{ json_encode($user->name) }},
                                                    teamsCount: {{ $user->teams_count }},
                                                    snippetsCount: {{ $user->snippets_count }},
                                                    ownedTeams: {{ json_encode($user->ownedTeams->map(fn($t) => [
                                                        'id' => $t->id, 
                                                        'name' => $t->name, 
                                                        'membersCount' => $t->members_count,
                                                        'snippetsCount' => $t->snippets_count,
                                                        'foldersCount' => $t->folders_count,
                                                        'members' => [] // Will be loaded on-demand if needed
                                                    ])) }}
                                                })"
                                                class="inline-flex items-center justify-center w-10 h-10 bg-red-600 dark:bg-red-500 text-white hover:bg-red-700 dark:hover:bg-red-600 transition-colors duration-200 rounded-r-lg">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div x-data="{
            showModal: false,
            userId: null,
            userName: '',
            teamsCount: 0,
            snippetsCount: 0,
            ownedTeams: [],
            teamOwners: {},
            confirmText: '',
            canDelete: false,
            loadingMembers: false,
            async loadTeamMembers(userId) {
                if (this.ownedTeams.length === 0 || this.ownedTeams[0].members.length > 0) return;
                
                this.loadingMembers = true;
                try {
                    const response = await fetch(`/admin/users/${userId}/team-members`);
                    const teams = await response.json();
                    
                    // Update members for each team
                    teams.forEach(teamData => {
                        const team = this.ownedTeams.find(t => t.id === teamData.id);
                        if (team) {
                            team.members = teamData.members;
                        }
                    });
                } catch (error) {
                    console.error('Failed to load team members:', error);
                } finally {
                    this.loadingMembers = false;
                }
            },
            checkCanDelete() {
                this.canDelete = (this.confirmText === this.userName);
                if (this.canDelete && this.ownedTeams.length > 0) {
                    // Check that all owned teams have either a new owner selected or will be deleted
                    for (let team of this.ownedTeams) {
                        // Always allow deletion now - either transfer or delete is valid
                    }
                }
            }
        }"
         @open-delete-user-modal.window="
            showModal = true;
            userId = $event.detail.userId;
            userName = $event.detail.userName;
            teamsCount = $event.detail.teamsCount;
            snippetsCount = $event.detail.snippetsCount;
            ownedTeams = $event.detail.ownedTeams || [];
            teamOwners = {};
            confirmText = '';
            canDelete = false;
            loadTeamMembers($event.detail.userId);
         "
         @keydown.escape.window="showModal = false">

        <!-- Modal Overlay -->
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <!-- Modal Content -->
            <div @click.away="showModal = false"
                 class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <!-- Warning Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-2">Delete User</h3>

                <!-- Warning Message -->
                <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200 font-semibold mb-2">⚠️ This will soft delete the user</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">Deleting this user will:</p>
                    <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1 ml-4">
                        <li>• Mark user "<span class="font-semibold" x-text="userName"></span>" as deleted</li>
                        <li>• Remove them from <span x-text="teamsCount"></span> team(s)</li>
                        <li>• Strip sensitive data (email, password)</li>
                        <li>• Preserve all snippets and version history with proper attribution</li>
                        <li>• Keep audit trail intact</li>
                    </ul>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-3 font-medium">💡 Use the "Disable" button for reversible deactivation instead.</p>
                </div>

                <!-- Owned Teams Section -->
                <div x-show="ownedTeams.length > 0" class="mb-4">
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 font-semibold mb-2">
                            👥 This user owns <span x-text="ownedTeams.length"></span> team(s). Choose what to do:
                        </p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-3 italic">
                            💡 Note: When transferring ownership, all snippet version history will be attributed to the new owner.
                        </p>
                        <div class="space-y-3">
                            <template x-for="team in ownedTeams" :key="team.id">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded border border-yellow-300 dark:border-yellow-700">
                                    <div class="flex items-start justify-between mb-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="team.name"></p>
                                        <div class="flex items-center space-x-3 text-xs text-gray-600 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <span x-text="team.membersCount"></span>
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                                </svg>
                                                <span x-text="team.snippetsCount"></span>
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                </svg>
                                                <span x-text="team.foldersCount"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <label class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">Transfer to:</label>
                                        <select x-model="teamOwners[team.id]"
                                                :name="'team_owners[' + team.id + ']'"
                                                class="flex-1 text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">Delete this team</option>
                                            <template x-for="member in team.members" :key="member.id">
                                                <option :value="member.id" x-text="member.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Input -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        To confirm, type the user's name: <span class="font-bold" x-text="userName"></span>
                    </label>
                    <input type="text"
                           x-model="confirmText"
                           @input="checkCanDelete()"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Type user name here">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button @click="showModal = false"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <form :action="'/admin/users/' + userId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <!-- Hidden inputs for team owners -->
                        <template x-for="(newOwnerId, teamId) in teamOwners" :key="teamId">
                            <input type="hidden" :name="'team_owners[' + teamId + ']'" :value="newOwnerId">
                        </template>
                        <button type="submit"
                                :disabled="!canDelete"
                                :class="canDelete ? 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
