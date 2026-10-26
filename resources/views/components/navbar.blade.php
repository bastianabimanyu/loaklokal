
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Navbar</title>

    @vite(['resources/css/navbar.css'])

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
    >
</head>

<body>
    <nav class="bottom-navigation">
        <a
            href="{{ route('beranda') }}"
            class="bottom-nav-item {{ request()->routeIs('beranda') ? 'active' : '' }}"
            aria-label="Beranda"
            title="Beranda"
        >
            <i class="fa-solid fa-house"></i>
        </a>

        <a
            href="{{ route('filter') }}"
            class="bottom-nav-item {{ request()->routeIs('filter') ? 'active' : '' }}"
            aria-label="Cari"
            title="Cari"
        >
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a
            href="{{ route('chat.index') }}"
            class="bottom-nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}"
            aria-label="Chat"
            title="Chat"
        >
            <i class="fa-solid fa-envelope"></i>
        </a>

        <a
            href="{{ url('/profil') }}"
            class="bottom-nav-item {{ request()->is('profil*') ? 'active' : '' }}"
            aria-label="Profil"
            title="Profil Saya"
        >
            <i class="fa-solid fa-user"></i>
        </a>
    </nav>
</body>

</html>
