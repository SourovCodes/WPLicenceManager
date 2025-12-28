<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Forbidden</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl text-center">
            <div class="flex justify-center mb-8">
                <svg class="h-24 w-24 text-red-400 dark:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">
                403
            </h1>
            
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                Forbidden
            </p>
            
            <p class="text-base text-gray-500 dark:text-gray-500 mb-8">
                {{ $exception->getMessage() ?: "You don't have permission to access this resource." }}
            </p>
            
            <div class="flex justify-center gap-4">
                <a href="javascript:history.back()" class="text-base font-semibold text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                    ← Go back
                </a>
                <a href="/" class="rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500">
                    Go home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
