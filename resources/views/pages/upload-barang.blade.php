
@extends('layouts.app')

@section('title', 'Upload Barang - LoakLokal')

@push('styles')
    @vite(['resources/css/barang.css'])
@endpush

@section('content')
<div class="barang-page">

    <div class="barang-header">
        <a href="{{ url()->previous() }}" class="back-link" aria-label="Kembali">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <h1>Upload Barang Loak</h1>
    </div>

    <div class="barang-form-card">
        <h2>Informasi Barang</h2>

        <p class="form-description">
            Lengkapi informasi barang yang ingin kamu jual, tukar, atau hibahkan.
        </p>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                Ada data yang belum benar. Periksa kembali form kamu.
            </div>
        @endif

        @include('pages.barang.partials.form')
    </div>
</div>
@endsection
