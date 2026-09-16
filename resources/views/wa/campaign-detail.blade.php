<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('wa.blast') }}"
                    class="p-2 bg-white rounded-xl border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                        Rincian Blast: {{ $campaign->judul }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Dibuat pada
                        {{ $campaign->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>

            @if ($campaign->failed_count > 0)
                <div class="flex items-center gap-2">
                    <a href="{{ route('wa.blast', ['load_failed_campaign' => $campaign->id]) }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit & Kirim Ulang di Halaman Blast
                    </a>
                    <form action="{{ route('wa.blast.campaign.retry', $campaign->id) }}" method="POST"
                        onsubmit="return confirm('Kirim ulang pesan ke {{ $campaign->failed_count }} nomor yang gagal?')">
                        @csrf
                        <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Langsung Kirim Ulang ({{ $campaign->failed_count }} Nomor)
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- STATS CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Target</div>
                    <div class="text-3xl font-black text-gray-800 mt-2">{{ $campaign->total_target }}</div>
                    <div class="text-xs text-gray-500 mt-1">Kontak terdaftar</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Berhasil Terkirim</div>
                    <div class="text-3xl font-black text-[#128C7E] mt-2">{{ $campaign->success_count }}</div>
                    <div class="text-xs text-emerald-600 mt-1">
                        {{ $campaign->total_target > 0 ? round(($campaign->success_count / $campaign->total_target) * 100) : 0 }}%
                        sukses
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider text-rose-500">Gagal</div>
                    <div class="text-3xl font-black text-rose-500 mt-2">{{ $campaign->failed_count }}</div>
                    <div class="text-xs text-rose-500 mt-1">
                        {{ $campaign->total_target > 0 ? round(($campaign->failed_count / $campaign->total_target) * 100) : 0 }}%
                        gagal
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Status Proses</div>
                    <div class="mt-2">
                        @if ($campaign->status === 'completed')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                Selesai
                            </span>
                        @elseif ($campaign->status === 'processing')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 animate-pulse">
                                Sedang Mengirim...
                            </span>
                        @elseif ($campaign->status === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                Dalam Antrean
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400 mt-2">
                        {{ $campaign->completed_at ? 'Selesai ' . $campaign->completed_at->diffForHumans() : 'Belum selesai' }}
                    </div>
                </div>
            </div>

            <!-- INFO PESAN & MEDIA -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Template Pesan yang Dikirim
                </h3>
                <div
                    class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 text-sm whitespace-pre-wrap font-sans">
                    {{ $campaign->pesan }}
                </div>
                @if ($campaign->media_path)
                    <div class="mt-4 flex items-center gap-4">
                        <div class="text-xs font-semibold text-gray-500">Lampiran Gambar:</div>
                        <a href="{{ Storage::url($campaign->media_path) }}" target="_blank"
                            class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            Buka Gambar
                        </a>
                    </div>
                @endif
            </div>

            <!-- TABEL RINCIAN TIAP NOMOR TARGET -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Toolbar Filter Status & Search -->
                <div
                    class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gray-50/50">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('wa.blast.campaign.show', ['id' => $campaign->id, 'search' => request('search')]) }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                            Semua ({{ $campaign->total_target }})
                        </a>
                        <a href="{{ route('wa.blast.campaign.show', ['id' => $campaign->id, 'status' => 'sent', 'search' => request('search')]) }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'sent' ? 'bg-[#128C7E] text-white' : 'bg-white text-[#128C7E] border border-emerald-200 hover:bg-emerald-50' }}">
                            Terkirim ({{ $campaign->success_count }})
                        </a>
                        <a href="{{ route('wa.blast.campaign.show', ['id' => $campaign->id, 'status' => 'failed', 'search' => request('search')]) }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'failed' ? 'bg-rose-600 text-white' : 'bg-white text-rose-600 border border-rose-200 hover:bg-rose-50' }}">
                            Gagal ({{ $campaign->failed_count }})
                        </a>
                        <a href="{{ route('wa.blast.campaign.show', ['id' => $campaign->id, 'status' => 'pending', 'search' => request('search')]) }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white' : 'bg-white text-amber-600 border border-amber-200 hover:bg-amber-50' }}">
                            Menunggu
                            ({{ max(0, $campaign->total_target - ($campaign->success_count + $campaign->failed_count)) }})
                        </a>
                    </div>

                    <form method="GET" action="{{ route('wa.blast.campaign.show', $campaign->id) }}"
                        class="flex items-center gap-2">
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor atau nama..."
                            class="rounded-xl border-gray-200 text-xs py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 w-48 md:w-64">
                        <button type="submit"
                            class="bg-gray-800 text-white px-3 py-2 rounded-xl text-xs font-semibold hover:bg-gray-700 transition">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Tabel Data Penerima -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr
                                class="bg-gray-50/80 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-4 px-6">Nomor HP</th>
                                <th class="py-4 px-6">Nama</th>
                                <th class="py-4 px-6">Pesan yang Dikirim</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6">Waktu Kirim</th>
                                <th class="py-4 px-6">Keterangan / Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($recipients as $recipient)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-gray-800 text-xs whitespace-nowrap">
                                        <a href="https://wa.me/{{ $recipient->nomor }}" target="_blank"
                                            class="hover:text-[#128C7E] flex items-center gap-1.5" title="Buka di WA">
                                            <span>{{ $recipient->nomor }}</span>
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                </path>
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="py-4 px-6 text-xs font-medium text-gray-700 whitespace-nowrap">
                                        {{ $recipient->nama ?: '-' }}
                                    </td>
                                    <td class="py-4 px-6 max-w-xs text-xs text-gray-600 truncate"
                                        title="{{ $recipient->pesan_personal }}">
                                        {{ $recipient->pesan_personal }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if ($recipient->status === 'sent')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Terkirim
                                            </span>
                                        @elseif ($recipient->status === 'failed')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Gagal
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-gray-500">
                                        {{ $recipient->sent_at ? $recipient->sent_at->format('d/m/Y H:i:s') : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-xs text-rose-500 max-w-xs truncate"
                                        title="{{ $recipient->error_message }}">
                                        {{ $recipient->error_message ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400 text-sm">
                                        Tidak ada nomor pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recipients->hasPages())
                    <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                        {{ $recipients->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
