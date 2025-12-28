<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WP License Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl text-center">
            <div class="flex justify-center mb-8">
                <svg class="h-16 w-16 text-blue-600 dark:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                WP License Manager
            </h1>
            
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                This is an in-house license management system for our WordPress plugins and themes.
            </p>
            
            <div class="flex justify-center">
                @auth
                    <a href="/admin" class="rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500">
                        Go to Dashboard
                    </a>
                @else
                    <a href="/admin/login" class="rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</body>
</html>
