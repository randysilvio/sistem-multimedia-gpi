@props(['name'])
@php
    // Ikon Bootstrap Icons (lihat resources/icons.php). Dipakai hemat: hanya tombol tanpa teks.
    static $icons = null;
    $icons ??= require resource_path('icons.php');
@endphp
<svg {{ $attributes->merge(['class' => 'adm-ico']) }} viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">{!! $icons[$name] ?? '' !!}</svg>
