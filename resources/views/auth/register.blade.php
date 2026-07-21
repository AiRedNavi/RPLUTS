{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="container-fluid px-4 py-5 d-flex justify-content-center">
    <div class="tw-card" style="max-width: 420px; width: 100%;">
        <div class="tw-eyebrow">Akses Sistem</div>
        <h2 class="font-display mb-1">Buat Akun Baru</h2>
        <p class="tw-muted mb-4" style="font-size:0.88rem;">Simpan negara favorit dan pantau risiko pribadimu.</p>

        @if ($errors->any())
            <div class="tw-badge tw-badge--high d-block mb-3 p-2" style="font-size:0.8rem;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.register') }}">
            @csrf
            <div class="mb-3">
                <label class="tw-muted mb-1 d-block" style="font-size:0.82rem;">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="tw-muted mb-1 d-block" style="font-size:0.82rem;">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="tw-muted mb-1 d-block" style="font-size:0.82rem;">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="tw-muted mb-1 d-block" style="font-size:0.82rem;">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn w-100" style="background: var(--signal-green); color: var(--ink-950); font-weight:600;">Daftar</button>
        </form>

        <p class="text-center tw-muted mt-4 mb-0" style="font-size:0.85rem;">
            Sudah punya akun? <a href="{{ route('auth.login') }}">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection