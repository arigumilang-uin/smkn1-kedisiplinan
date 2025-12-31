
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">User Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-activate') }}', 'Aktifkan user terpilih?')" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                Aktifkan
            </button>
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-deactivate') }}', 'Nonaktifkan user terpilih?')" class="btn btn-sm btn-white text-orange-600 border-orange-200 hover:bg-orange-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
                Suspend
            </button>
            <button type="button" @click="submitBulkAction('{{ route('users.bulk-delete') }}', 'Hapus user terpilih? Tindakan ini tidak dapat dibatalkan!')" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Hapus
            </button>
        </div>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="bulk-action-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="ids" :value="selected.join(',')">
    </form>

    {{-- Table --}}
    <div class="table-container min-h-[300px]"> {{-- min-h added to prevent dropdown cut-off at bottom --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Keterangan</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
                        <div class="flex items-center justify-center">
                            <template x-if="!selectionMode">
                                <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="9" y1="12" x2="15" y2="12"></line> 
                                    </svg>
                                </div>
                            </template>
                            <template x-if="selectionMode">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                </div>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $user->id }}') }">
                        <td class="font-medium text-gray-800">{{ $user->username }}</td>
                        <td class="text-gray-600 text-sm">{{ $user->nama ?? '-' }}</td>
                        <td class="text-gray-500">{{ $user->email ?? '-' }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $user->role->nama_role ?? '-' }}</span>
                        </td>
                        <td class="text-center relative">
                            {{-- Normal Mode: Kebab Dropdown --}}
                            <div x-show="!selectionMode" 
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             // Trigger Selection Mode
                                             this.toggleSelectionMode();
                                             // Add this row to selected
                                             if (!this.selected.includes('{{ $user->id }}')) {
                                                 this.selected.push('{{ $user->id }}');
                                             }
                                             // Haptic feedback if available (for mobile)
                                             if (navigator.vibrate) navigator.vibrate(50);
                                         }, 500);
                                     },
                                     
                                     endPress() {
                                         clearTimeout(this.timer);
                                     },
                                     
                                     toggle() {
                                         if (this.isLongPress) return;
                                         if (this.open) { this.open = false; return; }
                                         this.open = true;
                                         this.$nextTick(() => {
                                             const trigger = this.$refs.trigger.getBoundingClientRect();
                                             const menu = this.$refs.menu;
                                             
                                             // Calculate Right Alignment relative to viewport
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; // + padding
                                             
                                             // Check bottom overflow
                                             if (window.innerHeight - trigger.bottom < menu.offsetHeight + 20) {
                                                 top = trigger.top - menu.offsetHeight - 2;
                                             }
                                             
                                             menu.style.top = `${top}px`;
                                             menu.style.left = `${left}px`;
                                         });
                                     }
                                 }" 
                                 @scroll.window="open = false"
                                 @resize.window="open = false"
                                 class="relative inline-block text-left"
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    type="button" 
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('users.edit', $user->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $user->username }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Selection Mode: Checkbox --}}
                            <div x-show="selectionMode" style="display: none;">
                                <input type="checkbox" value="{{ $user->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <h3 class="empty-state-title">Tidak Ada User</h3>
                                <p class="empty-state-description">Belum ada user yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    
    @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }}</p>
            <div class="pagination">
                {{-- Previous --}}
                @if($users->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endif
    
    <script>
        function submitBulkAction(url, message) {
            if (confirm(message)) {
                const form = document.getElementById('bulk-action-form');
                form.action = url;
                form.submit();
            }
        }
    </script>
