@props(['title' => 'Finance Tracker'])
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Finance</title>

    {{-- Applies the saved theme before the page is drawn, so there is no flash --}}
    <script>
        try {
            document.documentElement.dataset.theme =
                localStorage.getItem('finance-theme') === 'light' ? 'light' : 'dark';
        } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-bg">
        <x-sidebar />
        {{ $slot }}
    </div>

    <x-auth-modal />
    <x-reminder-popup />
    <x-toast />
</body>
</html>