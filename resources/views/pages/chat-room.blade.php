@extends('layouts.app')

@section('title', 'Percakapan - LoakLokal')

@push('styles')
    @vite(['resources/css/chat-room.css'])
@endpush

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $isBuyer = (int) auth()->id() === (int) $conversation->buyer_id;
        $otherUser = $isBuyer ? $conversation->seller : $conversation->buyer;
        $barang = $conversation->barang;

        $otherUserAvatar = $otherUser->profile_photo_path ? Storage::url($otherUser->profile_photo_path) : null;

        $otherUserInitial = strtoupper(substr($otherUser->name ?? 'U', 0, 1));
    @endphp

    <div class="chat-room-page">
        <div class="chat-room-container">

            {{-- HEADER CHAT --}}
            <header class="chat-room-header">

                <a href="{{ route('chat.index') }}" class="chat-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                <div class="chat-header-divider"></div>

                <div class="chat-user-avatar">
                    @if ($otherUserAvatar)
                        <img src="{{ $otherUserAvatar }}" alt="{{ $otherUser->name }}">
                    @else
                        {{ $otherUserInitial }}
                    @endif
                </div>

                <div class="chat-user-info" id="chatUserInfo" data-other-user-id="{{ $otherUser->id }}">

                    <h2>{{ $otherUser->name }}</h2>

                    <span class="chat-user-role">
                        {{ $isBuyer ? 'Penjual' : 'Pembeli' }}
                    </span>

                    <div class="chat-online-status" id="chatOnlineStatus">
                        <span class="chat-status-indicator offline" id="chatStatusIndicator"></span>

                        <span class="chat-status-text" id="chatStatusText">Offline</span>
                    </div>

                    <div class="chat-typing-indicator" id="chatTypingIndicator" aria-live="polite" aria-atomic="true"
                        hidden>

                        <div class="chat-typing-dots" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <span id="chatTypingText">
                            {{ $otherUser->name }} sedang mengetik...
                        </span>
                    </div>
                </div>

                {{-- MENU TIGA TITIK HEADER --}}
                <div class="chat-header-actions">

                    <button type="button" class="chat-header-menu-button" id="chatHeaderMenuButton"
                        aria-label="Menu percakapan" aria-expanded="false" aria-controls="chatHeaderDropdown">

                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>

                    <div class="chat-header-dropdown" id="chatHeaderDropdown" hidden>

                        <button type="button" id="deleteConversationButton" class="chat-dropdown-item">

                            <i class="fa-regular fa-trash-can"></i>
                            <span>Hapus obrolan</span>
                        </button>

                    </div>
                </div>

            </header>

            {{-- INFORMASI BARANG --}}
            <section class="chat-product-card">

                <div class="chat-product-image">
                    @if ($barang->foto)
                        <img src="{{ Storage::url($barang->foto) }}" alt="{{ $barang->nama }}">
                    @else
                        <i class="fa-solid fa-box-open"></i>
                    @endif
                </div>

                <div class="chat-product-info">
                    <h3>{{ $barang->nama }}</h3>

                    <p>
                        @if ($barang->harga !== null)
                            Rp{{ number_format($barang->harga, 0, ',', '.') }}
                        @else
                            Gratis / Hibah
                        @endif
                    </p>
                </div>

                <a href="{{ route('barang.show', $barang) }}" class="chat-product-link">

                    Lihat Barang
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

            </section>

            {{-- AREA PESAN --}}
            <section class="chat-messages" id="chatMessages" data-current-user="{{ auth()->id() }}"
                data-conversation-id="{{ $conversation->id }}"
                data-token-url="{{ route('chat.ably-token', $conversation) }}" data-other-user-id="{{ $otherUser->id }}"
                data-other-user-name="{{ $otherUser->name }}" data-other-user-avatar="{{ $otherUserAvatar ?? '' }}"
                data-other-user-initial="{{ $otherUserInitial }}"
                data-delete-conversation-url="{{ route('chat.delete-for-me', $conversation) }}"
                data-chat-index-url="{{ route('chat.index') }}">

                <div class="chat-date-divider">
                    <span>Riwayat Percakapan</span>
                </div>

                {{-- TIMELINE PESAN DAN PENANDA PESAN TERHAPUS --}}
                @forelse ($timeline as $entry)

                @if ($entry['kind'] === 'message')

                    @php
                        $message = $entry['item'];

                        $isMine = (int) $message->sender_id === (int) auth()->id();

                        $messageStatus = null;

                        if ($isMine) {
                            if ($message->read_at) {
                                $messageStatus = 'read';
                            } elseif ($message->delivered_at) {
                                $messageStatus = 'delivered';
                            } else {
                                $messageStatus = 'sent';
                            }
                        }

                        $statusLabel = match ($messageStatus) {
                            'read' => 'Dibaca',
                            'delivered' => 'Diterima',
                            'sent' => 'Terkirim',
                            default => '',
                        };
                    @endphp

                    <div class="chat-message {{ $isMine ? 'mine' : 'theirs' }}"
                        data-message-id="{{ $message->id }}"
                        data-sender-id="{{ $message->sender_id }}">

                        @if (!$isMine)
                            <div class="chat-message-avatar">
                                @if ($otherUserAvatar)
                                    <img src="{{ $otherUserAvatar }}"
                                        alt="{{ $otherUser->name }}">
                                @else
                                    {{ $otherUserInitial }}
                                @endif
                            </div>
                        @endif

                        <div class="chat-message-content">

                            {{-- BUBBLE PESAN --}}
                            <div class="chat-bubble">

                                @if ($message->type === 'text')

                                    <p>{{ $message->body }}</p>

                                @elseif ($message->type === 'image')

                                    <a href="{{ Storage::url($message->media_path) }}"
                                    target="_blank"
                                    rel="noopener">

                                        <img src="{{ Storage::url($message->media_path) }}"
                                            alt="Foto yang dikirim"
                                            class="chat-message-image"
                                            loading="lazy">
                                    </a>

                                    @if ($message->body)
                                        <p>{{ $message->body }}</p>
                                    @endif

                                @elseif ($message->type === 'video')

                                    <video controls
                                        preload="metadata"
                                        playsinline
                                        class="chat-message-video">

                                        <source src="{{ Storage::url($message->media_path) }}"
                                                type="{{ $message->media_mime }}">

                                        Browser kamu tidak mendukung pemutaran video.
                                    </video>

                                    @if ($message->body)
                                        <p>{{ $message->body }}</p>
                                    @endif

                                @elseif ($message->type === 'location')

                                    <a href="https://www.google.com/maps?q={{ $message->location_lat }},{{ $message->location_lng }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="chat-location-card">

                                        <i class="fa-solid fa-location-dot"></i>

                                        <span>
                                            {{ $message->location_name ?: 'Lokasi yang dibagikan' }}

                                            <small>
                                                Lihat lokasi di Google Maps ↗
                                            </small>
                                        </span>
                                    </a>

                                @endif

                            </div>

                            {{-- METADATA DI SEBELAH KANAN BUBBLE --}}
                            <div class="chat-message-meta">

                                <time class="chat-message-time">
                                    {{ $message->created_at->format('H:i') }}
                                </time>

                                @if ($isMine)
                                    <span class="chat-message-status"
                                        data-status="{{ $messageStatus }}"
                                        title="{{ $statusLabel }}"
                                        aria-label="{{ $statusLabel }}">

                                        {{ $messageStatus === 'sent' ? '✓' : '✓✓' }}

                                    </span>
                                @endif

                                {{-- MENU AKSI PESAN --}}
                                <div class="chat-message-actions">

                                    <button type="button"
                                            class="chat-message-menu-button"
                                            aria-label="Pilihan pesan"
                                            aria-expanded="false">

                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>

                                    <div class="chat-message-dropdown" hidden>

                                        <button type="button"
                                                class="chat-delete-for-me"
                                                data-message-id="{{ $message->id }}">

                                            <i class="fa-regular fa-trash-can"></i>
                                            Hapus untuk saya
                                        </button>

                                        @if ($isMine)
                                            <button type="button"
                                                    class="chat-delete-for-everyone"
                                                    data-message-id="{{ $message->id }}">

                                                <i class="fa-solid fa-trash"></i>
                                                Hapus untuk semua orang
                                            </button>
                                        @endif

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                @elseif ($entry['kind'] === 'deleted')

                    @php
                        $tombstone = $entry['item'];

                        $isMine = (int) $tombstone->sender_id === (int) auth()->id();

                        $deletedByCurrentUser = (int) $tombstone->deleted_by === (int) auth()->id();
                    @endphp

                    <div class="chat-message {{ $isMine ? 'mine' : 'theirs' }} chat-message-deleted"
                        data-original-message-id="{{ $tombstone->original_message_id }}"
                        data-sender-id="{{ $tombstone->sender_id }}">

                        @if (!$isMine)
                            <div class="chat-message-avatar">
                                @if ($otherUserAvatar)
                                    <img src="{{ $otherUserAvatar }}"
                                        alt="{{ $otherUser->name }}">
                                @else
                                    {{ $otherUserInitial }}
                                @endif
                            </div>
                        @endif

                        <div class="chat-message-content">

                            <div class="chat-bubble chat-deleted-bubble">

                                <span class="chat-deleted-text">
                                    <i class="fa-solid fa-ban"></i>

                                    {{ $deletedByCurrentUser
                                        ? 'Anda menghapus pesan ini'
                                        : 'Pesan ini telah dihapus' }}
                                </span>

                            </div>

                            <div class="chat-message-meta">

                                <time class="chat-message-time">
                                    {{ $tombstone->original_created_at
                                        ? \Illuminate\Support\Carbon::parse($tombstone->original_created_at)->format('H:i')
                                        : '' }}
                                </time>

                            </div>

                        </div>
                    </div>

                @endif

            @empty

                <div class="chat-empty-state" id="chatEmptyState">
                    <i class="fa-regular fa-comments"></i>

                    <h3>Mulai Percakapan</h3>

                    <p>Kirim pesan untuk memulai komunikasi.</p>
                </div>

            @endforelse

            </section>

            {{-- PREVIEW GAMBAR --}}
            <div class="chat-image-preview" id="chatImagePreview" hidden>

                <img id="chatImagePreviewImage" src="" alt="Preview gambar">

                <button type="button" id="chatRemoveImage" class="chat-remove-image" aria-label="Batalkan gambar">

                    <i class="fa-solid fa-xmark"></i>
                </button>

                <span id="chatImagePreviewName"></span>
            </div>

            {{-- PREVIEW VIDEO --}}
            <div class="chat-video-preview" id="chatVideoPreview" hidden>

                <video id="chatVideoPreviewPlayer" controls playsinline preload="metadata"></video>

                <div class="chat-video-preview-info">

                    <span id="chatVideoPreviewName"></span>

                    <button type="button" id="chatRemoveVideo" class="chat-remove-video">

                        <i class="fa-solid fa-xmark"></i>
                        Batalkan video
                    </button>

                </div>
            </div>

            {{-- COMPOSER --}}
            <form class="chat-composer" id="chatMessageForm" action="{{ route('chat.messages.store', $conversation) }}"
                method="POST" enctype="multipart/form-data">

                @csrf

                <input type="file" id="chatImageInput" name="image"
                    accept="image/jpeg,image/png,image/webp,image/gif" hidden>

                <input type="file" id="chatVideoInput" name="video" accept="video/mp4,video/webm,video/ogg" hidden>

                <div class="chat-composer-input">

                    <input type="text" name="body" id="chatMessageInput" placeholder="Ketik pesan..."
                        maxlength="5000" autocomplete="off">

                    <div class="chat-composer-divider"></div>

                    <button type="button" class="chat-tool-button" id="chatImageButton" title="Kirim foto"
                        aria-label="Pilih foto">

                        <i class="fa-regular fa-image"></i>
                    </button>

                    <button type="button" class="chat-tool-button" id="chatVideoButton" title="Kirim video"
                        aria-label="Pilih video">

                        <i class="fa-solid fa-video"></i>
                    </button>

                    <button type="button" class="chat-tool-button" disabled title="Bagikan lokasi akan segera tersedia">

                        <i class="fa-solid fa-location-dot"></i>
                    </button>

                </div>

                <button type="submit" class="chat-send-button" id="chatSendButton" aria-label="Kirim pesan">

                    <i class="fa-solid fa-paper-plane"></i>
                </button>

            </form>

            <p id="chatErrorMessage" class="chat-error-message" role="alert"></p>

        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/chat-room.js'])
@endpush
