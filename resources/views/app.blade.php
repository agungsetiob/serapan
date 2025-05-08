<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
    <script>
        console.log(
            "%cSelamat Datang di EPENDI!\n\n%cElektronik Pengajuan Nota Dinas, merupakan sebuah project yang bertujuan untuk belajar lebih dalam tentang Laravel 12.\n\nVersi EPENDI ini menggunakan vue.js, terdapat versi lain yang menggunakan blade templating Laravel.\n\nUntuk informasi lebih lanjut silahkan ke: https://agungsetio.com",
            "font-size: 25px;",
            "font-size: 12px;"
        );
    </script>

</html>
