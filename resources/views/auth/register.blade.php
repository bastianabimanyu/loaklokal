
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi - LoakLokal</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
    >

    @vite(['resources/css/register.css'])
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="container">
        <div class="wrap-registrasi">

        <div class="header-top">
            <a href="{{ route('login') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>

            <h1>REGISTRASI</h1>
        </div>

        <div class="line"></div>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            action="{{ route('register.process') }}"
            method="POST"
            enctype="multipart/form-data"
            id="registerForm"
        >
            @csrf

            <div class="form-group">
                <label for="reg-email">E-mail</label>
                <input
                    type="email"
                    id="reg-email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email aktif"
                    autocomplete="email"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-phone">Nomor Telepon</label>
                <input
                    type="tel"
                    id="reg-phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="Contoh: 081234567890"
                    autocomplete="tel"
                    maxlength="25"
                    pattern="[0-9+\-() ]+"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-fullname">Nama Lengkap</label>
                <input
                    type="text"
                    id="reg-fullname"
                    name="fullname"
                    value="{{ old('fullname') }}"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-password">Password</label>
                <input
                    type="password"
                    id="reg-password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-password-confirmation">
                    Konfirmasi Password
                </label>
                <input
                    type="password"
                    id="reg-password-confirmation"
                    name="password_confirmation"
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-ktp">Foto KTP</label>
            </div>

            <div class="upload-section">
                <div class="upload-box" title="Klik untuk upload KTP">
                    <img
                        src="{{ asset('assets/images/Upload.png') }}"
                        alt="Ikon upload"
                        id="uploadIcon"
                        class="upload-icon-img"
                    >

                    <img
                        src=""
                        alt="Preview foto KTP"
                        id="ktpPreview"
                        class="preview-img"
                    >

                    <input
                        type="file"
                        id="reg-ktp"
                        name="ktp_image"
                        accept="image/jpeg,image/png,image/webp"
                        aria-label="Pilih foto KTP"
                        required
                    >
                </div>

                <div class="upload-info">
                    <button
                        type="button"
                        class="btn-upload"
                        id="btnUpload"
                    >
                        Upload KTP
                    </button>

                    <p id="uploadStatus" class="status-text">
                        JPG, PNG, atau WEBP. Maksimal 2 MB.
                    </p>
                </div>
            </div>

            <div class="submit-container">
                <button
                    type="submit"
                    class="btn-submit"
                    id="btnSubmit"
                    disabled
                >
                    Daftar Akun
                </button>
            </div>
        </form>
    </div>

    <script>
        const ktpInput = document.getElementById('reg-ktp');
        const ktpPreview = document.getElementById('ktpPreview');
        const uploadIcon = document.getElementById('uploadIcon');
        const uploadStatus = document.getElementById('uploadStatus');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnUpload = document.getElementById('btnUpload');
        const registerForm = document.getElementById('registerForm');

        const maxSize = 2 * 1024 * 1024;
        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        let imageIsClear = false;
        let checkingImage = false;

        btnUpload.addEventListener('click', function () {
            ktpInput.click();
        });

        ktpInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            imageIsClear = false;
            btnSubmit.disabled = true;
            ktpPreview.style.display = 'none';
            uploadIcon.style.display = 'block';

            if (!file) return;

            if (!allowedTypes.includes(file.type)) {
                showError('Gunakan file JPG, PNG, atau WEBP.');
                ktpInput.value = '';
                return;
            }

            if (file.size > maxSize) {
                showError('Ukuran foto KTP maksimal 2 MB.');
                ktpInput.value = '';
                return;
            }

            checkingImage = true;
            uploadStatus.textContent = 'Mengecek ketajaman gambar...';
            uploadStatus.className = 'status-text';

            const reader = new FileReader();

            reader.onload = function (e) {
                const img = new Image();

                img.onload = function () {
                    try {
                        checkBlur(img, function (isBlurry) {
                            checkingImage = false;

                            if (isBlurry) {
                                showError(
                                    'KTP mungkin buram. Silakan upload gambar yang lebih jelas.'
                                );

                                ktpInput.value = '';
                                return;
                            }

                            ktpPreview.src = e.target.result;
                            ktpPreview.style.display = 'block';
                            uploadIcon.style.display = 'none';

                            uploadStatus.textContent =
                                'Pemeriksaan ketajaman awal berhasil.';

                            uploadStatus.className =
                                'status-text status-success';

                            imageIsClear = true;
                            btnSubmit.disabled = false;
                        });
                    } catch (error) {
                        checkingImage = false;
                        showError('Gambar tidak dapat diperiksa.');
                        ktpInput.value = '';
                    }
                };

                img.onerror = function () {
                    checkingImage = false;
                    showError('File gambar tidak dapat dibaca.');
                    ktpInput.value = '';
                };

                img.src = e.target.result;
            };

            reader.onerror = function () {
                checkingImage = false;
                showError('Gagal membaca file gambar.');
                ktpInput.value = '';
            };

            reader.readAsDataURL(file);
        });

        function showError(message) {
            uploadStatus.textContent = message;
            uploadStatus.className = 'status-text status-error';
            imageIsClear = false;
            btnSubmit.disabled = true;
            ktpPreview.style.display = 'none';
            uploadIcon.style.display = 'block';
        }

        function checkBlur(img, callback) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', {
                willReadFrequently: true
            });

            const scale = Math.min(
                300 / img.width,
                300 / img.height,
                1
            );

            const width = Math.max(3, Math.floor(img.width * scale));
            const height = Math.max(3, Math.floor(img.height * scale));

            canvas.width = width;
            canvas.height = height;

            ctx.drawImage(img, 0, 0, width, height);

            const pixels = ctx.getImageData(
                0, 0, width, height
            ).data;

            const grayscale = new Float32Array(width * height);

            for (let i = 0, j = 0; i < pixels.length; i += 4, j++) {
                grayscale[j] =
                    pixels[i] * 0.299 +
                    pixels[i + 1] * 0.587 +
                    pixels[i + 2] * 0.114;
            }

            let sum = 0;
            let sumSq = 0;
            let count = 0;

            for (let y = 1; y < height - 1; y++) {
                for (let x = 1; x < width - 1; x++) {
                    const p =
                        grayscale[(y - 1) * width + x] +
                        grayscale[(y + 1) * width + x] +
                        grayscale[y * width + x - 1] +
                        grayscale[y * width + x + 1] -
                        4 * grayscale[y * width + x];

                    sum += p;
                    sumSq += p * p;
                    count++;
                }
            }

            if (count === 0) {
                callback(true);
                return;
            }

            const mean = sum / count;
            const variance = (sumSq / count) - (mean * mean);

            // Ambang awal; perlu diuji dengan foto KTP nyata.
            callback(variance < 150);
        }

        registerForm.addEventListener('submit', function (event) {
            if (
                !imageIsClear ||
                checkingImage ||
                !ktpInput.files.length
            ) {
                event.preventDefault();
                showError(
                    'Pilih foto KTP yang lolos pemeriksaan terlebih dahulu.'
                );
            }
        });
    </script>
</body>
</html>
