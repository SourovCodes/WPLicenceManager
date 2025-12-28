<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Session Expired</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl text-center">
            <div class="flex justify-center mb-8">
                <svg class="h-24 w-24 text-blue-400 dark:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">
                419
            </h1>
            
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                Session Expired
            </p>
            
            <p class="text-base text-gray-500 dark:text-gray-500 mb-8">
                Your session has expired. Please refresh the page and try again.
            </p>
            
            <div class="flex justify-center gap-4">
                <a href="javascript:history.back()" class="text-base font-semibold text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                    ← Go back
                </a>
                <a href="javascript:location.reload()" class="rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500">
                    Refresh page
                </a>
            </div>
        </div>
    </div>
</body>
</html>
