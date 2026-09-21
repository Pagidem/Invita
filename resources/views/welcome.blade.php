<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Invita</title>

        <!-- Favicon y iconos -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="alternate icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/favicon.svg">
        <meta name="theme-color" content="#d7b77a">

        <!-- Styles / Scripts -->
       
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
