<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </span>
                Log Aktivitas Sistem
            </h2>
            <div class="text-sm text-gray-500">
                Total Tercatat: <span class="font-bold text-gray-800">{{ $logs->total() }}</span> aktivitas
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- FILTER BAR -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('wa.logs') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Filter Tipe Aksi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Tipe
                            Aktivitas</label>
                        <select name="action"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Semua Aktivitas --</option>
                            @foreach ($actionTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tanggal -->
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Tanggal</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Filter Kata Kunci -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Cari
                            Deskripsi / IP</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama, pesan, IP..."
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <!-- Tombol Filter -->
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                        @if (request()->hasAny(['action', 'date', 'search']))
                            <a href="{{ route('wa.logs') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2.5 px-4 rounded-xl text-sm transition flex items-center justify-center"
                                title="Reset Filter">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- TABEL LOG AKTIVITAS -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr
                                class="bg-gray-50/80 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-4 px-6">Waktu</th>
                                <th class="py-4 px-6">Pengguna</th>
                                <th class="py-4 px-6">Aksi</th>
                                <th class="py-4 px-6">Deskripsi</th>
                                <th class="py-4 px-6">IP & Perangkat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <!-- Waktu -->
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-gray-500">
                                        <div class="font-medium text-gray-800">{{ $log->created_at->format('d/m/Y') }}
                                        </div>
                                        <div>{{ $log->created_at->format('H:i:s') }} <span
                                                class="text-[10px] text-gray-400">({{ $log->created_at->diffForHumans() }})</span>
                                        </div>
                                    </td>

                                    <!-- Pengguna -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if ($log->user)
                                            <div class="flex items-center gap-2.5">
                                                <div
                                                    class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-xs">
                                                        {{ $log->user->name }}</div>
                                                    <div class="text-[11px] text-gray-400">{{ $log->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                Sistem / Tamu
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @php
                                            $badgeClass = match (true) {
                                                str_starts_with($log->action, 'auth_login')
                                                    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                str_starts_with($log->action, 'auth_failed')
                                                    => 'bg-rose-50 text-rose-700 border-rose-200',
                                                str_starts_with($log->action, 'auth_logout')
                                                    => 'bg-gray-100 text-gray-700 border-gray-200',
                                                str_starts_with($log->action, 'blast')
                                                    => 'bg-teal-50 text-[#128C7E] border-teal-200',
                                                str_starts_with($log->action, 'contact')
                                                    => 'bg-violet-50 text-violet-700 border-violet-200',
                                                str_starts_with($log->action, 'template')
                                                    => 'bg-amber-50 text-amber-700 border-amber-200',
                                                default => 'bg-blue-50 text-blue-700 border-blue-200',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                            {{ $actionTypes[$log->action] ?? str_replace('_', ' ', strtoupper($log->action)) }}
                                        </span>
                                    </td>

                                    <!-- Deskripsi -->
                                    <td class="py-4 px-6 max-w-md">
                                        <div class="text-gray-800 leading-relaxed">{{ $log->description }}</div>
                                        @if ($log->properties)
                                            <details class="mt-1.5 text-xs text-gray-500 cursor-pointer">
                                                <summary class="hover:text-indigo-600 font-medium">Metadata Tambahan
                                                </summary>
                                                <pre class="mt-1 p-2 bg-gray-50 rounded-lg border border-gray-100 text-[11px] overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @endif
                                    </td>

                                    <!-- IP & Perangkat -->
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-gray-500">
                                        <div class="font-mono text-gray-700">{{ $log->ip_address ?: '-' }}</div>
                                        <div class="text-[11px] text-gray-400 max-w-[200px] truncate"
                                            title="{{ $log->user_agent }}">
                                            {{ $log->user_agent ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Tidak ada catatan aktivitas yang cocok dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                @if ($logs->hasPages())
                    <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
