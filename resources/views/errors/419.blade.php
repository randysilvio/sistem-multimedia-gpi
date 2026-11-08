@extends('errors.layout')
@section('code', '419')
@section('title', 'Sesi sudah kedaluwarsa')
@section('message', 'Halaman terlalu lama dibiarkan terbuka sehingga sesi keamanannya habis. Muat ulang halaman lalu kirim lagi.')
@section('actions')
    <a class="btn primary" href="javascript:location.reload()">Muat ulang</a>
    <a class="btn" href="{{ url('/login') }}">Masuk lagi</a>
@endsection
