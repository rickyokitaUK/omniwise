<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="{{ asset('js/gauge.min.js') }}"></script>

    {{-- Include the compiled Vite CSS/JS --}}
      @vite('resources/js/app.js')
</head>
<body>
    {{-- Your Vue app will be injected here --}}
    @yield('content')
</body>
</html>
