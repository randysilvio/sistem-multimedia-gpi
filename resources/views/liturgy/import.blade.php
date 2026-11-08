@extends('layouts.admin')

@section('eyebrow', 'Ibadah')
@section('title', 'Buat Jadwal dari Buletin')

@section('content')
    <form method="POST" action="{{ route('liturgy.import.review') }}" class="row g-4">
        @csrf
        <div class="col-lg-8">
            <div class="adm-card">
                <div class="adm-card-head">
                    <h2>1. Teks tata ibadah</h2>
                </div>
                <div class="p-3 p-md-4">
                    <p class="adm-muted small mb-3">
                        Unggah PDF buletin, atau salin teksnya dari Word lalu tempel di bawah. PDF booklet yang dilipat juga bisa.
                        Template, tanggal, tema, bacaan dan lagu akan diisi otomatis, lalu Anda periksa dulu sebelum disimpan.
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-1">
                        <label class="btn btn-outline-secondary mb-0">
                            Pilih PDF buletin
                            <input type="file" id="pdf-file" accept="application/pdf,.pdf" hidden>
                        </label>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="pdf-booklet">
                            <label class="form-check-label small" for="pdf-booklet">PDF berbentuk booklet (2 halaman per sisi, untuk dilipat)</label>
                        </div>
                    </div>
                    <div id="pdf-status" class="small mt-2 text-muted"></div>

                    <textarea name="text" id="bulletin-text" class="form-control mt-3 font-monospace" rows="18" required
                        placeholder="Contoh:&#10;LITURGI IBADAH MINGGU KE-4&#10;MINGGU, 22 NOVEMBER 2026&#10;Nyanyian Persiapan : DSL. 136 : 1, 2&#10;…&#10;PEMBACAAN ALKITAB : EFESUS 1 : 15 - 23">{{ old('text') }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="adm-card">
                <div class="adm-card-head"><h2>2. Template</h2></div>
                <div class="p-3 p-md-4">
                    <label class="form-label small fw-semibold" for="liturgy_id">Pakai template</label>
                    <select name="liturgy_id" id="liturgy_id" class="form-select">
                        <option value="">Tebak otomatis dari isi buletin</option>
                        @foreach($liturgies as $l)
                            <option value="{{ $l->id }}" @selected(old('liturgy_id') == $l->id)>{{ $l->name }}</option>
                        @endforeach
                    </select>
                    <p class="adm-muted small mt-2 mb-4">Contoh: buletin "Minggu ke-4" memakai template Minggu IV, "Adven" memakai Adven I.</p>
                    <button type="submit" class="btn btn-primary w-100">Periksa hasil</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite('resources/js/import-pdf.js')
@endpush
