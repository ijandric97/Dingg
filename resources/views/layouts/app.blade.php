<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.head')
</head>
<body class="bg-primary">
    <div id="app">
        @include('includes.navbar')
        <main class="py-4 bg-white">
            @include('includes.status-alert')
            @yield('content')
        </main>
        @include('includes.footer')
    </div>
    @stack('scripts')
</body>
</html>
