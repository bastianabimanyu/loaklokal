<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'LoakLokal')</title>

    @stack('styles')
</head>
<body>
    <main>
        @yield('content')
    </main>

    <x-navbar />

    @stack('scripts')
</body>
</html>