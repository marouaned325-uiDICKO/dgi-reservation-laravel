<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card-dgi { background: #1A8969; }
        .card-dgi label { color: #ffffff !important; }
        .card-dgi a { color: #d9f0e3 !important; }
        .card-dgi .text-sm.text-gray-600,
        .card-dgi p,
        .card-dgi span { color: #d9f0e3 !important; }
        .card-dgi input[type="checkbox"] { accent-color: #1A8969; }

        /* Messages lisibles sur le fond vert */
        .card-dgi .text-red-600 { color: #ffe08a !important; font-weight: 600; }
        .card-dgi .text-green-600 { color: #ffffff !important; font-weight: 600; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col items-center pt-6 sm:justify-center sm:pt-0 bg-gray-100">
        <div>
            <a href="/">
                <x-application-logo />
            </a>
        </div>

        <div class="card-dgi w-full sm:max-w-md mt-6 px-6 py-6 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>