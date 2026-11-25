<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'SaludCerca')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style> .selmap { height: 300px; border-radius: .5rem; } </style>
</head>
<body class="bg-light">
@include('partials.navbar')
<div class="container pb-4">
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('contenido')
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')
</body>
</html>
