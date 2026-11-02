
import { Realtime } from 'ably';

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('chatSearchInput');
    const tombolHapus = document.getElementById('chatSearchClear');
    const daftarChat = document.getElementById('chatList');
    const pesanKosong = document.getElementById('chatEmpty');

    if (!input || !daftarChat) {
        return;
    }

    const currentUserId = Number(daftarChat.dataset.currentUser);
    const tokenUrl = daftarChat.dataset.tokenUrl;

    let realtime = null;
    let userChannel = null;
    let isRefreshing = false;
    let refreshQueued = false;

    const subscribedChannels = new Map();

    // ==========================================
    // MENGAMBIL ITEM CHAT
    // ==========================================

    function getItemChat() {
        return Array.from(
            daftarChat.querySelectorAll('.chat-item')
        );
    }

    function normalisasi(teks) {
        return (teks || '')
            .trim()
            .toLocaleLowerCase('id-ID');
    }

    // ==========================================
    // PENCARIAN CHAT
    // ==========================================

    function filterChat() {
        const kataKunci = normalisasi(input.value);
        const itemChat = getItemChat();

        let jumlahTampil = 0;

        itemChat.forEach(function (item) {
            const nama = normalisasi(item.dataset.nama);
            const peran = normalisasi(item.dataset.peran);
            const pesan = normalisasi(item.dataset.pesan);

            const cocok =
                nama.includes(kataKunci) ||
                peran.includes(kataKunci) ||
                pesan.includes(kataKunci);

            item.hidden = !cocok;

            if (cocok) {
                jumlahTampil++;
            }
        });

        if (pesanKosong) {
            pesanKosong.hidden = jumlahTampil > 0;
        }

        if (tombolHapus) {
            tombolHapus.hidden = input.value.length === 0;
        }
    }

    input.addEventListener('input', filterChat);

    if (tombolHapus) {
        tombolHapus.addEventListener('click', function () {
            input.value = '';
            filterChat();
            input.focus();
        });
    }

    // ==========================================
    // PREVIEW PESAN
    // ==========================================

    function getPreviewPesan(message) {
        if (message.type === 'image') {
            return 'Mengirim gambar';
        }

        if (message.type === 'video') {
            return 'Mengirim video';
        }

        if (message.type === 'location') {
            return 'Membagikan lokasi';
        }

        return message.body || 'Pesan';
    }

    // ==========================================
    // FORMAT WAKTU PESAN
    // ==========================================

    function getWaktuPesan(message) {
        if (!message.created_at) {
            return '';
        }

        const tanggal = new Date(message.created_at);

        if (Number.isNaN(tanggal.getTime())) {
            return '';
        }

        const sekarang = new Date();

        const opsiWaktu = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        };

        if (tanggal.toDateString() === sekarang.toDateString()) {
            return new Intl.DateTimeFormat(
                'id-ID',
                opsiWaktu
            ).format(tanggal);
        }

        const kemarin = new Date(sekarang);
        kemarin.setDate(kemarin.getDate() - 1);

        if (tanggal.toDateString() === kemarin.toDateString()) {
            return 'Kemarin';
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
        }).format(tanggal);
    }

    // ==========================================
    // BADGE PESAN BELUM DIBACA
    // ==========================================

    function updateUnreadBadge(item, senderId) {
        if (Number(senderId) === currentUserId) {
            return;
        }

        const chatMeta = item.querySelector('.chat-meta');

        if (!chatMeta) {
            return;
        }

        let badge = item.querySelector('.chat-unread-badge');

        let jumlahUnread = badge
            ? Number(badge.textContent.replace('+', ''))
            : 0;

        jumlahUnread++;

        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'chat-unread-badge';
            chatMeta.appendChild(badge);
        }

        badge.textContent = jumlahUnread > 99
            ? '99+'
            : jumlahUnread;

        badge.setAttribute(
            'aria-label',
            `${jumlahUnread} pesan belum dibaca`
        );
    }

    // ==========================================
    // MEMPERBARUI ITEM CHAT YANG SUDAH ADA
    // ==========================================

    function updateChatItem(item, message) {
        const chatInfo = item.querySelector('.chat-info');
        const chatMeta = item.querySelector('.chat-meta');

        if (!chatInfo || !chatMeta) {
            return;
        }

        const isMine =
            Number(message.sender_id) === currentUserId;

        const preview = getPreviewPesan(message);

        const paragrafPesan = chatInfo.querySelectorAll(
            '.chat-last-message'
        );

        let lastMessageElement = paragrafPesan.length
            ? paragrafPesan[paragrafPesan.length - 1]
            : null;

        if (!lastMessageElement) {
            lastMessageElement = document.createElement('p');
            lastMessageElement.className = 'chat-last-message';
            chatInfo.appendChild(lastMessageElement);
        }

        lastMessageElement.replaceChildren();

        if (isMine) {
            const labelAnda = document.createElement('strong');
            labelAnda.textContent = 'Anda: ';
            lastMessageElement.appendChild(labelAnda);
        }

        lastMessageElement.appendChild(
            document.createTextNode(preview)
        );

        const waktu = getWaktuPesan(message);

        let timeElement = chatMeta.querySelector('time');

        if (waktu) {
            if (!timeElement) {
                timeElement = document.createElement('time');
                chatMeta.prepend(timeElement);
            }

            timeElement.textContent = waktu;
        }

        const dataBarang = item.querySelector(
            '.chat-info .chat-last-message small'
        );

        const namaBarang = dataBarang
            ? dataBarang.textContent.trim()
            : '';

        item.dataset.pesan = [
            item.dataset.nama || '',
            item.dataset.peran || '',
            preview,
            namaBarang,
        ].join(' ');

        updateUnreadBadge(item, message.sender_id);

        // Pindahkan chat terbaru ke posisi paling atas.
        daftarChat.insertBefore(item, getItemChat()[0] || null);

        filterChat();
    }

    // ==========================================
    // MENCARI ITEM BERDASARKAN ID PERCAKAPAN
    // ==========================================

    function findChatItem(conversationId) {
        return getItemChat().find(function (item) {
            return Number(item.dataset.conversationId) ===
                Number(conversationId);
        }) || null;
    }

    // ==========================================
    // SUBSCRIBE CHANNEL PERCAKAPAN
    // ==========================================

    function subscribeConversation(conversationId) {
        if (!realtime || !conversationId) {
            return;
        }

        const id = String(conversationId);

        if (subscribedChannels.has(id)) {
            return;
        }

        const channel = realtime.channels.get(
            `loaklokal:conversation:${id}`
        );

        channel.subscribe('message', function (ablyMessage) {
            const message = ablyMessage.data;

            if (!message || Number(message.conversation_id) !== Number(id)) {
                return;
            }

            const item = findChatItem(id);

            if (item) {
                updateChatItem(item, message);
            } else {
                // Jika item belum ada, ambil data daftar chat terbaru.
                refreshChatList();
            }
        });

        subscribedChannels.set(id, channel);
    }

    // ==========================================
    // SUBSCRIBE SEMUA CHAT YANG ADA DI DAFTAR
    // ==========================================

    function subscribeExistingConversations() {
        getItemChat().forEach(function (item) {
            subscribeConversation(item.dataset.conversationId);
        });
    }

    // ==========================================
    // MEMPERBARUI DAFTAR CHAT TANPA RELOAD MANUAL
    // ==========================================

    async function refreshChatList() {
        if (isRefreshing) {
            refreshQueued = true;
            return;
        }

        isRefreshing = true;

        try {
            const response = await fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(
                    `Gagal mengambil daftar chat. Status: ${response.status}`
                );
            }

            const html = await response.text();

            const parser = new DOMParser();
            const documentBaru = parser.parseFromString(
                html,
                'text/html'
            );

            const daftarChatBaru = documentBaru.getElementById('chatList');

            if (!daftarChatBaru) {
                throw new Error('Elemen chatList tidak ditemukan pada respons server.');
            }

            // Ganti isi daftar chat dengan data terbaru dari server.
            daftarChat.innerHTML = daftarChatBaru.innerHTML;

            // Perbarui tampilan pencarian.
            filterChat();

            // Langsung subscribe percakapan yang baru muncul.
            subscribeExistingConversations();

            console.log('LoakLokal: daftar chat berhasil diperbarui.');

        } catch (error) {
            console.error(
                'Gagal memperbarui daftar chat:',
                error
            );
        } finally {
            isRefreshing = false;

            if (refreshQueued) {
                refreshQueued = false;
                refreshChatList();
            }
        }
    }

    // ==========================================
    // CHANNEL KHUSUS PENGGUNA
    // ==========================================

    function subscribeUserChannel() {
        if (!realtime) {
            return;
        }

        userChannel = realtime.channels.get(
            `loaklokal:user:${currentUserId}`
        );

        userChannel.subscribe(
            'conversation-message',
            function (ablyMessage) {
                const message = ablyMessage.data;

                if (!message || !message.conversation_id) {
                    return;
                }

                /*
                 * Ambil ulang daftar dari server.
                 * Ini menangani percakapan yang sebelumnya
                 * dihapus dari daftar, lalu aktif kembali.
                 */
                refreshChatList();
            }
        );
    }

    // ==========================================
    // INISIALISASI ABLY
    // ==========================================

    async function initializeRealtime() {
        if (!tokenUrl) {
            console.warn('URL token Ably tidak ditemukan.');
            return;
        }

        try {
            realtime = new Realtime({
                authUrl: tokenUrl,
                authMethod: 'GET',
            });

            // Subscribe channel pengguna meskipun daftar chat kosong.
            subscribeUserChannel();

            // Subscribe percakapan yang sudah ada.
            subscribeExistingConversations();

            console.log(
                'LoakLokal: real-time daftar chat berhasil diinisialisasi.'
            );

        } catch (error) {
            console.error(
                'Gagal menghubungkan real-time daftar chat:',
                error
            );
        }
    }

    // ==========================================
    // INISIALISASI HALAMAN
    // ==========================================

    filterChat();
    initializeRealtime();

    // ==========================================
    // TUTUP KONEKSI SAAT HALAMAN DITINGGALKAN
    // ==========================================

    window.addEventListener('pagehide', function () {
        if (realtime) {
            realtime.close();
            realtime = null;
        }

        subscribedChannels.clear();
    });
});
