
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('chatSearchInput');
    const tombolHapus = document.getElementById('chatSearchClear');
    const daftarChat = document.getElementById('chatList');
    const pesanKosong = document.getElementById('chatEmpty');

    if (!input || !daftarChat) {
        return;
    }

    const itemChat = Array.from(
        daftarChat.querySelectorAll('.chat-item')
    );

    function normalisasi(teks) {
        return (teks || '')
            .trim()
            .toLocaleLowerCase('id-ID');
    }

    function filterChat() {
        const kataKunci = normalisasi(input.value);
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

    filterChat();
});
