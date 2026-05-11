<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Catalogue</title>
    <link rel="stylesheet" href="/L/course/public/css/main.css">
    <script src="/L/course/public/js/knockout.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header class="border-b px-8 h-16 flex items-center" style="background-color: #cbe3c5; border-color: #6a8a63;">
        <nav class="w-full flex items-center justify-between">
            <a href="/" class="font-bold text-lg">Server Catalog</a>

            <ul class="flex gap-8 list-none">
                <li><a href="/" class="no-underline text-gray-700 hover:text-green-700">Home</a></li>
                <li><a href="/servers" class="no-underline text-gray-700 hover:text-green-700">Servers</a></li>
                <li><a href="/contact" class="no-underline text-gray-700 hover:text-green-700">Contact</a></li>
            </ul>

            <div class="flex items-center gap-4">
                <a href="/login" class="no-underline text-gray-700 hover:text-green-700">Login</a> 
                <a href="/register" class="no-underline text-gray-700 hover:text-green-700">Register</a> 
                <button id="cart-btn" class="text-white px-4 py-2 rounded-lg" style="background-color: #308020;">Cart</button>
            </div>
        </nav>
    </header>