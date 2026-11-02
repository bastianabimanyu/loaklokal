
import { Realtime } from 'ably';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chatMessageForm');
    const input = document.getElementById('chatMessageInput');
    const messages = document.getElementById('chatMessages');
    const sendButton = document.getElementById('chatSendButton');
    const errorMessage = document.getElementById('chatErrorMessage');

    const statusIndicator = document.getElementById('chatStatusIndicator');
    const statusText = document.getElementById('chatStatusText');
    const typingIndicator = document.getElementById('chatTypingIndicator');
    const typingText = document.getElementById('chatTypingText');

    const imageInput = document.getElementById('chatImageInput');
    const imageButton = document.getElementById('chatImageButton');
    const imagePreview = document.getElementById('chatImagePreview');
    const imagePreviewImage = document.getElementById('chatImagePreviewImage');
    const imagePreviewName = document.getElementById('chatImagePreviewName');
    const removeImageButton = document.getElementById('chatRemoveImage');

    const videoInput = document.getElementById('chatVideoInput');
    const videoButton = document.getElementById('chatVideoButton');
    const videoPreview = document.getElementById('chatVideoPreview');
    const videoPreviewPlayer = document.getElementById('chatVideoPreviewPlayer');
    const videoPreviewName = document.getElementById('chatVideoPreviewName');
    const removeVideoButton = document.getElementById('chatRemoveVideo');

    if (!form || !input || !messages || !sendButton) return;

    const csrfToken = form.querySelector('input[name="_token"]')?.value;
    const currentUserId = Number(messages.dataset.currentUser);
    const conversationId = messages.dataset.conversationId;
    const tokenUrl = messages.dataset.tokenUrl;

    const otherUserId = Number(messages.dataset.otherUserId);
    const otherUserName = messages.dataset.otherUserName || 'Pengguna';
    const otherUserAvatar = messages.dataset.otherUserAvatar || '';
    const otherUserInitial = messages.dataset.otherUserInitial || 'U';

    const messageUrl = form.action;
    const readUrl = `${messageUrl}/read`;

    // ==========================================
    // MENU HEADER DAN HAPUS OBROLAN
    // ==========================================

    const chatHeaderMenuButton = document.getElementById(
        'chatHeaderMenuButton'
    );

    const chatHeaderDropdown = document.getElementById(
        'chatHeaderDropdown'
    );

    const deleteConversationButton = document.getElementById(
        'deleteConversationButton'
    );

    const deleteConversationUrl = messages.dataset.deleteConversationUrl;
    const chatIndexUrl = messages.dataset.chatIndexUrl;

    function closeHeaderMenu() {
        if (chatHeaderDropdown) {
            chatHeaderDropdown.hidden = true;
        }

        chatHeaderMenuButton?.setAttribute('aria-expanded', 'false');
    }

    if (chatHeaderMenuButton && chatHeaderDropdown) {
        chatHeaderMenuButton.addEventListener('click', event => {
            event.stopPropagation();

            const willOpen = chatHeaderDropdown.hidden;
            chatHeaderDropdown.hidden = !willOpen;

            chatHeaderMenuButton.setAttribute(
                'aria-expanded',
                String(willOpen)
            );
        });
    }

    document.addEventListener('click', event => {
        if (!event.target.closest('.chat-header-actions')) {
            closeHeaderMenu();
        }
    });

    // ==========================================
    // UTILITAS
    // ==========================================

    const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    const MAX_VIDEO_SIZE = 50 * 1024 * 1024;

    const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/webm',
        'video/ogg',
    ];

    let selectedImage = null;
    let selectedVideo = null;
    let previewObjectUrl = null;
    let videoPreviewObjectUrl = null;

    let realtime = null;
    let channel = null;

    let typingTimeout = null;
    let typingSendInterval = null;
    let isTyping = false;
    let remoteTypingTimeout = null;

    const acknowledgedMessages = new Set();
    const TYPING_INTERVAL = 1000;
    const TYPING_TIMEOUT = 2000;

    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = message || '';
        }
    }

    async function postToLaravel(url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({}),
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Permintaan gagal diproses.'
            );
        }

        return result;
    }

    async function deleteRequest(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({}),
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Permintaan penghapusan gagal.'
            );
        }

        return result;
    }

    function formatMessageTime(value) {
        if (!value) return '';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        }).format(date);
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    // ==========================================
    // HAPUS OBROLAN DARI DAFTAR SAYA
    // ==========================================

    if (deleteConversationButton) {
        deleteConversationButton.addEventListener('click', async () => {
            if (!deleteConversationUrl) {
                showError('URL hapus obrolan belum tersedia.');
                closeHeaderMenu();
                return;
            }

            const confirmed = window.confirm(
                'Hapus obrolan ini dari daftar chat kamu? Riwayat pesan pengguna lain tidak akan dihapus.'
            );

            if (!confirmed) return;

            closeHeaderMenu();
            showError('');

            deleteConversationButton.disabled = true;

            try {
                await deleteRequest(deleteConversationUrl);

                window.location.href = chatIndexUrl || '/chat';
            } catch (error) {
                console.error('Gagal menghapus obrolan:', error);
                showError(error.message);

                deleteConversationButton.disabled = false;
            }
        });
    }

    // ==========================================
    // MENU PESAN
    // ==========================================

    function closeMessageMenus() {
        messages.querySelectorAll('.chat-message-dropdown').forEach(menu => {
            menu.hidden = true;
        });

        messages.querySelectorAll('.chat-message-menu-button').forEach(button => {
            button.setAttribute('aria-expanded', 'false');
        });
    }

    messages.addEventListener('click', event => {
        const menuButton = event.target.closest(
            '.chat-message-menu-button'
        );

        if (menuButton) {
            const actions = menuButton.closest('.chat-message-actions');
            const dropdown = actions?.querySelector(
                '.chat-message-dropdown'
            );

            if (!dropdown) return;

            const willOpen = dropdown.hidden;

            closeMessageMenus();

            dropdown.hidden = !willOpen;
            menuButton.setAttribute(
                'aria-expanded',
                String(willOpen)
            );

            return;
        }

        const deleteMeButton = event.target.closest(
            '.chat-delete-for-me'
        );

        if (deleteMeButton) {
            const messageId = deleteMeButton.dataset.messageId;

            closeMessageMenus();
            deleteMessageForMe(messageId);

            return;
        }

        const deleteEveryoneButton = event.target.closest(
            '.chat-delete-for-everyone'
        );

        if (deleteEveryoneButton) {
            const messageId = deleteEveryoneButton.dataset.messageId;

            closeMessageMenus();
            deleteMessageForEveryone(messageId);
        }
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.chat-message-actions')) {
            closeMessageMenus();
        }
    });

    // ==========================================
    // HAPUS PESAN UNTUK SAYA
    // ==========================================

    async function deleteMessageForMe(messageId) {
        if (!messageId) return;

        const confirmed = window.confirm(
            'Hapus pesan ini hanya dari tampilan chat kamu?'
        );

        if (!confirmed) return;

        showError('');

        try {
            await deleteRequest(
                `${messageUrl}/${messageId}/delete-for-me`
            );

            const row = messages.querySelector(
                `[data-message-id="${messageId}"]`
            );

            row?.remove();

            if (!messages.querySelector('.chat-message')) {
                showEmptyState();
            }
        } catch (error) {
            console.error(
                'Gagal menghapus pesan untuk saya:',
                error
            );

            showError(error.message);
        }
    }

    // ==========================================
    // HAPUS PESAN UNTUK SEMUA ORANG
    // ==========================================

    async function deleteMessageForEveryone(messageId) {
        if (!messageId) return;

        const confirmed = window.confirm(
            'Hapus pesan ini untuk semua orang? Pesan tidak dapat dikembalikan.'
        );

        if (!confirmed) return;

        showError('');

        try {
            const result = await deleteRequest(
                `${messageUrl}/${messageId}/delete-for-everyone`
            );

            replaceWithDeletedMessage({
                message_id: Number(messageId),
                sender_id: currentUserId,
                deleted_by: currentUserId,
                created_at: result.created_at || new Date().toISOString(),
                deleted_at: result.deleted_at || new Date().toISOString(),
            });
        } catch (error) {
            console.error(
                'Gagal menghapus pesan untuk semua orang:',
                error
            );

            showError(error.message);
        }
    }

    // ==========================================
    // EMPTY STATE
    // ==========================================

    function showEmptyState() {
        if (messages.querySelector('.chat-message')) return;
        if (document.getElementById('chatEmptyState')) return;

        const empty = document.createElement('div');
        empty.className = 'chat-empty-state';
        empty.id = 'chatEmptyState';

        const icon = document.createElement('i');
        icon.className = 'fa-regular fa-comments';

        const title = document.createElement('h3');
        title.textContent = 'Mulai Percakapan';

        const description = document.createElement('p');
        description.textContent =
            'Kirim pesan untuk memulai komunikasi.';

        empty.append(icon, title, description);
        messages.appendChild(empty);
    }

    // ==========================================
    // PENANDA PESAN DIHAPUS
    // ==========================================

    function replaceWithDeletedMessage(data) {
        const messageId = Number(
            data.message_id || data.original_message_id
        );

        if (!messageId) return;

        if (
            messages.querySelector(
                `[data-original-message-id="${messageId}"]`
            )
        ) {
            return;
        }

        const oldRow = messages.querySelector(
            `[data-message-id="${messageId}"]`
        );

        if (!oldRow) return;

        const isMine = Number(data.sender_id) === currentUserId;
        const timeValue = data.created_at || data.original_created_at;

        const row = document.createElement('div');

        row.className = `chat-message ${
            isMine ? 'mine' : 'theirs'
        } chat-message-deleted`;

        row.dataset.originalMessageId = messageId;

        if (!isMine) {
            row.appendChild(createOtherUserAvatar());
        }

        // Wrapper pesan
        const content = document.createElement('div');
        content.className = 'chat-message-content';

        // Bubble
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble chat-deleted-bubble';

        const deletedText = document.createElement('span');
        deletedText.className = 'chat-deleted-text';

        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-ban';

        const label = document.createTextNode(
            Number(data.deleted_by) === currentUserId
                ? 'Anda menghapus pesan ini'
                : 'Pesan ini telah dihapus'
        );

        deletedText.append(icon, label);
        bubble.appendChild(deletedText);

        // Metadata berada di luar bubble
        const meta = document.createElement('div');
        meta.className = 'chat-message-meta';

        const time = document.createElement('time');
        time.className = 'chat-message-time';
        time.textContent = formatMessageTime(timeValue);

        meta.appendChild(time);

        content.append(bubble, meta);
        row.appendChild(content);

        oldRow.replaceWith(row);
    }

    function handleMessageDeleted(ablyMessage) {
        const data = ablyMessage.data || {};

        if (
            Number(data.conversation_id) !== Number(conversationId)
        ) {
            return;
        }

        replaceWithDeletedMessage(data);
    }

    // ==========================================
    // PREVIEW GAMBAR
    // ==========================================

    function clearImagePreview() {
        selectedImage = null;

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        if (imageInput) imageInput.value = '';
        if (imagePreviewImage) imagePreviewImage.src = '';
        if (imagePreviewName) imagePreviewName.textContent = '';
        if (imagePreview) imagePreview.hidden = true;
    }

    // ==========================================
    // PREVIEW VIDEO
    // ==========================================

    function clearVideoPreview() {
        selectedVideo = null;

        if (videoPreviewObjectUrl) {
            URL.revokeObjectURL(videoPreviewObjectUrl);
            videoPreviewObjectUrl = null;
        }

        if (videoPreviewPlayer) {
            videoPreviewPlayer.pause();
            videoPreviewPlayer.removeAttribute('src');
            videoPreviewPlayer.load();
        }

        if (videoInput) videoInput.value = '';
        if (videoPreviewName) videoPreviewName.textContent = '';
        if (videoPreview) videoPreview.hidden = true;
    }

    if (imageButton && imageInput) {
        imageButton.addEventListener('click', () => {
            imageInput.click();
        });
    }

    if (videoButton && videoInput) {
        videoButton.addEventListener('click', () => {
            videoInput.click();
        });
    }

    if (imageInput) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files?.[0];

            if (!file) return;

            showError('');

            if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                clearImagePreview();
                showError(
                    'Format gambar harus JPG, PNG, WEBP, atau GIF.'
                );
                return;
            }

            if (file.size > MAX_IMAGE_SIZE) {
                clearImagePreview();
                showError('Ukuran gambar maksimal 5 MB.');
                return;
            }

            clearVideoPreview();
            selectedImage = file;

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }

            previewObjectUrl = URL.createObjectURL(file);

            if (imagePreviewImage) {
                imagePreviewImage.src = previewObjectUrl;
            }

            if (imagePreviewName) {
                imagePreviewName.textContent = file.name;
            }

            if (imagePreview) {
                imagePreview.hidden = false;
            }
        });
    }

    if (videoInput) {
        videoInput.addEventListener('change', () => {
            const file = videoInput.files?.[0];

            if (!file) return;

            showError('');

            if (!ALLOWED_VIDEO_TYPES.includes(file.type)) {
                clearVideoPreview();
                showError('Format video harus MP4, WebM, atau OGG.');
                return;
            }

            if (file.size > MAX_VIDEO_SIZE) {
                clearVideoPreview();
                showError('Ukuran video maksimal 50 MB.');
                return;
            }

            clearImagePreview();
            selectedVideo = file;

            if (videoPreviewObjectUrl) {
                URL.revokeObjectURL(videoPreviewObjectUrl);
            }

            videoPreviewObjectUrl = URL.createObjectURL(file);

            if (videoPreviewPlayer) {
                videoPreviewPlayer.src = videoPreviewObjectUrl;
            }

            if (videoPreviewName) {
                videoPreviewName.textContent =
                    `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
            }

            if (videoPreview) {
                videoPreview.hidden = false;
            }
        });
    }

    if (removeImageButton) {
        removeImageButton.addEventListener('click', () => {
            clearImagePreview();
            showError('');
        });
    }

    if (removeVideoButton) {
        removeVideoButton.addEventListener('click', () => {
            clearVideoPreview();
            showError('');
        });
    }

    // ==========================================
    // ONLINE / OFFLINE
    // ==========================================

    function updateOnlineStatus(isOnline) {
        if (!statusIndicator || !statusText) return;

        statusIndicator.classList.toggle('online', isOnline);
        statusIndicator.classList.toggle('offline', !isOnline);

        statusText.classList.toggle('online', isOnline);
        statusText.textContent = isOnline ? 'Online' : 'Offline';
    }

    async function refreshOnlineStatus() {
        if (!channel || !otherUserId) return;

        try {
            const members = await channel.presence.get();

            const online = members.some(member =>
                member.clientId === `user-${otherUserId}`
            );

            updateOnlineStatus(online);
        } catch (error) {
            console.error(
                'Gagal memeriksa status online:',
                error
            );

            updateOnlineStatus(false);
        }
    }

    // ==========================================
    // TYPING INDICATOR
    // ==========================================

    function showTypingIndicator() {
        if (!typingIndicator) return;

        typingIndicator.hidden = false;

        if (typingText) {
            typingText.textContent =
                `${otherUserName} sedang mengetik...`;
        }

        scrollToBottom();
    }

    function hideTypingIndicator() {
        if (typingIndicator) {
            typingIndicator.hidden = true;
        }
    }

    async function publishTypingStatus(typing) {
        if (!channel) return;

        try {
            await channel.publish('typing', {
                userId: currentUserId,
                isTyping: typing,
            });
        } catch (error) {
            console.error(
                'Gagal mengirim status mengetik:',
                error
            );
        }
    }

    function startTyping() {
        if (!channel) return;

        if (!isTyping) {
            isTyping = true;
            publishTypingStatus(true);
        }

        if (!typingSendInterval) {
            typingSendInterval = setInterval(() => {
                if (isTyping) {
                    publishTypingStatus(true);
                }
            }, TYPING_INTERVAL);
        }

        clearTimeout(typingTimeout);

        typingTimeout = setTimeout(
            stopTyping,
            TYPING_TIMEOUT
        );
    }

    function stopTyping() {
        clearTimeout(typingTimeout);

        if (typingSendInterval) {
            clearInterval(typingSendInterval);
            typingSendInterval = null;
        }

        if (isTyping) {
            isTyping = false;
            publishTypingStatus(false);
        }
    }

    function handleTypingEvent(ablyMessage) {
        const data = ablyMessage.data;

        if (
            ablyMessage.clientId === `user-${currentUserId}`
        ) {
            return;
        }

        if (Number(data.userId) !== otherUserId) return;

        if (data.isTyping) {
            showTypingIndicator();
            clearTimeout(remoteTypingTimeout);

            remoteTypingTimeout = setTimeout(
                hideTypingIndicator,
                TYPING_TIMEOUT + 1000
            );
        } else {
            clearTimeout(remoteTypingTimeout);
            hideTypingIndicator();
        }
    }

    // ==========================================
    // STATUS PESAN
    // ==========================================

    function getMessageStatus(message) {
        if (message.read_at) return 'read';
        if (message.delivered_at) return 'delivered';

        return 'sent';
    }

    function createMessageStatus(status) {
        const element = document.createElement('span');

        element.className = 'chat-message-status';
        element.dataset.status = status;

        const labels = {
            sent: 'Terkirim',
            delivered: 'Diterima',
            read: 'Dibaca',
        };

        element.textContent =
            status === 'sent' ? '✓' : '✓✓';

        element.title = labels[status] || 'Terkirim';
        element.setAttribute('aria-label', element.title);

        return element;
    }

    function updateMessageStatus(messageId, status) {
        const row = messages.querySelector(
            `[data-message-id="${messageId}"]`
        );

        if (!row || !row.classList.contains('mine')) return;

        const statusElement = row.querySelector(
            '.chat-message-status'
        );

        if (!statusElement) return;

        const priority = {
            sent: 1,
            delivered: 2,
            read: 3,
        };

        const currentStatus =
            statusElement.dataset.status || 'sent';

        if (
            !priority[status] ||
            priority[status] <= priority[currentStatus]
        ) {
            return;
        }

        statusElement.dataset.status = status;

        statusElement.textContent =
            status === 'sent' ? '✓' : '✓✓';

        const labels = {
            sent: 'Terkirim',
            delivered: 'Diterima',
            read: 'Dibaca',
        };

        statusElement.title = labels[status];
        statusElement.setAttribute(
            'aria-label',
            labels[status]
        );
    }

    async function acknowledgeDelivery(message) {
        const messageId = Number(message.id);

        if (
            !messageId ||
            Number(message.sender_id) === currentUserId
        ) {
            return;
        }

        if (acknowledgedMessages.has(messageId)) return;

        acknowledgedMessages.add(messageId);

        try {
            await postToLaravel(
                `${messageUrl}/${messageId}/delivered`
            );
        } catch (error) {
            acknowledgedMessages.delete(messageId);

            console.error(
                'Gagal mengonfirmasi pesan diterima:',
                error
            );
        }
    }

    async function markMessagesAsRead() {
        try {
            await postToLaravel(readUrl);
        } catch (error) {
            console.error(
                'Gagal menandai pesan sudah dibaca:',
                error
            );
        }
    }

    function handleMessageStatus(ablyMessage) {
        const data = ablyMessage.data;

        if (
            data.message_id &&
            data.status === 'delivered'
        ) {
            updateMessageStatus(
                data.message_id,
                'delivered'
            );
        }
    }

    function handleMessagesRead(ablyMessage) {
        const data = ablyMessage.data;

        if (!Array.isArray(data.message_ids)) return;

        data.message_ids.forEach(id => {
            updateMessageStatus(id, 'read');
        });
    }

    // ==========================================
    // AVATAR DAN MEDIA PESAN
    // ==========================================

    function createOtherUserAvatar() {
        const avatar = document.createElement('div');

        avatar.className = 'chat-message-avatar';

        if (otherUserAvatar) {
            const image = document.createElement('img');

            image.src = otherUserAvatar;
            image.alt = otherUserName;
            image.loading = 'lazy';

            avatar.appendChild(image);
        } else {
            avatar.textContent = otherUserInitial;
        }

        return avatar;
    }

    function createMessageImage(message) {
        const wrapper = document.createElement('a');

        wrapper.href = message.media_url || '#';
        wrapper.target = '_blank';
        wrapper.rel = 'noopener';

        const image = document.createElement('img');

        image.src = message.media_url || '';
        image.alt = 'Foto yang dikirim';
        image.className = 'chat-message-image';
        image.loading = 'lazy';

        wrapper.appendChild(image);

        return wrapper;
    }

    function createMessageVideo(message) {
        const video = document.createElement('video');

        video.className = 'chat-message-video';
        video.controls = true;
        video.preload = 'metadata';
        video.playsInline = true;

        const source = document.createElement('source');

        source.src = message.media_url || '';
        source.type = message.media_mime || 'video/mp4';

        video.appendChild(source);

        video.appendChild(
            document.createTextNode(
                'Browser kamu tidak mendukung pemutaran video.'
            )
        );

        return video;
    }

    // ==========================================
    // MEMBUAT MENU AKSI PESAN
    // ==========================================

    function createMessageActions(message, isMine) {
        const actions = document.createElement('div');

        actions.className = 'chat-message-actions';

        const menuButton = document.createElement('button');

        menuButton.type = 'button';
        menuButton.className = 'chat-message-menu-button';
        menuButton.setAttribute('aria-label', 'Pilihan pesan');
        menuButton.setAttribute('aria-expanded', 'false');

        const icon = document.createElement('i');

        icon.className = 'fa-solid fa-ellipsis-vertical';

        menuButton.appendChild(icon);

        const dropdown = document.createElement('div');

        dropdown.className = 'chat-message-dropdown';
        dropdown.hidden = true;

        const deleteMe = document.createElement('button');

        deleteMe.type = 'button';
        deleteMe.className = 'chat-delete-for-me';
        deleteMe.dataset.messageId = message.id;

        deleteMe.innerHTML =
            '<i class="fa-regular fa-trash-can"></i> Hapus untuk saya';

        dropdown.appendChild(deleteMe);

        if (isMine) {
            const deleteEveryone = document.createElement('button');

            deleteEveryone.type = 'button';
            deleteEveryone.className = 'chat-delete-for-everyone';
            deleteEveryone.dataset.messageId = message.id;

            deleteEveryone.innerHTML =
                '<i class="fa-solid fa-trash"></i> Hapus untuk semua orang';

            dropdown.appendChild(deleteEveryone);
        }

        actions.append(menuButton, dropdown);

        return actions;
    }

    // ==========================================
    // MENAMPILKAN PESAN
    // ==========================================

    function addMessage(message) {
        // Mencegah pesan yang sama ditampilkan dua kali
        if (
            message.id &&
            messages.querySelector(
                `[data-message-id="${message.id}"]`
            )
        ) {
            return;
        }

        document.getElementById('chatEmptyState')?.remove();

        const isMine =
            Number(message.sender_id) === currentUserId;

        const row = document.createElement('div');

        row.className = isMine
            ? 'chat-message mine'
            : 'chat-message theirs';

        if (message.id) {
            row.dataset.messageId = message.id;
        }

        row.dataset.senderId = message.sender_id;

        if (!isMine) {
            row.appendChild(createOtherUserAvatar());
        }

        // Wrapper utama: bubble dan metadata
        const content = document.createElement('div');

        content.className = 'chat-message-content';

        // ======================================
        // BUBBLE PESAN
        // ======================================

        const bubble = document.createElement('div');

        bubble.className = 'chat-bubble';

        // Gambar
        if (
            message.type === 'image' &&
            message.media_url
        ) {
            bubble.appendChild(
                createMessageImage(message)
            );
        }

        // Video
        else if (
            message.type === 'video' &&
            message.media_url
        ) {
            bubble.appendChild(
                createMessageVideo(message)
            );
        }

        // Lokasi
        else if (message.type === 'location') {
            const link = document.createElement('a');

            link.className = 'chat-location-card';

            link.href =
                `https://www.google.com/maps?q=${message.location_lat},${message.location_lng}`;

            link.target = '_blank';
            link.rel = 'noopener';

            const icon = document.createElement('i');

            icon.className = 'fa-solid fa-location-dot';

            const label = document.createElement('span');

            label.textContent =
                message.location_name || 'Lokasi yang dibagikan';

            const small = document.createElement('small');

            small.textContent =
                'Lihat lokasi di Google Maps ↗';

            label.appendChild(small);
            link.append(icon, label);

            bubble.appendChild(link);
        }

        // Isi teks atau caption
        if (message.body) {
            const caption = document.createElement('p');

            caption.textContent = message.body;

            bubble.appendChild(caption);
        }

        // ======================================
        // METADATA DI LUAR BUBBLE
        // ======================================

        const meta = document.createElement('div');

        meta.className = 'chat-message-meta';

        // Jam pesan
        const time = document.createElement('time');

        time.className = 'chat-message-time';
        time.textContent = formatMessageTime(
            message.created_at
        );

        meta.appendChild(time);

        // Status centang hanya untuk pesan sendiri
        if (isMine) {
            meta.appendChild(
                createMessageStatus(
                    getMessageStatus(message)
                )
            );
        }

        // Menu tiga titik setelah jam dan status
        if (message.id) {
            meta.appendChild(
                createMessageActions(message, isMine)
            );
        }

        // Urutan wajib:
        // Bubble -> Jam -> Centang -> Tiga titik
        content.append(bubble, meta);

        row.appendChild(content);

        messages.appendChild(row);

        scrollToBottom();
    }

    // ==========================================
    // KONEKSI ABLY
    // ==========================================

    async function connectAbly() {
        if (!tokenUrl || !conversationId) {
            showError(
                'Informasi koneksi chat belum lengkap.'
            );

            return;
        }

        try {
            realtime = new Realtime({
                authUrl: tokenUrl,
                authMethod: 'GET',
            });

            channel = realtime.channels.get(
                `loaklokal:conversation:${conversationId}`
            );

            channel.subscribe('message', ablyMessage => {
                const message = ablyMessage.data;

                addMessage(message);

                if (
                    Number(message.sender_id) !== currentUserId
                ) {
                    acknowledgeDelivery(message);
                    markMessagesAsRead();
                }
            });

            channel.subscribe(
                'message-status',
                handleMessageStatus
            );

            channel.subscribe(
                'messages-read',
                handleMessagesRead
            );

            channel.subscribe(
                'message-deleted-everyone',
                handleMessageDeleted
            );

            channel.subscribe(
                'typing',
                handleTypingEvent
            );

            await channel.presence.subscribe(
                ['enter', 'leave', 'update'],
                refreshOnlineStatus
            );

            await channel.presence.enter({
                userId: currentUserId,
            });

            await refreshOnlineStatus();
            await markMessagesAsRead();

            realtime.connection.on('failed', () => {
                updateOnlineStatus(false);
                hideTypingIndicator();

                showError(
                    'Koneksi chat realtime gagal. Silakan muat ulang halaman.'
                );
            });

            realtime.connection.on('disconnected', () => {
                updateOnlineStatus(false);
                hideTypingIndicator();
            });

            realtime.connection.on('connected', async () => {
                showError('');

                try {
                    await channel.presence.enter({
                        userId: currentUserId,
                    });

                    await refreshOnlineStatus();
                    await markMessagesAsRead();
                } catch (error) {
                    console.error(
                        'Gagal bergabung kembali:',
                        error
                    );
                }
            });
        } catch (error) {
            console.error(
                'Ably connection error:',
                error
            );

            updateOnlineStatus(false);
            hideTypingIndicator();

            showError(
                'Tidak dapat menghubungkan chat realtime.'
            );
        }
    }

    // ==========================================
    // INPUT PENGGUNA
    // ==========================================

    input.addEventListener('input', () => {
        if (input.value.trim().length > 0) {
            startTyping();
        } else {
            stopTyping();
        }
    });

    input.addEventListener('blur', stopTyping);

    // ==========================================
    // KIRIM PESAN TEKS, GAMBAR, ATAU VIDEO
    // ==========================================

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const body = input.value.trim();

        if (!body && !selectedImage && !selectedVideo) {
            return;
        }

        if (selectedImage && selectedVideo) {
            showError(
                'Kirim gambar atau video secara terpisah.'
            );

            return;
        }

        showError('');
        stopTyping();

        sendButton.disabled = true;

        if (imageButton) {
            imageButton.disabled = true;
        }

        if (videoButton) {
            videoButton.disabled = true;
        }

        try {
            const formData = new FormData();

            const type = selectedVideo
                ? 'video'
                : selectedImage
                    ? 'image'
                    : 'text';

            formData.append('type', type);

            if (body) {
                formData.append('body', body);
            }

            if (selectedImage) {
                formData.append('image', selectedImage);
            }

            if (selectedVideo) {
                formData.append('video', selectedVideo);
            }

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                const validationErrors = result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : '';

                throw new Error(
                    validationErrors ||
                    result.message ||
                    'Pesan gagal dikirim. Silakan coba lagi.'
                );
            }

            // Tampilkan pesan menggunakan struktur yang sama
            // dengan pesan yang masuk melalui Ably.
            addMessage(result.message);

            input.value = '';

            clearImagePreview();
            clearVideoPreview();

            input.focus();
        } catch (error) {
            console.error(
                'Gagal mengirim pesan:',
                error
            );

            showError(
                error.message ||
                'Terjadi kesalahan saat mengirim pesan.'
            );
        } finally {
            sendButton.disabled = false;

            if (imageButton) {
                imageButton.disabled = false;
            }

            if (videoButton) {
                videoButton.disabled = false;
            }
        }
    });

    // ==========================================
    // INISIALISASI
    // ==========================================

    scrollToBottom();

    connectAbly();

    window.addEventListener('pagehide', () => {
        stopTyping();

        clearTimeout(remoteTypingTimeout);
        clearTimeout(typingTimeout);

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
        }

        if (videoPreviewObjectUrl) {
            URL.revokeObjectURL(videoPreviewObjectUrl);
        }

        if (realtime) {
            realtime.close();
        }
    });
});
