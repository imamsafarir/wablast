<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                <span class="p-2 bg-[#128C7E]/10 text-[#128C7E] rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                </span>
                Dashboard Ikhtisar
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('wa.blast') }}"
                    class="inline-flex items-center gap-2 bg-[#128C7E] hover:bg-[#0e6c61] text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span>+ Kirim Blast Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] min-h-screen space-y-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- BANNER STATUS KONEKSI WHATSAPP & UCAPAN -->
            <div
                class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-80 h-80 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                    </svg>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white/90 text-xs font-medium backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Sistem WABlast Aktif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            Selamat Datang, {{ Auth::user()->name }}! 👋
                        </h1>
                        <p class="text-sm text-slate-300 max-w-xl">
                            WhatsApp Instance: <span
                                class="font-mono font-bold text-teal-300">{{ Auth::user()->getWaInstanceName() }}</span>.
                            Pantau antrean pengiriman pesan massal dan aktivitas sistem Anda secara *real-time*.
                        </p>
                    </div>

                    <!-- KARTU STATUS KONEKSI SINKRON -->
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 flex items-center gap-4 shrink-0">
                        @if ($isConnected)
                            <div class="relative">
                                @if (!empty($profile['profile_picture_url']))
                                    <img src="{{ $profile['profile_picture_url'] }}" alt="WA Profile"
                                        class="w-12 h-12 rounded-full border-2 border-emerald-400 object-cover shadow-sm">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                        {{ !empty($profile['profile_name']) ? strtoupper(substr($profile['profile_name'], 0, 1)) : 'WA' }}
                                    </div>
                                @endif
                                <span
                                    class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-400 border-2 border-slate-900 rounded-full"></span>
                            </div>
                            <div>
                                <div class="text-xs text-slate-300">Status Koneksi WA</div>
                                <div class="text-sm font-bold text-emerald-300 flex items-center gap-1.5">
                                    <span>Terhubung
                                        (+{{ $profile['phone'] ?? Auth::user()->getWaInstanceName() }})</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $profile['profile_name'] ?? 'Siap Digunakan' }}</div>
                            </div>
                        @else
                            <div
                                class="w-12 h-12 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 flex items-center justify-center font-bold">
                                !
                            </div>
                            <div>
                                <div class="text-xs text-slate-300">Status Koneksi WA</div>
                                <div class="text-sm font-bold text-amber-300">Belum Terhubung ({{ strtoupper($state) }})
                                </div>
                                <a href="{{ route('wa.setting') }}"
                                    class="text-[11px] text-indigo-300 hover:text-white underline font-semibold mt-0.5 inline-block">
                                    Hubungkan & Scan QR →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RINGKASAN METRIK & STATISTIK (STAT CARDS) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Total Target -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Target</div>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($totalRecipients) }}</div>
                    <div class="text-[11px] text-gray-500">Nomor penerima</div>
                </div>

                <!-- Pesan Sukses -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Sukses Dikirim</div>
                    <div class="text-2xl font-black text-emerald-600">{{ number_format($successRecipients) }}</div>
                    <div class="text-[11px] text-emerald-700/70 font-medium">Berhasil terkirim</div>
                </div>

                <!-- Pesan Gagal -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Gagal Dikirim</div>
                    <div class="text-2xl font-black text-rose-600">{{ number_format($failedRecipients) }}</div>
                    <div class="text-[11px] text-rose-700/70 font-medium">Nomor/Koneksi gagal</div>
                </div>

                <!-- Dalam Antrean / Pending -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Di Antrean</div>
                    <div class="text-2xl font-black text-amber-600">{{ number_format($pendingRecipients) }}</div>
                    <div class="text-[11px] text-amber-700/70 font-medium">Proses / Menunggu</div>
                </div>

                <!-- Total Kampanye -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Kampanye Blast</div>
                    <div class="text-2xl font-black text-indigo-600">{{ number_format($totalCampaigns) }}</div>
                    <div class="text-[11px] text-indigo-700/70 font-medium">Total kampanye dibuat</div>
                </div>

                <!-- Grup & Template -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <div class="text-xs font-bold text-violet-600 uppercase tracking-wider">Grup & Template</div>
                    <div class="text-2xl font-black text-violet-600">{{ $totalGroups }} <span
                            class="text-xs text-gray-400 font-normal">Grup</span></div>
                    <div class="text-[11px] text-gray-500">{{ $totalTemplates }} Template Pesan</div>
                </div>
            </div>

            <!-- BAGIAN ANTREAN PROSES & KAMPANYE BERJALAN -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                        <h3 class="font-bold text-xl text-gray-900">Antrean & Pengiriman Blast Berjalan</h3>
                    </div>
                    <a href="{{ route('wa.blast') }}"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                        Buka Halaman Blast →
                    </a>
                </div>

                @if ($activeCampaigns->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($activeCampaigns as $active)
                            @php
                                $processed = $active->success_count + $active->failed_count;
                                $percent =
                                    $active->total_target > 0 ? round(($processed / $active->total_target) * 100) : 0;
                            @endphp
                            <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-4 shadow-xs">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                            <span>#{{ $active->id }} -
                                                {{ $active->judul ?: 'Kampanye WA Blast' }}</span>
                                        </h4>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Pembuat: <span
                                                class="font-semibold text-gray-700">{{ $active->user?->name ?? 'Sistem' }}</span>
                                            • Instance: <span
                                                class="font-mono font-bold text-teal-700">{{ $active->user?->getWaInstanceName() ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        {{ strtoupper($active->status) }}
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-xs font-bold text-gray-700">
                                        <span>Progress Pengiriman</span>
                                        <span>{{ $processed }} / {{ $active->total_target }}
                                            ({{ $percent }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r from-[#25D366] to-[#128C7E] h-3 rounded-full transition-all duration-300"
                                            style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200/60">
                                    <div class="flex items-center gap-4">
                                        <span class="text-emerald-700 font-bold">✓ Sukses:
                                            {{ $active->success_count }}</span>
                                        <span class="text-rose-700 font-bold">✕ Gagal:
                                            {{ $active->failed_count }}</span>
                                    </div>
                                    <a href="{{ route('wa.blast.campaign.show', $active->id) }}"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition">
                                        Lihat Rincian
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center bg-gray-50/70 rounded-2xl border border-dashed border-gray-200">
                        <div
                            class="w-14 h-14 mx-auto mb-3 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-800 text-base">Tidak ada antrean blast yang sedang berjalan</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                            Seluruh pengiriman selesai. Sistem dalam posisi siaga untuk menerima pesanan blast
                            berikutnya.
                        </p>
                        <a href="{{ route('wa.blast') }}"
                            class="inline-flex items-center gap-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition">
                            + Buat Pengiriman Blast Baru
                        </a>
                    </div>
                @endif
            </div>

            <!-- LAYOUT 2 KOLOM: RIWAYAT BLAST TERAKHIR & LOG AKTIVITAS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- KOLOM 1: RIWAYAT KAMPANYE BLAST TERAKHIR -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            Riwayat Kampanye Blast Terakhir
                        </h3>
                        <a href="{{ route('wa.blast') }}"
                            class="text-xs font-bold text-indigo-600 hover:underline">Semua →</a>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($recentCampaigns as $camp)
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-gray-900 text-sm truncate">
                                        #{{ $camp->id }} - {{ $camp->judul ?: 'WA Blast Campaign' }}
                                    </div>
                                    <div class="text-xs text-gray-400 flex items-center gap-2 mt-0.5">
                                        <span>Target: <strong
                                                class="text-gray-700">{{ $camp->total_target }}</strong></span>
                                        <span>•</span>
                                        <span class="text-emerald-600 font-semibold">✓
                                            {{ $camp->success_count }}</span>
                                        <span class="text-rose-600 font-semibold">✕ {{ $camp->failed_count }}</span>
                                        <span>•</span>
                                        <span>{{ $camp->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($camp->status === 'completed')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span>
                                    @elseif (in_array($camp->status, ['pending', 'processing']))
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Proses</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">{{ $camp->status }}</span>
                                    @endif
                                    <a href="{{ route('wa.blast.campaign.show', $camp->id) }}"
                                        class="p-1.5 text-gray-400 hover:text-indigo-600 transition"
                                        title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 text-xs">
                                Belum ada riwayat kampanye blast.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- KOLOM 2: LOG AKTIVITAS TERKINI (AUDIT TRAIL) -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                            <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </span>
                            Log Aktivitas Sistem Terkini
                        </h3>
                        <a href="{{ route('wa.logs') }}"
                            class="text-xs font-bold text-indigo-600 hover:underline">Lihat Semua →</a>
                    </div>

                    <div class="space-y-4">
                        @forelse ($recentLogs as $log)
                            <div class="flex items-start gap-3 text-xs">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold shrink-0 mt-0.5">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span
                                            class="font-bold text-gray-900 truncate">{{ $log->user?->name ?? 'Sistem' }}</span>
                                        <span
                                            class="text-[10px] text-gray-400 shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">
                                        {{ $log->description }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 text-xs">
                                Belum ada catatan aktivitas.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
