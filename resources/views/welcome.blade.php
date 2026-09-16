<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\AppSetting::getSiteName() }} - Platform WhatsApp Blast & Otomatisasi Pesan</title>
    <link rel="icon" href="{{ \App\Models\AppSetting::getFaviconUrl() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#fcfdfd] text-slate-800 selection:bg-[#128C7E] selection:text-white">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 border-b border-slate-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <x-application-logo class="w-10 h-10 transition-transform group-hover:scale-105" />
                <span
                    class="font-black text-xl tracking-tight text-slate-900 group-hover:text-[#128C7E] transition-colors">
                    {{ \App\Models\AppSetting::getSiteName() }}
                </span>
            </a>

            <!-- Nav Links Desktop -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#fitur" class="hover:text-[#128C7E] transition-colors">Fitur Unggulan</a>
                <a href="#alur-kerja" class="hover:text-[#128C7E] transition-colors">Cara Kerja</a>
                <a href="#keunggulan" class="hover:text-[#128C7E] transition-colors">Keunggulan</a>
                <a href="#faq" class="hover:text-[#128C7E] transition-colors">FAQ</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 bg-[#128C7E] hover:bg-[#0e6b60] text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all">
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 bg-[#128C7E] hover:bg-[#0e6b60] text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all">
                        <span>Masuk</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-12 pb-24 overflow-hidden">
        <!-- Background Glow Accent -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-tr from-emerald-100/50 via-teal-50/40 to-transparent blur-3xl -z-10 pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <!-- Badge Highlight -->
                <div
                    class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200/80 px-4 py-1.5 rounded-full shadow-sm text-xs font-bold text-[#128C7E]">
                    <span class="flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#25D366]"></span>
                    </span>
                    <span>Arsitektur Baru: Antrean Server Latar Belakang (Queue Worker)</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.15]">
                    Kirim Pesan WhatsApp Massal <br class="hidden sm:inline" />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-[#128C7E] via-[#25D366] to-teal-600">
                        Cepat, Cerdas & Bebas Khawatir
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-lg text-slate-600 leading-relaxed font-normal">
                    Jangkau ribuan kontak pelanggan dalam hitungan detik. Pengiriman tetap berjalan di server meski
                    laptop atau browser Anda ditutup. Dilengkapi jeda cerdas anti-bot, Spintax dinamis, dan riwayat log
                    nomor transparan.
                </p>

                <!-- CTA Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    @auth
                        <a href="{{ route('wa.blast') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-3 bg-[#128C7E] hover:bg-[#0e6b60] text-white font-black px-8 py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                            </svg>
                            <span>Buka Halaman Blast</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-3 bg-[#128C7E] hover:bg-[#0e6b60] text-white font-black px-8 py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                            </svg>
                            <span>Mulai Sekarang</span>
                        </a>
                    @endauth
                    <a href="#fitur"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-bold px-7 py-4 rounded-2xl border border-slate-200 shadow-sm transition">
                        <span>Pelajari Fitur</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="flex flex-wrap items-center justify-center gap-6 pt-6 text-xs font-semibold text-slate-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Tahan Tutup Browser
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Jeda Acak Anti-Bot
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Log Audit Transparan
                    </div>
                </div>
            </div>

            <!-- DASHBOARD MOCKUP PREVIEW -->
            <div class="mt-16 max-w-5xl mx-auto">
                <div
                    class="relative rounded-3xl p-3 bg-gradient-to-b from-slate-200/80 to-slate-100/50 shadow-2xl border border-slate-200">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-inner border border-slate-100 overflow-hidden">
                        <!-- Top Header Simulation -->
                        <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                <span class="text-xs font-mono text-slate-400 ml-2">wablast-server-queue // live
                                    monitor</span>
                            </div>
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#128C7E] border border-emerald-100">
                                <span class="w-2 h-2 rounded-full bg-[#25D366] animate-pulse"></span>
                                Background Worker Aktif
                            </div>
                        </div>

                        <!-- Content Simulation -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                            <!-- Left: Active Progress -->
                            <div class="lg:col-span-2 space-y-5">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="text-sm font-bold text-slate-800">Status Pengiriman Massal: Promo
                                            Gajian</div>
                                        <div class="text-xs font-bold text-[#128C7E]">840 / 850 Selesai (98%)</div>
                                    </div>
                                    <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                        <div
                                            class="bg-gradient-to-r from-[#25D366] to-[#128C7E] h-full rounded-full w-[98%] transition-all">
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600 font-mono space-y-2">
                                    <div class="flex items-center justify-between text-emerald-600">
                                        <span>[08:42:15] Sukses kirim ke 0812****890 (Budi Santoso)</span>
                                        <span>✓✓ 200 OK</span>
                                    </div>
                                    <div class="flex items-center justify-between text-emerald-600">
                                        <span>[08:42:18] Sukses kirim ke 0857****123 (Siti Rahma)</span>
                                        <span>✓✓ 200 OK</span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-400">
                                        <span>[08:42:21] Jeda acak anti-bot (3.4 detik)...</span>
                                        <span>Anti-Bot Shield</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Stat Summary -->
                            <div
                                class="p-5 bg-gradient-to-br from-emerald-50/50 to-teal-50/30 rounded-2xl border border-emerald-100/80 flex flex-col justify-between">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Efisiensi
                                        Pengiriman</div>
                                    <div class="text-3xl font-black text-[#128C7E] mt-2">99.8%</div>
                                    <p class="text-xs text-slate-500 mt-1">Tingkat pesan masuk ke kotak masuk penerima
                                        tanpa blokir.</p>
                                </div>
                                <div
                                    class="mt-4 pt-4 border-t border-emerald-100 text-xs text-[#128C7E] font-semibold flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                    Dilindungi Jeda Acak Human-Like
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- METRICS BAR -->
    <section class="py-12 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900">24/7</div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1">Background Queue Server</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-black text-[#128C7E]">99.8%</div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1">Pesan Sukses Terkirim</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900">0 Detik</div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1">Waktu Tunggu di Browser</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-black text-[#128C7E]">100%</div>
                    <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1">Log Audit Transparan</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR UNGGULAN -->
    <section id="fitur" class="py-24 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-[#128C7E] mb-2">Fitur Hebat & Modern</h2>
                <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Dirancang Khusus untuk
                    Kebutuhan Promosi Bisnis Anda</h3>
                <p class="text-sm text-slate-500 mt-3">Tidak perlu repot membiarkan laptop terus menyala atau takut
                    pesan terhenti di tengah jalan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 text-[#128C7E] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Server-Side Queue Worker</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Kirim ribuan pesan langsung ke antrean server. Anda bebas mematikan browser, menutup laptop,
                        atau beralih ke tugas lain tanpa menghentikan blast.
                    </p>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Jeda Cerdas Anti-Bot</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Mencegah pemblokiran akun WhatsApp dengan memberikan jeda acak manusiawi (2-5 detik) antar pesan
                        yang dikirimkan.
                    </p>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Spintax & Personalisasi Nama</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Kirim variasi kalimat acak seperti <code
                            class="text-xs bg-indigo-50 px-1 py-0.5 rounded">[Halo/Hai/Selamat]</code> serta sebut nama
                        pelanggan secara otomatis dengan <code
                            class="text-xs bg-indigo-50 px-1 py-0.5 rounded">{nama}</code>.
                    </p>
                </div>

                <!-- Card 4 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Audit Log & Detail per Nomor</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Ketahui persis nomor mana yang terkirim dan mana yang gagal, lengkap dengan waktu dan keterangan
                        error. Tersedia tombol kirim ulang instan untuk nomor yang gagal.
                    </p>
                </div>

                <!-- Card 5 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Buku Alamat & Impor Excel</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Kelola grup kontak pelanggan dengan mudah. Salin ribuan nomor langsung dari Excel atau Google
                        Sheets dalam format apa saja, sistem otomatis merapikannya.
                    </p>
                </div>

                <!-- Card 6 -->
                <div
                    class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Dukungan Gambar & Brosur</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Tingkatkan konversi penjualan dengan menyertakan foto produk atau pamflet promosi beresolusi
                        tinggi bersama dengan caption pesan blast Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ALUR KERJA (HOW IT WORKS) -->
    <section id="alur-kerja" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-[#128C7E] mb-2">Alur Penggunaan</h2>
                <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Kirim Blast Hanya dalam 3
                    Langkah Sederhana</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <!-- Step 1 -->
                <div class="text-center p-6 space-y-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-emerald-100 text-[#128C7E] font-black text-2xl flex items-center justify-center mx-auto shadow-sm">
                        1
                    </div>
                    <h4 class="text-lg font-bold text-slate-900">Hubungkan WhatsApp</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Cukup scan QR code di menu Setting menggunakan fitur Perangkat Tertaut (Linked Devices) di
                        aplikasi WhatsApp ponsel Anda.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center p-6 space-y-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-emerald-100 text-[#128C7E] font-black text-2xl flex items-center justify-center mx-auto shadow-sm">
                        2
                    </div>
                    <h4 class="text-lg font-bold text-slate-900">Siapkan Pesan & Target</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Tulis template pesan, gunakan Spintax agar pesan tidak seragam, dan tempel daftar nomor kontak
                        pelanggan Anda.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center p-6 space-y-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-emerald-100 text-[#128C7E] font-black text-2xl flex items-center justify-center mx-auto shadow-sm">
                        3
                    </div>
                    <h4 class="text-lg font-bold text-slate-900">Kirim & Biarkan Server Bekerja</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Klik tombol kirim. Anda bisa langsung menutup browser, server antrean akan memproses seluruh
                        pesan satu per satu secara aman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section id="faq" class="py-24 bg-[#f8fafc]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-[#128C7E] mb-2">Pertanyaan Umum</h2>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">Kerap Ditanyakan Mengenai Sistem</h3>
            </div>

            <div class="space-y-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h4 class="font-bold text-slate-900 text-base mb-2">Apakah browser boleh ditutup saat proses blast
                        berjalan?</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Ya, tentu saja! Berkat sistem Background Queue Worker, proses pengiriman berjalan langsung di
                        server kami. Anda bisa menutup browser, mematikan laptop, ataupun beralih ke aktivitas lain.
                        Saat Anda membuka kembali halaman, status pengiriman akan langsung diperbarui.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h4 class="font-bold text-slate-900 text-base mb-2">Bagaimana cara kerja fitur Jeda Anti-Bot?</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Setiap pesan yang dikirim akan diberikan jeda acak alami antara 2 hingga 5 detik. Ini membuat
                        pola pengiriman terbaca seperti interaksi manusia biasa dan melindungi akun WhatsApp Anda dari
                        deteksi spam.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h4 class="font-bold text-slate-900 text-base mb-2">Bagaimana cara kerja Spintax?</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Anda dapat menulis format seperti <code
                            class="bg-slate-100 px-1.5 py-0.5 rounded text-xs">[Halo/Hai/Selamat Pagi]</code>. Sistem
                        secara otomatis memilih salah satu kata secara acak untuk setiap penerima, sehingga tidak ada
                        dua pesan yang identik.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION BANNER -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-[#128C7E] to-teal-800 p-8 sm:p-14 text-white text-center shadow-xl relative overflow-hidden">
                <div class="max-w-2xl mx-auto relative z-10 space-y-6">
                    <h3 class="text-3xl sm:text-4xl font-black tracking-tight">Tingkatkan Omzet Bisnis Anda dengan
                        WhatsApp Blast Sekarang</h3>
                    <p class="text-emerald-100 text-sm sm:text-base leading-relaxed">
                        Dapatkan kemudahan promosi langsung ke genggaman pelanggan dengan teknologi background queue
                        terdepan.
                    </p>
                    <div class="pt-2">
                        @auth
                            <a href="{{ route('wa.blast') }}"
                                class="inline-flex items-center gap-2 bg-white text-[#128C7E] font-black px-8 py-4 rounded-2xl shadow hover:bg-slate-100 transition-all text-sm">
                                Masuk ke Panel Blast
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center gap-2 bg-white text-[#128C7E] font-black px-8 py-4 rounded-2xl shadow hover:bg-slate-100 transition-all text-sm">
                                Mulai Sekarang
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-xs">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <x-application-logo class="w-7 h-7 text-white" />
                <span
                    class="font-bold text-white text-sm tracking-tight">{{ \App\Models\AppSetting::getSiteName() }}</span>
            </div>
            <p>&copy; {{ date('Y') }} {{ \App\Models\AppSetting::getSiteName() }}. Seluruh hak cipta dilindungi.
            </p>
            <div class="flex items-center gap-6 font-semibold">
                <a href="{{ route('wa.setting') }}" class="hover:text-white transition-colors">Pengaturan</a>
                <a href="{{ route('wa.logs') }}" class="hover:text-white transition-colors">Log Aktivitas</a>
                <a href="{{ route('wa.blast') }}" class="hover:text-white transition-colors">Kirim Blast</a>
            </div>
        </div>
    </footer>

</body>

</html>
