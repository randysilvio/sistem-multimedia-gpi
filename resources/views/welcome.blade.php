<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Multimedia - GPI Papua</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Animasi Melayang untuk Mockup Layar */
        .floating-mockup {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px) rotate(2deg); }
            50% { transform: translateY(-12px) rotate(0deg); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
            100% { transform: translateY(0px) rotate(2deg); }
        }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800">

    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center group">
                    <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" class="h-10 w-auto mr-3 drop-shadow-sm transition-transform duration-300 group-hover:scale-105">
                    <span class="font-bold text-xl tracking-tight text-gray-900 uppercase group-hover:text-blue-600 transition-colors duration-300">Sistem Multimedia</span>
                </a>

                <div>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 hover:-translate-y-0.5 shadow-md hover:shadow-lg transition-all duration-200">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-blue-600 transition duration-150 mr-4">Log in Admin</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-10"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="py-20 md:py-28 text-center lg:text-left lg:flex lg:items-center lg:justify-between">
                <div class="lg:w-1/2">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl mb-6">
                        <span class="block text-blue-400 mb-2 drop-shadow-md">Sistem Informasi</span>
                        <span class="block drop-shadow-md">Multimedia Ibadah</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-300 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 leading-relaxed">
                        Platform manajemen tata ibadah, lirik lagu, dan kontrol proyektor interaktif. Dirancang khusus untuk mempermudah pelayanan multimedia di lingkungan Gereja Protestan Indonesia (GPI) di Papua.
                    </p>
                    <div class="mt-8 sm:mt-10 flex justify-center lg:justify-start gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-8 py-3 font-semibold rounded-md text-blue-900 bg-white hover:bg-gray-50 md:py-4 md:text-lg shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                                Masuk ke Sistem
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-8 py-3 font-semibold rounded-md text-blue-900 bg-white hover:bg-gray-50 md:py-4 md:text-lg shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                                Login Pengelola
                            </a>
                        @endauth
                    </div>
                </div>
                
                <div class="hidden lg:block lg:w-5/12 mt-12 lg:mt-0">
                    <div class="bg-gray-800 p-2 rounded-xl shadow-2xl border border-gray-700 floating-mockup">
                        <div class="bg-black rounded-lg aspect-video flex flex-col items-center justify-center p-6 text-center border border-gray-600 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 to-transparent"></div>
                            
                            <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="GPI Papua" class="absolute opacity-20 h-32 pointer-events-none">
                            <span class="text-blue-400 font-bold tracking-widest text-sm mb-4 relative z-10">TATA IBADAH MINGGU</span>
                            <h3 class="text-white font-extrabold text-2xl mb-4 relative z-10 shadow-black drop-shadow-lg">NYANYIAN PERSIAPAN<br>KJ 15:1</h3>
                            <p class="text-gray-300 font-medium text-lg relative z-10 drop-shadow-md">Haleluya! Pujilah<br>Allah Yang Mahaagung</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-16 bg-white relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base text-blue-600 font-bold tracking-wide uppercase">Fitur Utama</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Segalanya Untuk Pelayanan Layar
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Live Control Panel</h3>
                    <p class="text-gray-600 leading-relaxed">Sistem remote kontrol yang memungkinkan operator mengubah teks, warna, jenis huruf, dan bayangan layar proyektor secara real-time tanpa disadari jemaat.</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Database Nyanyian Offline</h3>
                    <p class="text-gray-600 leading-relaxed">Tarik lirik Kidung Jemaat, NKB, PKJ, dan lagu lainnya langsung dari database lokal. Sistem otomatis memecah bait lagu agar rapi di proyektor.</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tarik Ayat Alkitab</h3>
                    <p class="text-gray-600 leading-relaxed">Fitur pencarian Alkitab pintar terintegrasi. Ketik nama kitab dan pasal, sistem akan otomatis menarik ayat ke dalam slide siap tayang.</p>
                </div>

            </div>
        </div>
    </div>

    <footer class="bg-slate-900 py-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm mb-4 md:mb-0">
                &copy; {{ date('Y') }} Sistem Multimedia GPI Papua. All rights reserved.
            </p>
            <div class="flex space-x-6 text-sm text-gray-400">
                <span>Versi 1.0 (Powered by Laravel {{ Illuminate\Foundation\Application::VERSION }})</span>
            </div>
        </div>
    </footer>

</body>
</html>