<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 flex flex-col h-full border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex-grow">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Sistem Layar Proyektor</h4>
                        </div>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                            Pusat kendali utama untuk merakit tata ibadah, menyusun slide presentasi, dan mengendalikan tampilan layar proyektor secara langsung (Live Control Panel).
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4">
                        <a href="{{ route('liturgy.gallery') }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                            Buka Sistem Multimedia
                        </a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 flex flex-col h-full border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex-grow">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19.5V15m6 4.5v-4.5M9 15V9m6 6V9m-6 0V6m6 0v3m-3-9v18.75m-4.5 0h9" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Database Nyanyian</h4>
                        </div>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                            Kelola koleksi lirik lagu (Kidung Jemaat, NKB, PKJ, Rohani) yang tersimpan secara lokal. Tambahkan lagu baru agar siap ditarik secara otomatis ke dalam slide presentasi.
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4">
                        <a href="{{ route('songs.index') }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                            Kelola Database Lagu
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>