
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LoakLokal</title>

    @vite(['resources/css/login.css'])
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="container">
        <div class="header-brand">
            <img
                src="{{ asset('assets/images/LOGO.png') }}"
                alt="Logo LoakLokal"
            >

            <h1>LOAKLOKAL</h1>
            <p>Pasar Loak &amp; Hibah Lokal</p>
        </div>

        <div class="card">
            <div class="nav-tabs">
                <a href="{{ route('login') }}" class="active">Login</a>
                <a href="{{ route('register') }}">Registrasi</a>
            </div>

            @if (session('success'))
                <div class="status-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="login-email">E-mail</label>
                    <input
                        type="email"
                        id="login-email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input
                        type="password"
                        id="login-password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Masuk Akun
                </button>
            </form>
        </div>
    </div>
</body>
</html>
