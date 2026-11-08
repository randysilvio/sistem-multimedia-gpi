<?php

/*
|--------------------------------------------------------------------------
| Pengaturan bawaan layar live (proyektor / TV)
|--------------------------------------------------------------------------
| Semua ukuran layar memakai satuan sentimeter fisik. Aplikasi menggambar
| slide pada kanvas logis 1920x1080 lalu mengubah cm -> piksel berdasarkan
| ukuran layar sebenarnya, sehingga batas 20cm / 5cm tetap tepat di layar.
|
| Default: TV 55 inci 16:9 -> lebar 121,8 cm x tinggi 68,5 cm.
*/

return [

    // Logo. Urutan: unggahan di menu Pengaturan -> PRESENTATION_LOGO_URL di .env ->
    // file public/images/logo.png -> lambang netral bawaan (public/images/logo-default.svg).
    'logo_url' => env('PRESENTATION_LOGO_URL'),
    'logo_file' => 'images/logo.png',
    'logo_default' => 'images/logo-default.svg',

    // Gaya slide lirik lagu (tab Lagu di Kontrol Live)
    'song_styles' => ['nomor', 'terang', 'klasik', 'kiri'],

    'backgrounds' => ['static', 'aurora', 'flow', 'bokeh', 'waves', 'rays', 'grid', 'video'],

    'fonts' => ['Inter', 'Montserrat', 'Roboto', 'Lato', 'Oswald', 'Playfair Display', 'Arial'],

    'defaults' => [
        'screen' => [
            'widthCm' => 121.8,
            'heightCm' => 68.5,
            'marginTopCm' => 20,
            'marginSideCm' => 5,
            'marginBottomCm' => 5,
        ],
        'header' => [
            'show' => true,   // tampilkan judul slide di area atas
            'logo' => true,   // tampilkan logo di area atas
        ],
        'design' => [
            'bg' => 'aurora',
            'color1' => '#1b2735',
            'color2' => '#2b6cb0',
            'color3' => '#05070d',
            'speed' => 1,          // pengali kecepatan animasi (0.25 - 3)
            'intensity' => 0.7,    // kekuatan warna animasi (0 - 1)
            'powerSave' => false,  // true = latar diam (tanpa animasi)
            'font' => 'Inter',
            'textColor' => '#ffffff',
            'shadowColor' => '#000000',
            'shadowStrength' => 0.85,
            'songMark' => true,    // slide nyanyian memakai warna lirik tersendiri
            'songColor' => '#ffe08a', // warna lirik nyanyian / respons yang dinyanyikan
            'songStyle' => 'nomor',   // gaya slide lirik: nomor | terang | klasik | kiri
        ],
        'guides' => false,         // garis bantu batas area di layar live
        'fonts' => [],             // [slideKey => ukuran px pada kanvas 1920x1080]
        'hidden' => [],            // [slideKey, ...] slide yang dilewati saat tayang (Sembunyikan Slide)
        'notes' => [],             // [slideKey => catatan operator] hanya tampil di control panel
    ],
];
