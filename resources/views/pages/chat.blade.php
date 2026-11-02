
@extends('layouts.app')

@section('title', 'Chat - LoakLokal')

@push('styles')
    @vite(['resources/css/chat.css'])
@endpush

@section('content')

{{-- HEADER KHUSUS HALAMAN CHAT --}}
<header class="chat-top-header">
    <a href="{{ route('beranda') }}" class="chat-top-brand">
        <img
            src="{{ asset('assets/images/LOGO.png') }}"
            alt="Logo LoakLokal"
            class="chat-top-logo"
        >

        <span>LOAKLOKAL</span>
    </a>

    <a
        href="#"
        class="chat-top-notification"
        aria-label="Notifikasi"
        title="Notifikasi"
    >
        <i class="fa-solid fa-bell"></i>
    </a>
</header>

{{-- KONTEN HALAMAN CHAT --}}
<main class="chat-page">
    <div class="chat-container">

        {{-- JUDUL --}}
        <header class="chat-heading">
            <span class="chat-heading-label">PESAN PRIBADI</span>

            <h1>Daftar Percakapan</h1>

            <p>
                Terhubung dan berkomunikasi dengan komunitas LoakLokal.
            </p>
        </header>

        {{-- PENCARIAN CHAT --}}
        <div class="chat-search">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="search"
                id="chatSearchInput"
                placeholder="Cari pesan atau nama..."
                aria-label="Cari pesan atau nama"
                autocomplete="off"
            >

            <button
                type="button"
                id="chatSearchClear"
                aria-label="Hapus pencarian"
                hidden
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- DAFTAR PERCAKAPAN --}}
        <section
            class="chat-list"
            id="chatList"
            data-current-user="{{ auth()->id() }}"
            data-token-url="{{ route('chat.list-ably-token') }}"
        >

            @forelse ($conversations as $conversation)

                @php
                    $currentUserId = (int) auth()->id();

                    // Tentukan lawan bicara.
                    $otherUser = $currentUserId === (int) $conversation->buyer_id
                        ? $conversation->seller
                        : $conversation->buyer;

                    // Tentukan peran lawan bicara.
                    $otherRole = $currentUserId === (int) $conversation->buyer_id
                        ? 'Pemilik Barang'
                        : 'Pembeli';

                    // Ambil pesan terakhir.
                    $lastMessage = $conversation->messages->first();

                    // Ambil informasi barang.
                    $barang = $conversation->barang;

                    // Tentukan teks pesan terakhir.
                    $lastMessageText = 'Belum ada pesan';

                    if ($lastMessage) {
                        if ($lastMessage->type === 'image') {
                            $lastMessageText = 'Mengirim gambar';
                        } elseif ($lastMessage->type === 'video') {
                            $lastMessageText = 'Mengirim video';
                        } elseif ($lastMessage->type === 'location') {
                            $lastMessageText = 'Membagikan lokasi';
                        } else {
                            $lastMessageText = $lastMessage->body ?? 'Pesan';
                        }
                    }

                    // Tentukan apakah pesan terakhir dikirim pengguna login.
                    $isLastMessageMine = $lastMessage
                        && (int) $lastMessage->sender_id === $currentUserId;

                    // Format waktu pesan terakhir.
                    $messageTime = '';

                    if ($lastMessage && $lastMessage->created_at) {
                        if ($lastMessage->created_at->isToday()) {
                            $messageTime = $lastMessage->created_at->format('H:i');
                        } elseif ($lastMessage->created_at->isYesterday()) {
                            $messageTime = 'Kemarin';
                        } else {
                            $messageTime = $lastMessage->created_at->translatedFormat('d M');
                        }
                    }

                    // Inisial nama untuk avatar.
                    $initial = $otherUser && $otherUser->name
                        ? mb_strtoupper(mb_substr($otherUser->name, 0, 1))
                        : '?';

                    // Data pencarian.
                    $searchData = implode(' ', [
                        $otherUser->name ?? '',
                        $otherRole,
                        $lastMessageText,
                        $barang->nama ?? '',
                    ]);
                @endphp

                <a
                    href="{{ route('chat.show', $conversation) }}"
                    class="chat-item"
                    data-conversation-id="{{ $conversation->id }}"
                    data-nama="{{ $otherUser->name ?? '' }}"
                    data-peran="{{ $otherRole }}"
                    data-pesan="{{ $searchData }}"
                >

                    {{-- FOTO PROFIL LAWAN BICARA --}}
                    @if ($otherUser && $otherUser->profile_photo_path)
                        <div class="chat-avatar">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($otherUser->profile_photo_path) }}"
                                alt="{{ $otherUser->name }}"
                            >
                        </div>
                    @else
                        <div class="chat-avatar chat-avatar-blue">
                            {{ $initial }}
                        </div>
                    @endif

                    {{-- INFORMASI PERCAKAPAN --}}
                    <div class="chat-info">
                        <h2>
                            {{ $otherUser->name ?? 'Pengguna' }}
                            <span>({{ $otherRole }})</span>
                        </h2>

                        @if ($barang)
                            <p class="chat-last-message">
                                <small>
                                    {{ $barang->nama }}
                                </small>
                            </p>
                        @endif

                        <p class="chat-last-message">
                            @if ($isLastMessageMine)
                                <strong>Anda:</strong>
                            @endif

                            {{ $lastMessageText }}
                        </p>
                    </div>

                    {{-- WAKTU PESAN DAN BADGE UNREAD --}}
                    <div class="chat-meta">
                        @if ($messageTime)
                            <time>
                                {{ $messageTime }}
                            </time>
                        @endif

                        @if (($conversation->unread_count ?? 0) > 0)
                            <span
                                class="chat-unread-badge"
                                aria-label="{{ $conversation->unread_count }} pesan belum dibaca"
                            >
                                {{ $conversation->unread_count > 99 ? '99+' : $conversation->unread_count }}
                            </span>
                        @endif
                    </div>

                </a>

            @empty

                {{-- BELUM ADA PERCAKAPAN --}}
                <div class="chat-empty" id="chatEmpty">
                    <div class="chat-empty-icon">
                        <i class="fa-regular fa-comments"></i>
                    </div>

                    <h3>Belum ada percakapan</h3>

                    <p>
                        Percakapan dengan pembeli atau penjual akan muncul di sini.
                    </p>
                </div>

            @endforelse

            {{-- HASIL PENCARIAN KOSONG --}}
            @if ($conversations->isNotEmpty())
                <div class="chat-empty" id="chatEmpty" hidden>
                    <div class="chat-empty-icon">
                        <i class="fa-regular fa-comments"></i>
                    </div>

                    <h3>Percakapan tidak ditemukan</h3>

                    <p>
                        Coba gunakan nama atau kata kunci lainnya.
                    </p>
                </div>
            @endif

        </section>

    </div>
</main>

@endsection

@push('scripts')
    @vite(['resources/js/chat.js'])
@endpush
