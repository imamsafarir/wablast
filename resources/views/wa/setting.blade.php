<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-3">
            <span class="p-2 bg-emerald-50 text-[#128C7E] rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </span>
            Pengaturan
        </h2>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Alert Notifikasi -->
            @if (session('success'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
                    <div class="font-bold text-sm mb-1">Ada beberapa kesalahan input:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CARD 1: PENGATURAN BRANDING WEBSITE (KHUSUS SUPERADMIN) -->
            @if (Auth::user()->isSuperAdmin())
                <div class="bg-white shadow-sm sm:rounded-3xl border border-gray-100 overflow-hidden"
                    x-data="{
                        logoPreview: '{{ $logoUrl }}',
                        faviconPreview: '{{ $faviconUrl }}',
                        previewImage(event, type) {
                            const file = event.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    if (type === 'logo') this.logoPreview = e.target.result;
                                    if (type === 'favicon') this.faviconPreview = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    }">
                    <div class="bg-gray-50/70 px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Branding & Identitas Website</h3>
                                <p class="text-xs text-gray-500">Atur nama aplikasi, logo, dan favicon browser.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('wa.setting.branding') }}" method="POST" enctype="multipart/form-data"
                        class="p-8 space-y-6">
                        @csrf

                        <!-- Nama Website -->
                        <div>
                            <label for="site_name"
                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Nama Website / Aplikasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="site_name" name="site_name"
                                value="{{ old('site_name', $siteName) }}" required
                                placeholder="Contoh: WABlast - WhatsApp Marketing"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2.5 px-4">
                            <p class="text-xs text-gray-400 mt-1.5">Nama ini akan tampil di judul tab browser, header
                                navigasi, dan landing page.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <!-- Logo Website -->
                            <div class="p-5 bg-gray-50/60 rounded-2xl border border-gray-100">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                    Logo Website
                                </label>
                                <div class="flex items-center gap-4 mb-4">
                                    <div
                                        class="w-20 h-20 rounded-2xl bg-white border border-gray-200 flex items-center justify-center overflow-hidden p-2 shadow-sm">
                                        <template x-if="logoPreview">
                                            <img :src="logoPreview" alt="Logo Preview"
                                                class="max-h-full max-w-full object-contain">
                                        </template>
                                        <template x-if="!logoPreview">
                                            <svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="logo" id="logo" accept="image/*"
                                            @change="previewImage($event, 'logo')"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        <p class="text-[11px] text-gray-400 mt-1">Format: PNG, JPG, SVG, WebP. Maks 2MB.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Favicon Browser -->
                            <div class="p-5 bg-gray-50/60 rounded-2xl border border-gray-100">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                    Favicon Browser (Icon Tab)
                                </label>
                                <div class="flex items-center gap-4 mb-4">
                                    <div
                                        class="w-20 h-20 rounded-2xl bg-white border border-gray-200 flex items-center justify-center overflow-hidden p-3 shadow-sm">
                                        <template x-if="faviconPreview">
                                            <img :src="faviconPreview" alt="Favicon Preview"
                                                class="w-8 h-8 object-contain">
                                        </template>
                                        <template x-if="!faviconPreview">
                                            <svg class="w-8 h-8 text-emerald-500" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="favicon" id="favicon"
                                            accept=".ico,.png,.jpg,.jpeg,.svg" @change="previewImage($event, 'favicon')"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        <p class="text-[11px] text-gray-400 mt-1">Format: ICO, PNG, SVG. Ukuran ideal
                                            32x32
                                            px.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-sm transition flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Perubahan Branding
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- CARD 2: KONEKSI WHATSAPP GATEWAY (SCAN QR / PROFILE TERHUBUNG / LOGOUT) -->
            <div class="bg-white shadow-sm sm:rounded-3xl border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/70 px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-xl bg-emerald-50 text-[#128C7E] flex items-center justify-center font-bold text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Koneksi WhatsApp Gateway</h3>
                            <p class="text-xs text-gray-500">Status konektivitas nomor pengirim (Instance: <span
                                    class="font-mono font-bold text-gray-700">{{ Auth::user()->getWaInstanceName() }}</span>)
                            </p>
                        </div>
                    </div>
                    @if ($isConnected)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Terhubung
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ strtoupper($state) }}
                        </span>
                    @endif
                </div>

                <div class="p-8">
                    @if ($isConnected)
                        <!-- SUDAH TERHUBUNG -->
                        <div class="max-w-2xl mx-auto flex flex-col items-center justify-center text-center py-4">
                            <!-- Avatar / Foto Profil -->
                            <div class="relative mb-4">
                                @if (!empty($profile['profile_picture_url']))
                                    <img src="{{ $profile['profile_picture_url'] }}" alt="WhatsApp Profile"
                                        class="w-24 h-24 rounded-full border-4 border-emerald-500/20 object-cover shadow-md">
                                @else
                                    <div
                                        class="w-24 h-24 rounded-full bg-gradient-to-tr from-[#128C7E] to-[#25D366] text-white flex items-center justify-center text-3xl font-black shadow-md border-4 border-emerald-500/20">
                                        {{ !empty($profile['profile_name']) ? strtoupper(substr($profile['profile_name'], 0, 1)) : 'WA' }}
                                    </div>
                                @endif
                                <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-2 border-white rounded-full flex items-center justify-center text-white"
                                    title="Aktif & Terhubung">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>

                            <h3 class="text-2xl font-black text-gray-900 mb-1">
                                {{ $profile['profile_name'] ?? 'Nomor WhatsApp Terhubung' }}
                            </h3>

                            @if (!empty($profile['phone']))
                                <div
                                    class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200/70 font-mono font-bold text-sm px-4 py-1.5 rounded-full mb-4">
                                    <svg class="w-4 h-4 text-[#128C7E]" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21c1.12.45 2.33.69 3.48.69a1 1 0 011 1v3.5a1 1 0 01-1 1A19.93 19.93 0 012 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.15.24 2.36.69 3.48a1 1 0 01-.21 1.11l-2.36 2.2z" />
                                    </svg>
                                    <span>+{{ $profile['phone'] }}</span>
                                </div>
                            @endif

                            <!-- Detail Ringkasan Akun -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full my-4 text-left">
                                <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
                                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nama Akun
                                        / Pushname</div>
                                    <div class="text-sm font-bold text-gray-800 truncate mt-0.5">
                                        {{ $profile['profile_name'] ?? '-' }}</div>
                                </div>
                                <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
                                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nomor
                                        Pengirim</div>
                                    <div class="text-sm font-bold text-gray-800 font-mono mt-0.5">
                                        {{ !empty($profile['phone']) ? '+' . $profile['phone'] : '-' }}</div>
                                </div>
                                <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
                                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Instance
                                        Aktif</div>
                                    <div class="text-sm font-bold text-gray-800 font-mono mt-0.5 truncate">
                                        {{ Auth::user()->getWaInstanceName() }}</div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mb-6">
                                Sesi WhatsApp aktif dan siap digunakan untuk pengiriman blast massal maupun pesan
                                single.
                            </p>

                            <form action="{{ route('wa.disconnect') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin memutus koneksi WhatsApp ini?')"
                                    class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold py-2.5 px-6 rounded-xl border border-rose-200 transition text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Putuskan Sesi WhatsApp (Logout)
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- BELUM TERHUBUNG (MINTA SCAN QR) -->
                        <div class="flex flex-col items-center justify-center text-center py-4">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Hubungkan WhatsApp Pengirim</h3>
                            <p class="text-sm text-gray-500 mb-6 max-w-md">Buka aplikasi WhatsApp di ponsel Anda, pilih
                                <strong>Perangkat Tertaut</strong>, lalu scan QR Code di bawah ini.
                            </p>

                            @if ($qrCode)
                                <div
                                    class="bg-white p-4 rounded-2xl border-2 border-dashed border-gray-200 shadow-sm mb-6">
                                    <img src="{{ $qrCode }}" alt="QR Code WhatsApp"
                                        class="w-64 h-64 rounded-xl">
                                </div>
                            @else
                                <div
                                    class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl mb-6 w-full max-w-md text-xs">
                                    Gagal memuat QR Code. Pastikan server Evolution API aktif dan terhubung.
                                </div>
                            @endif

                            <div class="flex gap-3">
                                <button onclick="window.location.reload()"
                                    class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl flex items-center gap-2 transition text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    Segarkan Status
                                </button>
                            </div>

                            <p class="text-xs text-gray-400 mt-4">
                                Status Gateway Terkini:
                                <span class="font-bold text-gray-700 uppercase font-mono">{{ $state }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CARD 3: KONFIGURASI EVOLUTION API GATEWAY (KHUSUS SUPERADMIN) -->
            @if (Auth::user()->isSuperAdmin())
                <div class="bg-white shadow-sm sm:rounded-3xl border border-gray-100 overflow-hidden"
                    x-data="{
                        apiUrl: '{{ $evoUrl }}',
                        apiKey: '{{ $evoApiKey }}',
                        globalInstance: '{{ $evoInstance }}',
                        showApiKey: false,
                        isTesting: false,
                        testResult: null,
                        testConnection() {
                            if (!this.apiUrl || !this.apiKey) {
                                alert('Silakan lengkapi URL dan API Key sebelum melakukan tes koneksi.');
                                return;
                            }
                            this.isTesting = true;
                            this.testResult = null;
                    
                            fetch('{{ route('wa.setting.test-evolution') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        evolution_api_url: this.apiUrl,
                                        evolution_api_apikey: this.apiKey,
                                        wa_instance_name: this.globalInstance
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    this.testResult = data;
                                })
                                .catch(err => {
                                    this.testResult = {
                                        success: false,
                                        message: 'Koneksi gagal: Tidak dapat menghubungi server lokal atau koneksi terputus.'
                                    };
                                })
                                .finally(() => {
                                    this.isTesting = false;
                                });
                        }
                    }">

                    <!-- HEADER CARD -->
                    <div class="bg-gray-50/70 px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Server Evolution API Gateway</h3>
                                <p class="text-xs text-gray-500">Konfigurasi endpoint URL, Global API Key, dan nama
                                    instance default server WhatsApp.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Test Feedback Alert -->
                        <div x-show="testResult !== null" style="display: none;"
                            :class="testResult && testResult.success ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
                                'bg-rose-50 border-rose-200 text-rose-800'"
                            class="border px-5 py-4 rounded-2xl text-sm flex items-start gap-3 transition">
                            <template x-if="testResult && testResult.success">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </template>
                            <template x-if="testResult && !testResult.success">
                                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </template>
                            <div>
                                <div class="font-bold"
                                    x-text="testResult && testResult.success ? 'Koneksi Berhasil!' : 'Koneksi Gagal!'">
                                </div>
                                <div class="text-xs mt-0.5" x-text="testResult ? testResult.message : ''"></div>
                            </div>
                        </div>

                        <form action="{{ route('wa.setting.evolution') }}" method="POST" class="space-y-6">
                            @csrf

                            <!-- 1. Evolution API Base URL -->
                            <div>
                                <label for="evolution_api_url"
                                    class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Evolution API Base URL <span class="text-rose-500">*</span>
                                </label>
                                <input type="url" id="evolution_api_url" name="evolution_api_url"
                                    x-model="apiUrl" required
                                    placeholder="Contoh: https://evolutionapi.domainanda.com atau http://localhost:8080"
                                    class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2.5 px-4 font-mono">
                                <p class="text-xs text-gray-400 mt-1.5">URL server Evolution API Gateway (tanpa slash
                                    di
                                    akhir '/').</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- 2. Global API Key -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="evolution_api_apikey"
                                            class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Global API Key <span class="text-rose-500">*</span>
                                        </label>
                                        <button type="button" @click="showApiKey = !showApiKey"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                            <span x-text="showApiKey ? 'Sembunyikan' : 'Tampilkan'"></span>
                                        </button>
                                    </div>
                                    <input :type="showApiKey ? 'text' : 'password'" id="evolution_api_apikey"
                                        name="evolution_api_apikey" x-model="apiKey" required
                                        placeholder="Masukkan API Key Evolution API Anda"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2.5 px-4 font-mono">
                                </div>

                                <!-- 3. Instance Default Sistem (Global Fallback) -->
                                <div>
                                    <label for="wa_instance_name_global"
                                        class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                        Instance Default Sistem (Global Fallback) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" id="wa_instance_name_global" name="wa_instance_name"
                                        x-model="globalInstance" required placeholder="Contoh: wablast atau admin_wa"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2.5 px-4 font-mono">
                                    <p class="text-xs text-gray-400 mt-1.5">Nama instance default aplikasi jika user
                                        belum
                                        mengisi instance pribadi.</p>
                                </div>
                            </div>

                            <div
                                class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100">
                                <!-- Button Tes Koneksi -->
                                <button type="button" @click="testConnection()" :disabled="isTesting"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2.5 px-5 rounded-xl transition text-sm disabled:opacity-50">
                                    <svg x-show="!isTesting" class="w-4 h-4 text-slate-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    <svg x-show="isTesting" style="display: none;"
                                        class="w-4 h-4 animate-spin text-slate-600" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-text="isTesting ? 'Menguji Koneksi...' : 'Tes Koneksi Gateway'"></span>
                                </button>

                                <!-- Button Simpan Konfigurasi (Khusus Admin) -->
                                <button type="submit"
                                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Konfigurasi Gateway
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
