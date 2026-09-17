<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
            <span class="text-[#25D366] mr-3">
                <svg class="w-8 h-8 drop-shadow-sm" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.383-.058c.104-.13.454-.53.577-.713.123-.183.243-.153.401-.094.159.058 1.006.474 1.179.561.174.087.291.13.334.202.043.073.043.423-.101.827z" />
                </svg>
            </span>
            Kirim Pesan Blast
        </h2>
    </x-slot>

    <div class="py-8 bg-[#f0f2f5] min-h-screen" x-data="blastForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alert Messages -->
            @if (session('success'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl shadow-sm mb-6 flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-8">

                <!-- BAGIAN KIRI: Form Input -->
                <div class="w-full lg:w-3/5 space-y-6">

                    <!-- PROGRESS BAR MODAL (Overlay) -->
                    <div x-show="isSending && !hideSendingModal" x-transition
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
                        style="display: none;">
                        <div
                            class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md text-center transform scale-100 transition-all">
                            <div
                                class="w-20 h-20 mx-auto mb-4 bg-emerald-50 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-[#128C7E] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Mengirim di Latar Belakang...</h3>
                            <p class="text-sm text-gray-500 mb-6">Harap tunggu. <span x-text="sentCount"
                                    class="font-bold text-gray-800"></span> dari <span x-text="totalTarget"
                                    class="font-bold text-gray-800"></span> nomor selesai diproses.</p>

                            <div
                                class="w-full bg-gray-100 rounded-full h-5 mb-4 overflow-hidden shadow-inner border border-gray-200">
                                <div class="bg-gradient-to-r from-[#25D366] to-[#128C7E] h-5 rounded-full transition-all duration-300 relative flex items-center justify-end px-2"
                                    :style="`width: ${progress}%`">
                                    <div class="absolute inset-0 bg-white/20"
                                        style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,0.1) 10px, rgba(0,0,0,0.1) 20px);">
                                    </div>
                                    <span class="text-[10px] font-bold text-white relative z-10" x-show="progress > 10"
                                        x-text="`${progress}%`"></span>
                                </div>
                            </div>

                            <div
                                class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-start gap-2 text-left mb-4">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><strong>Aman Ditinggal:</strong> Pengiriman berjalan di server (Background Queue).
                                    Anda bebas menutup browser atau laptop kapan saja!</span>
                            </div>

                            <button type="button" @click="hideSendingModal = true"
                                class="text-xs text-gray-500 hover:text-gray-800 font-semibold py-1.5 px-3 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                                Sembunyikan Jendela (Biarkan Berjalan di Latar Belakang)
                            </button>
                        </div>
                    </div>

                    <!-- FLOATING MINI PROGRESS BAR (Keterangan Latar Belakang) -->
                    <div x-show="isSending && hideSendingModal" x-transition
                        class="fixed bottom-6 right-6 z-40 bg-gray-900/90 text-white p-4 rounded-2xl shadow-2xl border border-gray-700/80 backdrop-blur-md flex items-center gap-4"
                        style="display: none;">
                        <div class="flex items-center gap-3">
                            <div class="relative flex items-center justify-center w-8 h-8 shrink-0">
                                <svg class="w-8 h-8 text-[#25D366] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-100 flex items-center gap-1.5">
                                    <span>Pengiriman Blast Aktif</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                </div>
                                <div class="text-[11px] text-gray-300">
                                    <span x-text="sentCount" class="font-bold text-white"></span> / <span
                                        x-text="totalTarget" class="font-bold text-white"></span> nomor (<span
                                        x-text="`${progress}%`" class="text-emerald-400 font-bold"></span>)
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="hideSendingModal = false"
                            class="bg-[#128C7E] hover:bg-[#0e6b60] text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow-sm shrink-0 cursor-pointer">
                            Buka Progress
                        </button>
                    </div>

                    <!-- HASIL AKHIR MODAL -->
                    <div x-show="showResult" x-transition
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
                        style="display: none;">
                        <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md text-center">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5"
                                :class="gagalCount > 0 ? 'bg-amber-100 text-amber-500' : 'bg-emerald-100 text-[#128C7E]'">
                                <svg x-show="gagalCount === 0" class="w-10 h-10" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg x-show="gagalCount > 0" class="w-10 h-10" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2"
                                x-text="gagalCount > 0 ? 'Proses Selesai (Ada Gagal)' : 'Blast Berhasil 100%!'">
                            </h3>
                            <div
                                class="flex justify-center gap-6 my-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-bold">Berhasil</p>
                                    <p class="text-2xl font-black text-[#128C7E]" x-text="berhasilCount">
                                    </p>
                                </div>
                                <div class="w-px bg-gray-300"></div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-bold">Gagal</p>
                                    <p class="text-2xl font-black text-rose-500" x-text="gagalCount"></p>
                                </div>
                            </div>

                            <template x-if="currentCampaignId && gagalCount > 0">
                                <button type="button" @click="loadFailedToForm(currentCampaignId)"
                                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl mb-3 shadow-md transition-all text-center text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Muat Nomor Gagal ke Form Input
                                </button>
                            </template>

                            <template x-if="currentCampaignId">
                                <a :href="`/wa/blast/campaign/${currentCampaignId}`"
                                    class="block w-full bg-[#128C7E] hover:bg-[#0e6b60] text-white font-bold py-3.5 px-4 rounded-xl mb-3 shadow-md transition-all text-center text-sm">
                                    Lihat Rincian Status Per Nomor
                                </a>
                            </template>
                            <button @click="showResult = false; window.location.reload();"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all text-sm">
                                Tutup Jendela
                            </button>
                        </div>
                    </div>

                    <!-- FORM 1: TARGET NOMOR -->
                    <div
                        class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden group hover:shadow-md transition-all duration-300">
                        <div
                            class="bg-gray-50/80 px-6 py-4 border-b border-gray-100 flex justify-between items-center gap-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="bg-[#128C7E] text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shadow-sm">1</span>
                                <h3 class="font-bold text-gray-800 text-lg">Daftar Kontak Target</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Tombol Bersihkan Kontak Target -->
                                <button type="button" @click="clearAllTargets()"
                                    :disabled="targetList.length === 0 && !newTargetContact"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-full border transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100/80 border-rose-200 shadow-2xs"
                                    title="Bersihkan seluruh daftar kontak target">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    <span>Bersihkan</span>
                                </button>
                                <a href="#section-kontak"
                                    class="text-xs font-bold text-[#128C7E] bg-emerald-50 border border-emerald-100 px-4 py-2 rounded-full hover:bg-[#128C7E] hover:text-white transition-all flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                    Buku Alamat
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            <!-- DROPDOWN TARIK NOMOR KONTAK DARI GRUP WHATSAPP -->
                            <div
                                class="mb-5 bg-white rounded-2xl border border-emerald-200/80 shadow-sm overflow-hidden transition-all duration-300">
                                <!-- Dropdown Header / Toggle Bar -->
                                <div @click="toggleGroupDropdown()"
                                    class="w-full px-4 py-3.5 bg-gradient-to-r from-emerald-50/90 to-[#e7ffdb]/70 hover:from-emerald-100/90 hover:to-[#e7ffdb] flex items-center justify-between cursor-pointer select-none transition-colors border-b border-emerald-100/80">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#128C7E] to-[#075e54] text-white flex items-center justify-center shadow-sm shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                        </span>
                                        <div class="text-left">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-gray-800">Pilih Grup WhatsApp (Kirim
                                                    Pesan ke Grup)</h4>
                                                <span
                                                    class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-white border border-emerald-200 text-emerald-900 font-bold shadow-2xs flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    Instance: <span x-text="userInstance"></span>
                                                </span>
                                                <template x-if="isInstanceConnected">
                                                    <span
                                                        class="text-[11px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full flex items-center gap-1">
                                                        <span
                                                            class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Terhubung
                                                    </span>
                                                </template>
                                                <template x-if="!isInstanceConnected">
                                                    <span
                                                        class="text-[11px] font-bold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full flex items-center gap-1">
                                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                                        Belum Terhubung
                                                    </span>
                                                </template>
                                                <span x-show="accountGroups.length > 0"
                                                    class="text-[11px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full"
                                                    x-text="accountGroups.length + ' grup aktif'"></span>
                                            </div>
                                            <p class="text-xs text-gray-500">Pilih grup untuk dikirimi pesan langsung
                                                ke grup, atau tarik nomor kontak anggota.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span x-show="!openGroupDropdown"
                                            class="hidden sm:inline-block text-xs font-semibold text-[#128C7E] bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                                            Buka Dropdown
                                        </span>
                                        <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                            :class="{ 'rotate-180 text-[#128C7E]': openGroupDropdown }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Dropdown Content / Collapsible Panel -->
                                <div x-show="openGroupDropdown" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;"
                                    class="p-4 bg-gray-50/50 space-y-4">
                                    <!-- Disconnected Warning Banner -->
                                    <template x-if="!isInstanceConnected">
                                        <div
                                            class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
                                            <div class="flex items-start gap-2.5">
                                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div>
                                                    <p class="font-bold text-amber-900">Instance WhatsApp (<span
                                                            x-text="userInstance"></span>) Belum Terhubung</p>
                                                    <p class="text-amber-700 mt-0.5">Daftar grup WhatsApp hanya
                                                        menampilkan grup dari nomor instance akun Anda. Hubungkan
                                                        WhatsApp terlebih dahulu di menu Pengaturan.</p>
                                                </div>
                                            </div>
                                            <a href="{{ route('wa.setting') }}"
                                                class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded-lg transition shadow-xs">
                                                Hubungkan WhatsApp
                                            </a>
                                        </div>
                                    </template>

                                    <!-- Action Toolbar -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                        <div class="relative flex-1">
                                            <input type="text" x-model="groupSearch"
                                                placeholder="Cari nama grup WhatsApp..."
                                                class="w-full text-xs bg-white border border-gray-200 rounded-xl pl-8 pr-3 py-2 focus:border-[#128C7E] focus:ring-1 focus:ring-[#128C7E] shadow-2xs">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="fetchAccountGroups(true)"
                                                :disabled="isLoadingGroups"
                                                class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 transition shadow-2xs disabled:opacity-50">
                                                <svg x-show="!isLoadingGroups" class="w-3.5 h-3.5 text-[#128C7E]"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                    </path>
                                                </svg>
                                                <svg x-show="isLoadingGroups" style="display: none;"
                                                    class="w-3.5 h-3.5 animate-spin text-[#128C7E]" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <span
                                                    x-text="isLoadingGroups ? 'Memvalidasi...' : 'Segarkan Grup'"></span>
                                            </button>

                                            <template x-if="accountGroups.length > 0">
                                                <div class="flex items-center gap-1.5 text-xs">
                                                    <button type="button" @click="selectAllAccountGroups(true)"
                                                        class="text-[#128C7E] hover:underline font-bold px-1.5 py-1">Pilih
                                                        Semua</button>
                                                    <span class="text-gray-300">|</span>
                                                    <button type="button" @click="selectAllAccountGroups(false)"
                                                        class="text-rose-500 hover:underline font-bold px-1.5 py-1">Batal</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Scrollable Groups List -->
                                    <div
                                        class="max-h-56 overflow-y-auto bg-white rounded-xl border border-gray-200 p-2 space-y-1 shadow-inner">
                                        <template x-if="isLoadingGroups && accountGroups.length === 0">
                                            <div
                                                class="py-8 text-center text-xs text-gray-500 flex flex-col items-center justify-center gap-2">
                                                <svg class="w-6 h-6 animate-spin text-[#128C7E]" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <span>Memeriksa dan memvalidasi grup aktif dari WhatsApp
                                                    Gateway...</span>
                                            </div>
                                        </template>

                                        <template
                                            x-if="!isLoadingGroups && accountGroups.length === 0 && !isInstanceConnected">
                                            <div class="py-8 text-center text-xs text-amber-700 space-y-1">
                                                <p class="font-bold">WhatsApp pada instance "<span
                                                        x-text="userInstance"></span>" belum terhubung.</p>
                                                <p class="text-gray-400">Hubungkan WhatsApp di menu Pengaturan untuk
                                                    memuat dan memilih grup.</p>
                                            </div>
                                        </template>

                                        <template
                                            x-if="!isLoadingGroups && accountGroups.length === 0 && isInstanceConnected">
                                            <div class="py-8 text-center text-xs text-gray-400">
                                                Belum ada grup yang dimuat untuk instance "<span
                                                    x-text="userInstance"></span>". Klik tombol <strong>"Segarkan
                                                    Grup"</strong>
                                                di atas.
                                            </div>
                                        </template>

                                        <template x-for="g in filteredAccountGroups" :key="g.id">
                                            <div
                                                class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-slate-100 transition-colors gap-2">
                                                <label class="flex items-center gap-2.5 min-w-0 cursor-pointer flex-1">
                                                    <input type="checkbox" :value="g.id"
                                                        x-model="selectedGroupIds"
                                                        class="w-4 h-4 text-[#128C7E] rounded border-gray-300 focus:ring-[#128C7E]">
                                                    <div class="min-w-0">
                                                        <span class="font-bold text-gray-800 text-xs truncate block"
                                                            x-text="g.subject"></span>
                                                        <span class="text-[10px] text-gray-400 font-normal block"
                                                            x-text="g.size ? g.size + ' anggota' : 'Grup Aktif'"></span>
                                                    </div>
                                                </label>

                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <!-- Tombol 1: Tambahkan Grup Langsung ke Target (Kirim Pesan ke Grup) -->
                                                    <button type="button" @click="addGroupToTarget(g)"
                                                        class="text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg transition flex items-center gap-1"
                                                        title="Tambahkan grup ini langsung ke daftar target pengiriman">
                                                        <span>+ Pilih Grup</span>
                                                    </button>
                                                    <!-- Tombol 2: Tarik Nomor Anggota Grup (Japri) -->
                                                    <button type="button" @click="extractGroupParticipants(g.id)"
                                                        :disabled="isExtractingParticipants"
                                                        class="text-[11px] font-semibold text-gray-500 hover:text-gray-800 bg-white hover:bg-gray-50 border border-gray-200 px-2 py-1 rounded-lg transition"
                                                        title="Tarik nomor kontak orang-orang anggota dari grup ini ke daftar target">
                                                        📥 Anggota
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <template
                                            x-if="accountGroups.length > 0 && filteredAccountGroups.length === 0">
                                            <div class="text-center py-6 text-xs text-gray-400">
                                                Tidak ada grup yang cocok dengan kata kunci "<span
                                                    x-text="groupSearch"></span>".
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Multi-Action Footer -->
                                    <div
                                        class="pt-2.5 border-t border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="text-xs text-gray-600">
                                            Terpilih: <strong class="text-[#128C7E]"
                                                x-text="selectedGroupIds.length"></strong> grup
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <!-- Action Utama: Tambahkan Grup Terpilih Langsung (Kirim ke Grup) -->
                                            <button type="button" @click="addSelectedGroupsToTargets()"
                                                :disabled="selectedGroupIds.length === 0"
                                                class="bg-[#128C7E] hover:bg-[#075e54] text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow-sm disabled:opacity-40 inline-flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                <span>+ Tambah <span
                                                        x-text="selectedGroupIds.length > 0 ? selectedGroupIds.length + ' ' : ''"></span>Grup
                                                    Terpilih</span>
                                            </button>

                                            <!-- Action Sekunder: Tarik Kontak Anggota (Japri Orang-orang) -->
                                            <button type="button" @click="extractGroupParticipants()"
                                                :disabled="selectedGroupIds.length === 0 || isExtractingParticipants"
                                                class="bg-white hover:bg-gray-50 text-gray-700 hover:text-emerald-700 text-xs font-bold px-3 py-2 rounded-xl border border-gray-200 transition shadow-2xs disabled:opacity-40 inline-flex items-center gap-1.5"
                                                title="Tarik seluruh nomor kontak orang-orang anggota dari grup terpilih">
                                                <svg x-show="!isExtractingParticipants"
                                                    class="w-3.5 h-3.5 text-gray-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                    </path>
                                                </svg>
                                                <svg x-show="isExtractingParticipants" style="display: none;"
                                                    class="w-3.5 h-3.5 animate-spin" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <span
                                                    x-text="isExtractingParticipants ? 'Mengekstrak Anggota...' : '📥 Tarik Anggota (Japri)'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Feedback / Status Alert -->
                                    <div x-show="groupFetchMessage" style="display: none;"
                                        class="p-2.5 bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 rounded-xl flex items-center justify-between gap-2">
                                        <span x-text="groupFetchMessage"></span>
                                        <button type="button" @click="groupFetchMessage = ''"
                                            class="text-emerald-600 hover:text-emerald-900 font-bold text-sm leading-none">&times;</button>
                                    </div>
                                </div>
                            </div>

                            <!-- KOTAK CONTOH PENGISIAN NOMOR -->
                            <div
                                class="mb-5 bg-gradient-to-r from-blue-50 to-indigo-50/50 rounded-xl p-4 border border-blue-100/60 shadow-inner">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h4 class="text-sm font-bold text-blue-900">Cara Pengisian Nomor (Bisa Paste dari
                                        Excel / CSV)</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-mono">
                                    <div
                                        class="bg-white px-3 py-2 rounded-lg border border-blue-100 flex items-center gap-2 text-gray-600">
                                        <span class="text-green-500">✓</span> Budi - 08123456789
                                    </div>
                                    <div
                                        class="bg-white px-3 py-2 rounded-lg border border-blue-100 flex items-center gap-2 text-gray-600">
                                        <span class="text-green-500">✓</span> Siti - +62856777888
                                    </div>
                                    <div
                                        class="bg-white px-3 py-2 rounded-lg border border-blue-100 flex items-center gap-2 text-gray-600">
                                        <span class="text-green-500">✓</span> 62895350667734
                                    </div>
                                    <div
                                        class="bg-white px-3 py-2 rounded-lg border border-blue-100 flex items-center gap-2 text-gray-600">
                                        <span class="text-green-500">✓</span> 089777888999
                                    </div>
                                </div>
                            </div>

                            <!-- KOTAK SMART INPUT (CHIPS) UTAMA -->
                            <div class="mb-2 relative">
                                <input type="hidden" name="target" :value="targetInput">

                                <div
                                    class="w-full border border-gray-300 focus-within:border-[#128C7E] focus-within:ring-1 focus-within:ring-[#128C7E] rounded-xl bg-white transition-colors overflow-hidden flex flex-col h-[280px] shadow-sm">

                                    <!-- Area Daftar Kontak (Scrollable) -->
                                    <div
                                        class="flex-1 overflow-y-auto p-4 flex flex-wrap gap-2 items-start content-start border-b border-gray-100 relative">
                                        <!-- Jika Kosong -->
                                        <div x-show="targetList.length === 0"
                                            class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 gap-3">
                                            <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            <span class="text-sm">Belum ada target. Paste data Excel Anda di bawah
                                                ini.</span>
                                        </div>

                                        <!-- Looping Chip Nomor -->
                                        <template x-for="(contact, index) in targetList" :key="index">
                                            <span
                                                class="inline-flex items-center gap-1.5 bg-[#e7ffdb] border border-[#a1df83] text-[#075e54] px-3 py-1.5 rounded-lg text-sm font-medium shadow-sm group hover:border-[#128C7E] transition-all">
                                                <span x-text="contact"></span>
                                                <button type="button" @click="removeTargetContact(index)"
                                                    class="text-[#128C7E] hover:text-white hover:bg-red-500 rounded-md p-0.5 transition-colors focus:outline-none"
                                                    title="Hapus kontak">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Area Input Teks (Paste & Ketik) -->
                                    <div class="p-2.5 bg-slate-100 border-t border-slate-200 relative z-20">
                                        <textarea x-ref="inputTargetTextarea" x-model="newTargetContact" @paste="handleMainPaste($event)"
                                            @keydown.enter="handleMainEnter($event)" rows="2"
                                            class="w-full border border-slate-200 focus:border-[#128C7E] focus:ring-1 focus:ring-[#128C7E] text-sm resize-none bg-white rounded-xl p-3 shadow-sm placeholder-slate-400 transition-all"
                                            placeholder="Ketik nomor lalu tekan Enter, atau PASTE data Excel (Nama & Nomor) ke sini..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-100">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs text-gray-500 font-medium">Sistem otomatis mendeteksi &
                                        mencegah nomor ganda.</span>
                                    <button type="button" @click="formatAndCleanTargets()"
                                        class="text-xs bg-emerald-100 hover:bg-[#128C7E] text-[#075e54] hover:text-white px-2.5 py-1 rounded-md font-bold transition-all flex items-center gap-1 shadow-sm">
                                        <span>✨</span> Rapikan & Format Otomatis
                                    </button>
                                    <button type="button" @click="clearAllTargets()"
                                        x-show="targetList.length > 0 || newTargetContact"
                                        class="text-xs text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-md font-bold transition-all flex items-center gap-1 shadow-2xs"
                                        title="Kosongkan seluruh daftar kontak target">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Bersihkan Target
                                    </button>
                                </div>
                                <span class="text-sm text-gray-600 font-bold">Total Target: <span x-text="targetCount"
                                        class="text-[#128C7E] text-lg bg-emerald-100 px-2 py-0.5 rounded-md ml-1 shadow-sm">0</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- FORM 2: KONTEN PESAN -->
                    <div
                        class="bg-white shadow-sm sm:rounded-[24px] border border-slate-200 overflow-hidden transition-all">
                        <div
                            class="px-6 py-4 sm:py-5 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">
                            <div class="flex items-center gap-3">
                                <span
                                    class="bg-[#128C7E] text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shadow-sm">2</span>
                                <h3 class="font-bold text-slate-800 text-lg">Konten Pesan & Media</h3>
                            </div>
                            <!-- Tombol Bersihkan Seluruhnya -->
                            <button type="button" @click="clearMessageAndMedia()"
                                :disabled="!pesanInput && !imageUrl && !imageBase64"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100/80 border-rose-200/80 shadow-2xs"
                                title="Bersihkan seluruh isi teks pesan dan lampiran media">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                <span>Bersihkan Seluruhnya</span>
                            </button>
                        </div>
                        <div class="p-6 pt-5">

                            <!-- KOTAK TIPS PENGISIAN PESAN -->
                            <div
                                class="mb-5 bg-orange-50 rounded-xl p-4 border border-orange-200 flex items-start gap-3 shadow-sm">
                                <div class="mt-0.5">
                                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-xs text-orange-800 leading-relaxed w-full">
                                    <h4 class="text-sm font-bold text-orange-900 mb-2">Tips Format Pesan Cerdas</h4>
                                    <ul class="space-y-1.5">
                                        <li>1. Gunakan <code
                                                class="bg-white border border-orange-200 px-1.5 py-0.5 rounded text-orange-600 font-bold shadow-sm">{nama}</code>
                                            untuk menyapa otomatis.</li>
                                        <li>2. Gunakan <code
                                                class="bg-white border border-orange-200 px-1.5 py-0.5 rounded text-orange-600 font-bold shadow-sm">[Kata1/Kata2]</code>
                                            (Spintax) agar sistem mengacak kata untuk mencegah deteksi spam.</li>
                                    </ul>
                                    <div
                                        class="mt-3 p-2.5 bg-white rounded-lg border border-orange-200 font-mono text-[11px] text-orange-700 shadow-sm">
                                        <span class="font-semibold text-orange-900 font-sans">Contoh:</span><br>
                                        "[Halo/Hai/Selamat Pagi] {nama}, [silakan/mari] cek promo kami."
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Media -->
                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-2">
                                    <label
                                        class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Lampiran
                                        Media (Opsional)</label>
                                    <template x-if="imageUrl">
                                        <button type="button" @click="clearMedia()"
                                            class="text-[11px] text-rose-600 hover:text-rose-800 font-bold hover:underline flex items-center gap-1 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Hapus Media
                                        </button>
                                    </template>
                                </div>
                                <div
                                    class="relative border-2 border-dashed border-slate-200 rounded-xl p-3 hover:border-[#128C7E] bg-slate-50/50 transition-colors">
                                    <input type="file" x-ref="imageInput" accept="image/*" @change="previewImage"
                                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer focus:outline-none transition-colors">
                                </div>
                            </div>

                            <!-- Toolbar & Textarea -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-2 uppercase tracking-wide">Teks
                                    Pesan</label>
                                <div
                                    class="border border-slate-200 rounded-xl overflow-hidden focus-within:border-[#128C7E] focus-within:ring-1 focus-within:ring-[#128C7E] transition-all bg-white shadow-sm">

                                    <!-- Toolbar Ikon -->
                                    <div
                                        class="bg-slate-50 border-b border-slate-200 px-3 py-2 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="insertFormat('*')"
                                                class="p-1.5 text-slate-500 hover:bg-slate-200 hover:text-slate-800 rounded-md transition-colors"
                                                title="Tebal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                                </svg>
                                            </button>
                                            <button type="button" @click="insertFormat('_')"
                                                class="p-1.5 text-slate-500 hover:bg-slate-200 hover:text-slate-800 rounded-md transition-colors"
                                                title="Miring">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                                </svg>
                                            </button>
                                            <button type="button" @click="insertFormat('~')"
                                                class="p-1.5 text-slate-500 hover:bg-slate-200 hover:text-slate-800 rounded-md transition-colors"
                                                title="Coret">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"></path>
                                                </svg>
                                            </button>

                                            <div class="w-px h-4 bg-slate-300 mx-1"></div>

                                            <div class="relative">
                                                <button type="button" @click="showEmoji = !showEmoji"
                                                    class="p-1.5 text-slate-500 hover:bg-slate-200 hover:text-slate-800 rounded-md transition-colors flex items-center gap-1"
                                                    title="Sisipkan Emoji">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    </svg>
                                                </button>
                                                <div x-show="showEmoji" @click.away="showEmoji = false" x-transition
                                                    class="absolute z-20 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg p-2 grid grid-cols-5 gap-1"
                                                    style="display: none;">
                                                    <template x-for="emoji in emojis">
                                                        <button type="button" @click="insertEmoji(emoji)"
                                                            class="text-lg hover:bg-slate-100 rounded transition-transform hover:scale-110"
                                                            x-text="emoji"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Indikator Karakter & Bersihkan Teks -->
                                        <div class="flex items-center gap-2">
                                            <span x-show="pesanInput && pesanInput.length > 0"
                                                class="text-[11px] text-slate-400 font-medium"
                                                x-text="pesanInput.length + ' karakter'"></span>
                                            <button type="button"
                                                @click="pesanInput = ''; if ($refs.pesanTextarea) $refs.pesanTextarea.focus();"
                                                x-show="pesanInput && pesanInput.length > 0"
                                                class="text-[11px] text-rose-500 hover:text-rose-700 font-semibold hover:underline"
                                                title="Kosongkan teks pesan">
                                                Hapus Teks
                                            </button>
                                        </div>
                                    </div>
                                    <textarea x-ref="pesanTextarea" x-model="pesanInput" rows="6" placeholder="Ketik pesan Anda di sini..."
                                        class="w-full border-0 focus:ring-0 p-4 text-sm resize-y text-slate-700 bg-transparent" required></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TOMBOL SUBMIT & ANTI BOT -->
                    <div class="space-y-4">
                        <label
                            class="flex items-center gap-3 p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl cursor-pointer hover:bg-emerald-50 transition-colors">
                            <input type="checkbox" x-model="antiBot"
                                class="w-5 h-5 text-[#128C7E] rounded-md focus:ring-[#128C7E] border-emerald-300">
                            <div>
                                <span class="text-sm font-bold text-emerald-800 block">Gunakan Mode Anti-Bot
                                    (Anti-Banned)</span>
                                <span class="text-[11px] text-emerald-600 font-medium">Sistem akan memberi jeda acak
                                    2-6 detik antar pesan seolah diketik manusia.</span>
                            </div>
                        </label>

                        <button type="button" @click="executeBlast()"
                            class="w-full bg-[#128C7E] hover:bg-[#075e54] text-white font-bold py-4 px-4 rounded-2xl shadow-sm transition-all flex justify-center items-center gap-2 text-[15px]">
                            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path>
                            </svg>
                            Mulai Kirim Blast
                        </button>
                    </div>
                </div>

                <!-- BAGIAN KANAN: Live Preview (Mobile Mockup) -->
                <div class="w-full lg:w-2/5 pt-4 lg:sticky lg:top-8 h-max flex justify-center">
                    <div
                        class="w-[330px] bg-white rounded-[45px] border-[12px] border-gray-900 shadow-2xl overflow-hidden flex flex-col relative aspect-[9/19]">

                        <!-- Poni HP (Notch) -->
                        <div class="absolute top-0 inset-x-0 h-6 bg-gray-900 rounded-b-2xl w-36 mx-auto z-20"></div>

                        <!-- WA Header -->
                        <div class="bg-[#075e54] text-white flex items-center px-4 py-3 shadow-md z-10 pt-10">
                            <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex-shrink-0 bg-cover bg-center border border-emerald-700"
                                style="background-image: url('https://ui-avatars.com/api/?name=Pelanggan&background=E5DDD5&color=075E54')">
                            </div>
                            <div class="ml-3">
                                <div class="font-bold text-[15px] leading-tight">Nama Pelanggan</div>
                                <div class="text-[11px] text-emerald-100 font-medium">online</div>
                            </div>
                        </div>

                        <!-- WA Background -->
                        <div class="flex-1 bg-[#efeae2] p-4 overflow-y-auto relative"
                            style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-size: cover; opacity: 0.95;">

                            <div class="flex justify-end mb-4 relative z-10">
                                <div
                                    class="bg-[#e7ffdb] rounded-2xl rounded-tr-none p-1.5 max-w-[85%] shadow-sm relative border border-gray-200/50">

                                    <!-- Jika Input Kosong -->
                                    <template x-if="pesanInput === '' && !imageUrl">
                                        <div
                                            class="p-3 text-gray-400 italic text-sm text-center flex flex-col items-center gap-2">
                                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                </path>
                                            </svg>
                                            Preview pesan muncul di sini
                                        </div>
                                    </template>

                                    <!-- Konten Preview -->
                                    <div x-show="pesanInput !== '' || imageUrl" class="p-1">
                                        <template x-if="imageUrl">
                                            <div
                                                class="mb-1.5 relative rounded-xl overflow-hidden bg-black/5 shadow-sm border border-black/5">
                                                <img :src="imageUrl"
                                                    class="w-full max-h-52 object-cover rounded-xl">
                                            </div>
                                        </template>
                                        <p class="text-[15px] text-[#111b21] leading-[1.3] break-words px-1 font-sans"
                                            x-html="formattedPreview"></p>

                                        <!-- Waktu & Centang -->
                                        <div
                                            class="text-[10px] text-gray-500 flex justify-end items-center gap-1 mt-1 mr-1">
                                            <span x-text="currentTime"></span>
                                            <svg class="w-[15px] h-[15px] text-[#53bdeb]" viewBox="0 0 16 15"
                                                fill="none">
                                                <path
                                                    d="M15.01 3.316l-7.466 7.466-2.22-2.22-1.06 1.06 3.28 3.28 8.526-8.526-1.06-1.06zM9.763 3.316L8.703 2.256l-5.466 5.467-2.22-2.22L-.043 6.563l3.28 3.28L9.763 3.316z"
                                                    fill="currentColor" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Segitiga Bubble Tail -->
                                    <div class="absolute top-0 -right-2.5 w-3 h-4 bg-[#e7ffdb]"
                                        style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Mockup -->
                        <div class="bg-[#f0f2f5] p-2.5 flex items-center gap-2 border-t border-gray-200">
                            <div
                                class="bg-white rounded-full flex-1 h-10 flex items-center px-4 shadow-sm border border-gray-200">
                                <span class="text-gray-400 text-sm">Ketik pesan...</span>
                            </div>
                            <div
                                class="w-8 h-8 bg-[#128C7E] rounded-full flex items-center justify-center text-white shadow-sm">
                                <svg class="w-4 h-4 pr-0.5 pb-0.5" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN BAWAH: TEMPLATE PESAN -->
            <div class="mt-16 bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">📋 Template Pesan Siap Pakai</h3>
                        <p class="text-sm text-gray-500 mt-1">Simpan pesan promosi yang sering Anda gunakan.</p>
                    </div>
                    <button @click="openTemplateModal(null)"
                        class="bg-[#128C7E] hover:bg-[#075e54] text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>Tambah Template
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($templates as $template)
                        <div
                            class="border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 bg-white flex flex-col justify-between group">
                            <div>
                                <h4
                                    class="font-bold text-lg text-[#128C7E] mb-2 group-hover:text-[#075E54] transition-colors">
                                    {{ $template->judul }}</h4>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-5 leading-relaxed">
                                    {{ $template->pesan }}</p>
                            </div>
                            <div class="flex justify-between gap-2 border-t border-gray-100 pt-4">
                                <button @click="pakaiTemplate(`{{ addslashes($template->pesan) }}`)"
                                    class="flex-1 text-sm bg-emerald-50 text-[#128C7E] px-3 py-2 rounded-lg hover:bg-[#128C7E] hover:text-white font-bold transition-colors">Gunakan</button>
                                <button
                                    @click="openTemplateModal({{ $template->id }}, `{{ addslashes($template->judul) }}`, `{{ addslashes($template->pesan) }}`)"
                                    class="text-sm bg-amber-50 text-amber-600 px-4 py-2 rounded-lg hover:bg-amber-500 hover:text-white font-bold transition-colors">Edit</button>
                                <form action="{{ route('wa.template.delete', $template->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus template ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-sm bg-red-50 text-red-600 px-4 py-2 rounded-lg hover:bg-red-500 hover:text-white font-bold transition-colors">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    @if ($templates->count() == 0)
                        <div
                            class="col-span-full text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                            <p class="text-gray-400 font-medium">Belum ada template yang tersimpan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- BAGIAN BAWAH: BUKU ALAMAT (GRUP KONTAK) -->
            <div id="section-kontak" class="mt-8 bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-8 mb-10">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">📖 Buku Alamat (Grup Kontak)</h3>
                        <p class="text-sm text-gray-500 mt-1">Kelola database nomor target Anda dengan mudah.</p>
                    </div>
                    <button @click="openContactModal(null)"
                        class="bg-[#128C7E] hover:bg-[#075e54] text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>Tambah Grup
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($contactGroups as $grup)
                        <div
                            class="border border-blue-100 rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 bg-gradient-to-b from-blue-50/40 to-white flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-bold text-lg text-blue-900">{{ $grup->nama_grup }}</h4>
                                    <span
                                        class="bg-blue-100 text-blue-800 text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                                        {{ count(explode("\n", trim($grup->nomor))) }} Kontak
                                    </span>
                                </div>
                                <p
                                    class="text-[13px] text-gray-500 font-mono line-clamp-3 mb-5 whitespace-pre-line bg-white/50 p-2 rounded-lg border border-blue-50">
                                    {{ $grup->nomor }}
                                </p>
                            </div>
                            <div class="flex justify-between gap-2 border-t border-gray-100 pt-4">
                                <button @click="pakaiKontak(`{{ addslashes($grup->nomor) }}`)"
                                    class="flex-1 text-sm bg-emerald-50 text-[#128C7E] px-3 py-2 rounded-lg hover:bg-[#128C7E] hover:text-white font-bold transition-colors">Gunakan</button>
                                <button
                                    @click="openContactModal({{ $grup->id }}, `{{ addslashes($grup->nama_grup) }}`, `{{ addslashes($grup->nomor) }}`)"
                                    class="text-sm bg-amber-50 text-amber-600 px-4 py-2 rounded-lg hover:bg-amber-500 hover:text-white font-bold transition-colors">Edit</button>
                                <form action="{{ route('wa.contact.delete', $grup->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus grup kontak ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-sm bg-red-50 text-red-600 px-4 py-2 rounded-lg hover:bg-red-500 hover:text-white font-bold transition-colors">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    @if ($contactGroups->count() == 0)
                        <div
                            class="col-span-full text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                            <p class="text-gray-400 font-medium">Belum ada grup kontak yang tersimpan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- MODAL TAMBAH/EDIT GRUP KONTAK -->
            <div x-show="showContactModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
                style="display: none;">
                <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all">

                    <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                        <span x-show="!contactId" class="bg-blue-100 text-blue-600 p-2 rounded-xl"><svg
                                class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4"></path>
                            </svg></span>
                        <span x-show="contactId" class="bg-amber-100 text-amber-600 p-2 rounded-xl"><svg
                                class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg></span>
                        <span x-text="contactId ? 'Edit Grup Kontak' : 'Tambah Grup Baru'"></span>
                    </h3>

                    <form :action="contactId ? `/wa/contact-group/${contactId}` : '{{ route('wa.contact.store') }}'"
                        method="POST" @submit="prepareContactSubmit()">
                        @csrf
                        <template x-if="contactId"><input type="hidden" name="_method" value="PUT"></template>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Grup</label>
                            <input type="text" name="nama_grup" x-model="contactNamaGrup"
                                placeholder="misal: Pelanggan VIP Bulan Agustus"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 rounded-xl px-4 py-3 bg-gray-50 focus:bg-white transition-colors shadow-sm"
                                required>
                        </div>

                        <!-- KOTAK SMART INPUT (CHIPS) -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2 flex justify-between items-center">
                                <span>Daftar Kontak Target</span>
                                <span
                                    class="text-xs font-bold px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg shadow-sm"><span
                                        x-text="contactModalList.length"></span> Kontak</span>
                            </label>

                            <!-- Input tersembunyi yang akan dikirim ke Laravel Database -->
                            <input type="hidden" name="nomor" :value="contactNomor">

                            <!-- Wadah Editor Chips -->
                            <div
                                class="w-full border border-gray-300 focus-within:border-blue-500 focus-within:ring focus-within:ring-blue-200 rounded-xl bg-gray-50 focus-within:bg-white transition-colors overflow-hidden flex flex-col h-[280px] shadow-inner">

                                <!-- Area Daftar Kontak (Bisa di-scroll) -->
                                <div
                                    class="flex-1 overflow-y-auto p-4 flex flex-wrap gap-2 items-start content-start border-b border-gray-100 relative">

                                    <!-- Jika Kosong -->
                                    <div x-show="contactModalList.length === 0"
                                        class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 gap-3">
                                        <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">Belum ada nomor. Paste dari Excel di bawah.</span>
                                    </div>

                                    <!-- Looping Nomor menjadi Label/Badge Berwarna -->
                                    <template x-for="(contact, index) in contactModalList" :key="index">
                                        <span
                                            class="inline-flex items-center gap-2 bg-white border border-blue-200 text-blue-800 px-3 py-1.5 rounded-lg text-sm font-medium shadow-sm group hover:border-blue-400 hover:shadow transition-all">
                                            <span x-text="contact"></span>
                                            <!-- Tombol Hapus (X) -->
                                            <button type="button" @click="removeContactModalNumber(index)"
                                                class="text-blue-300 hover:text-white hover:bg-red-500 rounded-md p-0.5 transition-colors focus:outline-none"
                                                title="Hapus kontak ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <!-- Area Input Teks (Paste & Ketik) -->
                                <div class="p-2.5 bg-slate-100 border-t border-slate-200 relative z-20">
                                    <textarea x-model="contactNewNomor" @paste="handleContactModalPaste($event)"
                                        @keydown.enter="handleContactModalEnter($event)" rows="2"
                                        class="w-full border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm resize-none bg-white rounded-xl p-3 shadow-sm placeholder-slate-400 transition-all"
                                        placeholder="Ketik nomor lalu tekan Enter, atau PASTE data Excel (Nama & Nomor) ke sini..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <button type="button" @click="showContactModal = false"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold transition-colors">Batal</button>
                            <button type="submit"
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-transform transform hover:-translate-y-0.5 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Grup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL TAMBAH/EDIT TEMPLATE -->
            <div x-show="showTemplateModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
                style="display: none;">
                <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-lg">
                    <h3 class="text-2xl font-bold mb-6 text-gray-800"
                        x-text="templateId ? '✏️ Edit Template' : '➕ Tambah Template Baru'"></h3>
                    <form :action="templateId ? `/wa/template/${templateId}` : '{{ route('wa.template.store') }}'"
                        method="POST">
                        @csrf
                        <template x-if="templateId"><input type="hidden" name="_method" value="PUT"></template>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Judul Template</label>
                            <input type="text" name="judul" x-model="templateJudul"
                                placeholder="misal: Promo Akhir Tahun"
                                class="w-full border-gray-300 focus:border-[#128C7E] focus:ring focus:ring-[#128C7E] rounded-xl"
                                required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Isi Pesan Utama</label>
                            <textarea name="pesan" x-model="templatePesan" rows="6"
                                class="w-full border-gray-300 focus:border-[#128C7E] focus:ring focus:ring-[#128C7E] rounded-xl resize-y"
                                placeholder="Gunakan {nama} jika perlu..." required></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showTemplateModal = false"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold transition-colors">Batal</button>
                            <button type="submit"
                                class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition-colors">Simpan
                                Template</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- BAGIAN BAWAH: RIWAYAT BLAST (LOGS) -->
            <div class="mt-8 bg-white shadow-sm sm:rounded-[24px] border border-slate-200 p-8 mb-10 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">🕒 Riwayat Kampanye Blast (Logs)</h3>
                        <p class="text-xs text-slate-500 mt-1">10 Aktivitas blast server terakhir beserta status
                            pengiriman tiap nomor.</p>
                    </div>
                    <a href="{{ route('wa.logs') }}"
                        class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3.5 py-2 rounded-xl border border-indigo-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Buka Semua Log Aktivitas
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 border-y border-slate-200 text-xs uppercase text-slate-500 font-semibold">
                                <th class="py-3 px-4">Tanggal & Kampanye</th>
                                <th class="py-3 px-4 w-1/4">Isi Pesan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Target</th>
                                <th class="py-3 px-4 text-center">Berhasil</th>
                                <th class="py-3 px-4 text-center">Gagal</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaigns as $camp)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 text-xs">{{ $camp->judul }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $camp->created_at->format('d M Y, H:i') }} WIB
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs">
                                        <div
                                            class="line-clamp-2 bg-slate-50 p-2 rounded border border-slate-100 text-slate-700">
                                            {{ $camp->pesan }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if ($camp->status === 'completed')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                Selesai
                                            </span>
                                        @elseif ($camp->status === 'processing')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 animate-pulse">
                                                Memproses...
                                            </span>
                                        @elseif ($camp->status === 'pending')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                Antrean
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700">
                                                {{ ucfirst($camp->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-700 text-xs">
                                        {{ $camp->total_target }}</td>
                                    <td
                                        class="py-3 px-4 text-center font-bold text-[#128C7E] bg-emerald-50/30 text-xs">
                                        {{ $camp->success_count }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-rose-500 text-xs">
                                        {{ $camp->failed_count }}</td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <a href="{{ route('wa.blast.campaign.show', $camp->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#128C7E] bg-emerald-50 hover:bg-[#128C7E] hover:text-white transition border border-emerald-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Rincian Nomor
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                @foreach ($logs as $log)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-slate-700">
                                                {{ $log->created_at->format('d M Y') }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $log->created_at->format('H:i:s') }} WIB</div>
                                        </td>
                                        <td class="py-3 px-4 text-xs">
                                            <div class="line-clamp-2 bg-slate-50 p-2 rounded border border-slate-100">
                                                {{ $log->pesan }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center"><span
                                                class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700">Selesai</span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-700">
                                            {{ $log->total_target }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-[#128C7E] bg-emerald-50/30">
                                            {{ $log->success_count }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-rose-500">
                                            {{ $log->failed_count }}</td>
                                        <td class="py-3 px-4 text-center text-xs text-gray-400">-</td>
                                    </tr>
                                @endforeach
                                @if ($logs->isEmpty())
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs italic">
                                            Belum ada riwayat pengiriman blast.
                                        </td>
                                    </tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT ALPINE -->
    <script>
        function normalizeWaPhone(phoneStr) {
            if (!phoneStr) return '';
            let s = String(phoneStr).trim().replace(/^["']+|["']+$/g, '');

            // Tangani notasi ilmiah Excel (misal: 8.95351E+11)
            if (/^[+\-]?\d+[\.,]\d+[eE][+\-]?\d+$/i.test(s)) {
                let n = parseFloat(s.replace(',', '.'));
                if (!isNaN(n)) {
                    s = n.toLocaleString('fullwide', {
                        useGrouping: false
                    });
                }
            }

            let clean = s.replace(/\D/g, '');
            if (!clean) return '';

            if (clean.startsWith('620')) {
                clean = '62' + clean.substring(3);
            }

            // Perbaikan otomatis nomor Tri/Three (089x) jika hilang angka 8: 095/6295/95 -> 62895
            let mTri = clean.match(/^09([5-9]\d{7,10})$/) || clean.match(/^629([5-9]\d{7,10})$/) || clean.match(
                /^9([5-9]\d{7,10})$/);
            if (mTri) {
                return '6289' + mTri[1];
            }

            if (clean.startsWith('0')) {
                clean = '62' + clean.substring(1);
            } else if (clean.startsWith('8')) {
                clean = '62' + clean;
            }

            return clean;
        }

        function parseWaContacts(rawText) {
            if (!rawText) return [];
            let lines = String(rawText).split(/\r?\n/);
            let parsedList = [];

            lines.forEach(line => {
                let trimmed = line.trim().replace(/^["']+|["']+$/g, '');
                if (!trimmed) return;

                // Abaikan baris header Excel jika tidak memuat digit nomor telepon
                if (/^(no|nomor|name|nama|kontak|contact|phone|hp|telepon)[\s\t,;:\-\|]/i.test(trimmed) && !/\d{8,}/
                    .test(trimmed)) {
                    return;
                }

                let name = '';
                let rawPhone = '';

                // Cek pemisah tabular (Excel copy-paste), titik koma, pipe, atau koma
                let delimiter = null;
                if (trimmed.includes('\t')) delimiter = '\t';
                else if (trimmed.includes(';')) delimiter = ';';
                else if (trimmed.includes('|')) delimiter = '|';
                else if ((trimmed.match(/,/g) || []).length === 1 && /\d{8,}/.test(trimmed)) delimiter = ',';

                if (delimiter !== null) {
                    let cells = trimmed.split(delimiter).map(c => c.trim().replace(/^["']+|["']+$/g, '')).filter(
                        c => c !== '');
                    let phoneIdx = -1;
                    cells.forEach((cell, idx) => {
                        let digits = cell.replace(/\D/g, '');
                        if (digits.length >= 8 && digits.length <= 16 && phoneIdx === -1) {
                            rawPhone = cell;
                            phoneIdx = idx;
                        }
                    });

                    if (phoneIdx !== -1) {
                        for (let idx = 0; idx < cells.length; idx++) {
                            if (idx === phoneIdx) continue;
                            let c = cells[idx];
                            if (/^\d{1,4}$/.test(c)) continue; // Lewati nomor urut baris
                            if (/[a-zA-Z]/.test(c)) {
                                name = c;
                                break;
                            }
                        }
                        if (!name) {
                            for (let idx = 0; idx < cells.length; idx++) {
                                if (idx === phoneIdx) continue;
                                let c = cells[idx];
                                if (!/^\d{1,4}$/.test(c)) {
                                    name = c;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!rawPhone) {
                    let mParen = trimmed.match(/^(.*?)\(([\+?\d\s\-\.]{8,20})\)(.*?)$/);
                    let mHyphen1 = trimmed.match(/^([^\:\-]+)[\:\-]\s*([\+?\d\s\-\.]{8,20})$/);
                    let mHyphen2 = trimmed.match(/^([\+?\d\s\-\.]{8,20})\s*[\:\-]\s*([^\:\-]+)$/);
                    let mSpace = trimmed.match(/^(.*?)((?:\+?62|0|8|9)\d[\d\s\-\.]{6,16}\d)(.*?)$/);

                    if (mParen) {
                        rawPhone = mParen[2].trim();
                        name = (mParen[1] + ' ' + mParen[3]).trim();
                    } else if (mHyphen1) {
                        rawPhone = mHyphen1[2].trim();
                        name = mHyphen1[1].trim();
                    } else if (mHyphen2) {
                        rawPhone = mHyphen2[1].trim();
                        name = mHyphen2[2].trim();
                    } else if (mSpace) {
                        rawPhone = mSpace[2].trim();
                        name = (mSpace[1] + ' ' + mSpace[3]).trim();
                    }
                }

                if (name) {
                    name = name.replace(/^[\s\-\:\,\;\|\(\)\[\]\/]+|[\s\-\:\,\;\|\(\)\[\]\/]+$/g, '').replace(
                        /\s{2,}/g, ' ').trim();
                }

                // Dukungan JID Grup WhatsApp (misal: Nama Grup - 120363043232123456@g.us atau 120363043232123456@g.us)
                if (trimmed.includes('@g.us')) {
                    parsedList.push(trimmed);
                    return;
                }

                let cleanNum = normalizeWaPhone(rawPhone || trimmed);
                if (cleanNum && cleanNum.length >= 9) {
                    let formatted = name ? `${name} - ${cleanNum}` : cleanNum;
                    parsedList.push(formatted);
                }
            });

            return parsedList;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('blastForm', () => ({
                pesanInput: '',
                targetInput: '',
                newTargetContact: '',
                imageUrl: null,
                imageBase64: null,
                antiBot: true, // Default tercentang

                // Fitur Tarik Grup WhatsApp Instance (Dropdown)
                userInstance: '{{ $userInstance }}',
                isInstanceConnected: {{ $isInstanceConnected ? 'true' : 'false' }},
                instanceState: '{{ $instanceState }}',
                openGroupDropdown: false,
                accountGroups: [],
                selectedGroupIds: [],
                groupSearch: '',
                isLoadingGroups: false,
                isExtractingParticipants: false,
                groupFetchMessage: '',

                showEmoji: false,
                emojis: ['😀', '😂', '😇', '🥰', '😎', '🙏', '👍', '🔥', '🎉', '✨', '✅', '❌', '⚠️',
                    '📢', '💼', '📦', '📝', '⭐', '🚀', '❤️'
                ],

                isSending: false,
                hideSendingModal: false,
                showResult: false,
                progress: 0,
                sentCount: 0,
                totalTarget: 0,
                berhasilCount: 0,
                gagalCount: 0,
                currentCampaignId: null,
                pollTimer: null,

                // Modal Template
                showTemplateModal: false,
                templateId: null,
                templateJudul: '',
                templatePesan: '',

                // Modal Grup Kontak (Buku Alamat)
                showContactModal: false,
                contactId: null,
                contactNamaGrup: '',
                contactNomor: '',
                contactNewNomor: '',

                init() {
                    this.checkActiveCampaign();
                    this.checkUrlParams();
                },

                checkUrlParams() {
                    const params = new URLSearchParams(window.location.search);
                    const campaignId = params.get('load_failed_campaign');
                    if (campaignId) {
                        this.loadFailedToForm(campaignId);
                    }
                },

                async loadFailedToForm(campaignId) {
                    try {
                        let res = await fetch(`/wa/blast/campaign/${campaignId}/failed-recipients`);
                        let data = await res.json();
                        if (data.success && data.failed_recipients) {
                            let contacts = data.failed_recipients.map(r => r.nama ?
                                `${r.nama} - ${r.nomor}` : r.nomor);
                            this.targetInput = contacts.join('\n');
                            if (data.campaign && data.campaign.pesan) {
                                this.pesanInput = data.campaign.pesan;
                            }
                            this.showResult = false;
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        } else {
                            alert(data.message || 'Tidak dapat memuat nomor gagal.');
                        }
                    } catch (e) {
                        console.error('Error loading failed recipients:', e);
                        alert('Gagal mengambil data nomor dari server.');
                    }
                },

                async checkActiveCampaign() {
                    try {
                        let res = await fetch('{{ route('wa.blast.active') }}');
                        let data = await res.json();
                        if (data.active && data.campaign) {
                            this.currentCampaignId = data.campaign.id;
                            this.totalTarget = data.campaign.total_target;
                            this.sentCount = data.campaign.processed;
                            this.progress = data.campaign.progress;
                            this.berhasilCount = data.campaign.success_count;
                            this.gagalCount = data.campaign.failed_count;
                            this.isSending = true;
                            this.hideSendingModal = true;
                            this.startPolling(data.campaign.id);
                        }
                    } catch (e) {
                        console.error('Error checking active campaign', e);
                    }
                },

                // Tarik Grup WhatsApp dari Instance
                async fetchAccountGroups(forceRefresh = false) {
                    this.isLoadingGroups = true;
                    this.groupFetchMessage = '';
                    try {
                        let url = '{{ route('wa.account.groups') }}' + (forceRefresh ?
                            '?refresh=1' : '');
                        let res = await fetch(url);
                        let data = await res.json();
                        if (data.instance) {
                            this.userInstance = data.instance;
                        }
                        if (typeof data.connected !== 'undefined') {
                            this.isInstanceConnected = data.connected;
                        }
                        if (data.state) {
                            this.instanceState = data.state;
                        }
                        if (data.success && Array.isArray(data.groups)) {
                            this.accountGroups = data.groups;
                            if (this.accountGroups.length === 0) {
                                this.groupFetchMessage = data.message ||
                                    `Tidak ada grup WhatsApp yang terdeteksi pada instance "${this.userInstance}".`;
                            } else {
                                this.selectedGroupIds = [];
                                this.groupFetchMessage =
                                    `Ditemukan ${this.accountGroups.length} grup WhatsApp aktif dari instance "${this.userInstance}".`;
                            }
                        } else {
                            this.groupFetchMessage = data.message ||
                                'Gagal menarik daftar grup dari WhatsApp Gateway.';
                        }
                    } catch (e) {
                        console.error('Error fetching account WA groups:', e);
                        this.groupFetchMessage =
                            'Terjadi kesalahan koneksi saat menarik grup WhatsApp.';
                    } finally {
                        this.isLoadingGroups = false;
                    }
                },

                get filteredAccountGroups() {
                    if (!this.groupSearch.trim()) {
                        return this.accountGroups;
                    }
                    let q = this.groupSearch.toLowerCase();
                    return this.accountGroups.filter(g => (g.subject || '').toLowerCase().includes(
                        q) || (g.id || '').includes(q));
                },

                selectAllAccountGroups(select = true) {
                    if (select) {
                        let ids = this.filteredAccountGroups.map(g => g.id);
                        let set = new Set(this.selectedGroupIds);
                        ids.forEach(id => set.add(id));
                        this.selectedGroupIds = Array.from(set);
                    } else {
                        if (this.groupSearch.trim()) {
                            let filteredIds = this.filteredAccountGroups.map(g => g.id);
                            this.selectedGroupIds = this.selectedGroupIds.filter(id => !filteredIds
                                .includes(id));
                        } else {
                            this.selectedGroupIds = [];
                        }
                    }
                },

                addGroupToTarget(group) {
                    let item = `${group.subject} - ${group.id}`;
                    let currentList = [...this.targetList];
                    if (!currentList.includes(item)) {
                        currentList.push(item);
                        this.targetInput = currentList.join('\n');
                        this.groupFetchMessage =
                            `Grup "${group.subject}" berhasil ditambahkan ke daftar target!`;
                    } else {
                        this.groupFetchMessage = `Grup "${group.subject}" sudah ada di daftar target.`;
                    }
                },

                addSelectedGroupsToTargets() {
                    if (this.selectedGroupIds.length === 0) return;

                    let selectedGroups = this.accountGroups.filter(g => this.selectedGroupIds.includes(g
                        .id));
                    let currentList = [...this.targetList];
                    let addedCount = 0;

                    selectedGroups.forEach(g => {
                        let item = `${g.subject} - ${g.id}`;
                        if (!currentList.includes(item)) {
                            currentList.push(item);
                            addedCount++;
                        }
                    });

                    this.targetInput = currentList.join('\n');
                    this.groupFetchMessage =
                        `Berhasil menambahkan ${addedCount} grup WhatsApp ke daftar target!`;
                },

                toggleGroupDropdown() {
                    this.openGroupDropdown = !this.openGroupDropdown;
                    if (this.openGroupDropdown && this.accountGroups.length === 0 && !this
                        .isLoadingGroups && this.isInstanceConnected) {
                        this.fetchAccountGroups();
                    }
                },

                async extractGroupParticipants(targetGroupIds = null) {
                    let ids = [];
                    if (targetGroupIds) {
                        ids = Array.isArray(targetGroupIds) ? targetGroupIds : [targetGroupIds];
                    } else {
                        ids = this.selectedGroupIds;
                    }

                    if (!ids || ids.length === 0) {
                        alert('Silakan pilih minimal satu grup WhatsApp terlebih dahulu.');
                        return;
                    }

                    this.isExtractingParticipants = true;
                    this.groupFetchMessage =
                        'Sedang mengekstrak nomor kontak dari grup terpilih...';

                    try {
                        let res = await fetch('{{ route('wa.group.participants') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                group_jids: ids
                            })
                        });
                        let data = await res.json();

                        if (data.success && Array.isArray(data.participants)) {
                            let currentList = [...this.targetList];
                            let addedCount = 0;

                            data.participants.forEach(phone => {
                                if (!currentList.includes(phone)) {
                                    currentList.push(phone);
                                    addedCount++;
                                }
                            });

                            this.targetInput = currentList.join('\n');
                            this.groupFetchMessage =
                                `Berhasil menarik ${data.participants.length} nomor kontak anggota (${addedCount} nomor baru dimasukkan ke daftar target).`;
                        } else {
                            this.groupFetchMessage = data.message ||
                                'Gagal mengekstrak nomor kontak dari grup WhatsApp.';
                            alert(this.groupFetchMessage);
                        }
                    } catch (e) {
                        console.error('Error extracting group participants:', e);
                        this.groupFetchMessage =
                            'Terjadi kesalahan koneksi saat menarik nomor kontak dari grup.';
                        alert(this.groupFetchMessage);
                    } finally {
                        this.isExtractingParticipants = false;
                    }
                },

                // Target Contacts
                get targetList() {
                    return this.targetInput ? this.targetInput.split('\n').filter(i => i.trim() !==
                        '') : [];
                },
                get targetCount() {
                    return this.targetList.length;
                },

                normalizePhone(phoneStr) {
                    return normalizeWaPhone(phoneStr);
                },

                parseExcelOrRawText(rawText) {
                    return parseWaContacts(rawText);
                },

                addTargetContacts(rawText) {
                    let newContacts = this.parseExcelOrRawText(rawText);
                    let currentList = [...this.targetList];

                    newContacts.forEach(contact => {
                        if (!currentList.includes(contact)) {
                            currentList.push(contact);
                        }
                    });

                    this.targetInput = currentList.join('\n');
                    this.newTargetContact = '';
                },

                formatAndCleanTargets() {
                    let allRaw = this.targetInput + '\n' + this.newTargetContact;
                    let parsed = this.parseExcelOrRawText(allRaw);
                    let unique = [];
                    parsed.forEach(c => {
                        if (!unique.includes(c)) unique.push(c);
                    });
                    this.targetInput = unique.join('\n');
                    this.newTargetContact = '';
                },

                removeTargetContact(index) {
                    let currentList = this.targetList;
                    currentList.splice(index, 1);
                    this.targetInput = currentList.join('\n');
                },

                clearAllTargets() {
                    this.targetInput = '';
                    this.newTargetContact = '';
                    if (this.$refs.inputTargetTextarea) {
                        this.$refs.inputTargetTextarea.focus();
                    }
                },

                handleMainPaste(e) {
                    e.preventDefault();
                    this.addTargetContacts((e.clipboardData || window.clipboardData).getData('text'));
                },

                handleMainEnter(e) {
                    if (this.newTargetContact.trim()) {
                        e.preventDefault();
                        this.addTargetContacts(this.newTargetContact);
                    }
                },

                get currentTime() {
                    let d = new Date();
                    return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes()
                        .toString().padStart(2, '0');
                },

                // Template Actions
                pakaiTemplate(pesan) {
                    this.pesanInput = pesan;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },

                openTemplateModal(id, judul = '', pesan = '') {
                    this.templateId = id;
                    this.templateJudul = judul;
                    this.templatePesan = pesan;
                    this.showTemplateModal = true;
                },

                // Contact Group (Buku Alamat) Actions
                pakaiKontak(nomor) {
                    let cleaned = parseWaContacts(nomor);
                    this.targetInput = cleaned.length > 0 ? cleaned.join('\n') : nomor;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },

                openContactModal(id, nama = '', nomor = '') {
                    this.contactId = id;
                    this.contactNamaGrup = nama;
                    this.contactNomor = nomor;
                    this.contactNewNomor = '';
                    this.showContactModal = true;
                },

                get contactModalList() {
                    return this.contactNomor ? this.contactNomor.split('\n').filter(i => i
                        .trim() !== '') : [];
                },

                addContactModalNumbers(rawText) {
                    let newContacts = parseWaContacts(rawText);
                    let currentList = [...this.contactModalList];
                    newContacts.forEach(contact => {
                        if (!currentList.includes(contact)) {
                            currentList.push(contact);
                        }
                    });
                    this.contactNomor = currentList.join('\n');
                    this.contactNewNomor = '';
                },

                removeContactModalNumber(index) {
                    let currentList = [...this.contactModalList];
                    currentList.splice(index, 1);
                    this.contactNomor = currentList.join('\n');
                },

                handleContactModalPaste(e) {
                    let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    e.preventDefault();
                    this.addContactModalNumbers(pastedText);
                },

                handleContactModalEnter(e) {
                    if (this.contactNewNomor.trim() !== '') {
                        e.preventDefault();
                        this.addContactModalNumbers(this.contactNewNomor);
                    }
                },

                prepareContactSubmit() {
                    if (this.contactNewNomor && this.contactNewNomor.trim() !== '') {
                        this.addContactModalNumbers(this.contactNewNomor);
                    }
                },

                // Media & Formatting
                previewImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.imageUrl = URL.createObjectURL(file);
                        let r = new FileReader();
                        r.onload = e => this.imageBase64 = e.target.result;
                        r.readAsDataURL(file);
                    } else {
                        this.imageUrl = this.imageBase64 = null;
                    }
                },

                clearMedia() {
                    this.imageUrl = null;
                    this.imageBase64 = null;
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = '';
                    }
                },

                clearMessageAndMedia() {
                    this.pesanInput = '';
                    this.clearMedia();
                    if (this.$refs.pesanTextarea) {
                        this.$refs.pesanTextarea.focus();
                    }
                },

                insertFormat(char) {
                    const el = this.$refs.pesanTextarea;
                    const start = el.selectionStart,
                        end = el.selectionEnd;
                    this.pesanInput = this.pesanInput.substring(0, start) + char + this.pesanInput
                        .substring(start, end) + char + this.pesanInput.substring(end);
                    setTimeout(() => {
                        el.focus();
                        el.setSelectionRange(end + 2, end + 2);
                    }, 10);
                },

                insertEmoji(e) {
                    const el = this.$refs.pesanTextarea;
                    const start = el.selectionStart;
                    this.pesanInput = this.pesanInput.substring(0, start) + e + this.pesanInput
                        .substring(start);
                    this.showEmoji = false;
                    setTimeout(() => {
                        el.focus();
                        el.setSelectionRange(start + e.length, start + e.length);
                    }, 10);
                },

                parseSpintax(text) {
                    return text.replace(/\[(.*?)\]/g, function(match, contents) {
                        var choices = contents.split('/');
                        return choices[Math.floor(Math.random() * choices.length)];
                    });
                },

                get formattedPreview() {
                    if (!this.pesanInput) return '';
                    let firstName = 'Bapak/Ibu';
                    if (this.targetList && this.targetList.length > 0) {
                        let firstTarget = this.targetList[0];
                        if (firstTarget.includes(' - ')) {
                            firstName = firstTarget.split(' - ')[0].trim() || 'Bapak/Ibu';
                        }
                    }
                    let f = this.pesanInput.replace(/\{(?:nama|name)\}/gi, firstName);
                    f = this.parseSpintax(f);
                    f = f.replace(/\*(.*?)\*/g, '<strong>$1</strong>')
                        .replace(/_(.*?)_/g, '<em>$1</em>')
                        .replace(/~(.*?)~/g, '<del>$1</del>');
                    return f.replace(/\n/g, '<br>');
                },

                async executeBlast() {
                    if (this.newTargetContact && this.newTargetContact.trim()) {
                        this.addTargetContacts(this.newTargetContact);
                    }

                    if (this.targetInput && this.targetInput.trim()) {
                        let cleaned = parseWaContacts(this.targetInput);
                        if (cleaned.length > 0) {
                            this.targetInput = cleaned.join('\n');
                        }
                    }

                    if (!this.targetInput.trim() || !this.pesanInput.trim()) {
                        return alert('Target & Pesan wajib diisi!');
                    }

                    this.isSending = true;
                    this.hideSendingModal = false;
                    this.sentCount = this.progress = 0;
                    this.totalTarget = this.targetCount;
                    this.berhasilCount = 0;
                    this.gagalCount = 0;

                    try {
                        let req = await fetch('{{ route('wa.blast.start') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                targets: this.targetInput,
                                pesan: this.pesanInput,
                                gambar_base64: this.imageBase64,
                                anti_bot: this.antiBot
                            })
                        });
                        let res = await req.json();

                        if (!res.success) {
                            this.isSending = false;
                            this.hideSendingModal = false;
                            alert(res.message || 'Gagal memulai pengiriman.');
                            return;
                        }

                        this.currentCampaignId = res.campaign_id;
                        this.totalTarget = res.total_target;
                        this.startPolling(res.campaign_id);
                    } catch (e) {
                        this.isSending = false;
                        this.hideSendingModal = false;
                        alert('Terjadi kesalahan saat mengirim permintaan ke server.');
                        console.error(e);
                    }
                },

                startPolling(campaignId) {
                    if (this.pollTimer) clearInterval(this.pollTimer);

                    this.pollTimer = setInterval(async () => {
                        try {
                            let res = await fetch(
                                `/wa/blast/campaign/${campaignId}/status`);
                            let statusData = await res.json();

                            this.progress = statusData.progress;
                            this.sentCount = statusData.processed;
                            this.totalTarget = statusData.total_target;
                            this.berhasilCount = statusData.success_count;
                            this.gagalCount = statusData.failed_count;

                            if (statusData.completed) {
                                clearInterval(this.pollTimer);
                                this.pollTimer = null;
                                setTimeout(() => {
                                    this.isSending = false;
                                    this.hideSendingModal = false;
                                    this.showResult = true;
                                }, 500);
                            }
                        } catch (e) {
                            console.error('Polling error', e);
                        }
                    }, 2000);
                },

                retryFailed() {
                    if (this.currentCampaignId) {
                        window.location.href = `/wa/blast/campaign/${this.currentCampaignId}`;
                    } else {
                        this.showResult = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
