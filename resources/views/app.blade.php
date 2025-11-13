<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- Importante para Inertia: Token CSRF para seguridad en peticiones POST/PUT/DELETE -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts de Vite (Esto carga tu CSS de Tailwind compilado y la aplicación Vue) -->
        @vite('resources/js/app.js')
        
        <!-- Directiva de Inertia: Carga el componente Vue de la página actual -->
        @inertiaHead
    </head>
    <!-- Establece la fuente Inter y un fondo gris suave para la aplicación -->
    <body class="font-sans antialiased bg-gray-100">
        @inertia
    </body>
</html>