<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </span>
                Kelola User & Hak Akses
            </h2>
            <div>
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-create-user-modal'))"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah User Baru</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] min-h-screen" x-data="{
        createModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
        editModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
        currentUser: {
            id: '{{ old('edit_id') }}',
            name: '{{ old('name') }}',
            username: '{{ old('username') }}',
            email: '{{ old('email') }}',
            role: '{{ old('role', 'pengguna') }}',
            wa_instance_name: '{{ old('wa_instance_name') }}'
        },
        openEdit(user) {
            this.currentUser = { ...user };
            this.editModal = true;
        }
    }"
        @open-create-user-modal.window="createModal = true"
        @keydown.escape.window="createModal = false; editModal = false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- FLASH ALERTS -->
            @if (session('status'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl">
                    <div class="font-bold text-sm mb-1">Terjadi kesalahan input data:</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-slate-100 text-slate-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Total User</div>
                        <div class="text-2xl font-black text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-purple-600 font-medium">Superadmin</div>
                        <div class="text-2xl font-black text-gray-800">{{ $stats['superadmin'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-blue-600 font-medium">Admin</div>
                        <div class="text-2xl font-black text-gray-800">{{ $stats['admin'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-emerald-600 font-medium">Pengguna Standar</div>
                        <div class="text-2xl font-black text-gray-800">{{ $stats['pengguna'] }}</div>
                    </div>
                </div>
            </div>

            <!-- FILTER & SEARCH BAR -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('users.index') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Cari
                            User</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama, username, email..."
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Filter
                            Role</label>
                        <select name="role"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Semua Role --</option>
                            <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>
                                Superadmin</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pengguna" {{ request('role') === 'pengguna' ? 'selected' : '' }}>Pengguna
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'role']))
                            <a href="{{ route('users.index') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-xl text-sm transition flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- USERS TABLE -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">Daftar Akun User</h3>
                        <p class="text-xs text-gray-500">Kelola pengguna, hak akses role, dan kata sandi akun.</p>
                    </div>
                    <button type="button" @click="createModal = true"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span>+ Tambah User</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead
                            class="bg-gray-50/80 text-gray-500 font-bold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Instance WA</th>
                                <th class="px-6 py-4">Dibuat Pada</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-500 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if ($user->id === Auth::id())
                                                        <span
                                                            class="text-[10px] bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded-full">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono">
                                                    &#64;{{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($user->role === 'superadmin')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                Superadmin
                                            </span>
                                        @elseif ($user->role === 'admin')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Admin
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Pengguna
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($user->wa_instance_name)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                {{ $user->wa_instance_name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Default</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400">
                                        {{ $user->created_at ? $user->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @php
                                            $canEdit = Auth::user()->isSuperAdmin() || !$user->isSuperAdmin();
                                            $canDelete =
                                                Auth::user()->id !== $user->id &&
                                                (Auth::user()->isSuperAdmin() || !$user->isSuperAdmin());
                                        @endphp

                                        @if ($canEdit)
                                            <button type="button"
                                                @click="openEdit({{ json_encode(['id' => $user->id, 'name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'role' => $user->role, 'wa_instance_name' => $user->wa_instance_name ?? '']) }})"
                                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                                Edit
                                            </button>
                                        @endif

                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                class="inline-block"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data tidak dapat dipulihkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                        Tidak ada data user ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- CREATE MODAL -->
        <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="createModal = false"></div>

                <div
                    class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl z-10 space-y-5 border border-gray-100">
                    <div class="flex items-center justify-between border-b pb-4">
                        <h3 class="font-bold text-lg text-gray-900">Tambah User Baru</h3>
                        <button type="button" @click="createModal = false"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama
                                Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Contoh: John Doe"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Username
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="username" value="{{ old('username') }}" required
                                placeholder="Contoh: johndoe"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('username')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email
                                <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="Contoh: john@example.com"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role /
                                Hak Akses <span class="text-rose-500">*</span></label>
                            <select name="role" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @if (Auth::user()->isSuperAdmin())
                                    <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>
                                        Superadmin (Akses Penuh)</option>
                                @endif
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Kelola
                                    User & Blast)</option>
                                <option value="pengguna"
                                    {{ old('role', 'pengguna') === 'pengguna' ? 'selected' : '' }}>Pengguna (Akses
                                    Fitur Standar)</option>
                            </select>
                            @error('role')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama
                                WhatsApp Instance (Opsional)</label>
                            <input type="text" name="wa_instance_name" value="{{ old('wa_instance_name') }}"
                                placeholder="Contoh: cs_budi (Kosongkan jika default)"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                            <p class="text-[11px] text-gray-400 mt-1">Nama instance unik untuk koneksi WhatsApp akun
                                ini.</p>
                            @error('wa_instance_name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password
                                <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('password')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" @click="createModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="editModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="editModal = false"></div>

                <div
                    class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl z-10 space-y-5 border border-gray-100">
                    <div class="flex items-center justify-between border-b pb-4">
                        <h3 class="font-bold text-lg text-gray-900">Edit User</h3>
                        <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="'{{ url('users') }}/' + currentUser.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_id" :value="currentUser.id">

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama
                                Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="currentUser.name" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Username
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="username" x-model="currentUser.username" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email
                                <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" x-model="currentUser.email" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role /
                                Hak Akses <span class="text-rose-500">*</span></label>
                            <select name="role" x-model="currentUser.role" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @if (Auth::user()->isSuperAdmin())
                                    <option value="superadmin">Superadmin (Akses Penuh)</option>
                                @endif
                                <option value="admin">Admin (Kelola User & Blast)</option>
                                <option value="pengguna">Pengguna (Akses Fitur Standar)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama
                                WhatsApp Instance (Opsional)</label>
                            <input type="text" name="wa_instance_name" x-model="currentUser.wa_instance_name"
                                placeholder="Contoh: cs_budi (Kosongkan jika default)"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                            <p class="text-[11px] text-gray-400 mt-1">Nama instance unik untuk koneksi WhatsApp akun
                                ini.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password
                                Baru (Opsional)</label>
                            <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" @click="editModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Perbarui User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
