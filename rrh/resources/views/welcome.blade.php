{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Rwanda Resilience Hub') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="w-12 h-12 bg-amber-800 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Rwanda</h1>
                        <p class="text-sm text-gray-600">Resilience Hub</p>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="flex items-center space-x-1 text-gray-700 hover:text-amber-800 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span>HOME</span>
                    </a>
                    <a href="#alerts" class="flex items-center space-x-1 text-gray-700 hover:text-amber-800 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>ALERT</span>
                    </a>
                    <a href="#contact" class="flex items-center space-x-1 text-gray-700 hover:text-amber-800 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        <span>Contact</span>
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-amber-800 text-white px-6 py-2 rounded-lg hover:bg-amber-900 transition-colors">
                            Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="bg-amber-800 text-white px-6 py-2 rounded-lg hover:bg-amber-900 transition-colors">
                                Login
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Rwanda Resilience Hub</h1>
            <p class="text-xl text-gray-600 mb-8">Advanced flood prediction and disaster resilience system</p>
        </div>

        <!-- News Section -->
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <!-- News Item 1 -->
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="w-full h-48 bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                    <div class="text-white text-center">
                        <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm">Flood Image Placeholder</p>
                    </div>
                </div>
                <div class="p-6">
                    <time class="text-sm text-gray-500 mb-2 block">3 May 2023</time>
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        At least 130 people have died after floods and landslides hit Rwanda's northern and western provinces, authorities say
                    </h2>
                    <p class="text-gray-600 text-sm">
                        Heavy rainfall has caused devastating floods and landslides across multiple provinces in Rwanda, highlighting the need for better early warning systems.
                    </p>
                </div>
            </article>

            <!-- News Item 2 -->
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="w-full h-48 bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center">
                    <div class="text-white text-center">
                        <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm">Flooding Image Placeholder</p>
                    </div>
                </div>
                <div class="p-6">
                    <time class="text-sm text-gray-500 mb-2 block">Recent</time>
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        Rwandan authorities say at least 129 people have been killed after heavy rain caused flooding in western, northern and southern provinces
                    </h2>
                    <p class="text-gray-600 text-sm">
                        Continued severe weather patterns emphasize the critical importance of flood prediction and community preparedness systems.
                    </p>
                </div>
            </article>
        </div>

        <!-- Get Started Button -->
        <div class="text-center mb-12">
            @auth
                <a href="{{ url('/dashboard') }}" 
                   class="inline-block bg-amber-800 text-white px-8 py-3 rounded-full text-lg font-medium hover:bg-amber-900 transition-colors shadow-lg">
                    Go to Dashboard
                </a>
            @else
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" 
                       class="inline-block bg-amber-800 text-white px-8 py-3 rounded-full text-lg font-medium hover:bg-amber-900 transition-colors shadow-lg">
                        Get started
                    </a>
                @endif
            @endguest
        </div>

        <!-- Features Section -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Early Warning</h3>
                <p class="text-gray-600">Advanced flood prediction using real-time data and machine learning algorithms.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Data Collection</h3>
                <p class="text-gray-600">Comprehensive environmental monitoring with IoT sensors and satellite data.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 1a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 3a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Reporting</h3>
                <p class="text-gray-600">Detailed analytics and reports for informed decision-making and response planning.</p>
            </div>
        </div>
    </main>

    <!-- Contact Section -->
    <section id="contact" class="bg-amber-800 text-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">Talk to us</h2>
            
            <form method="POST" action="{{ route('contact.store') }}" class="max-w-4xl">
                @csrf
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium mb-2">Full Name</label>
                        <input type="text" 
                               id="full_name"
                               name="full_name" 
                               placeholder="Full name" 
                               required
                               value="{{ old('full_name') }}"
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg placeholder-white/70 text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                        @error('full_name')
                            <p class="mt-1 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" 
                               id="email"
                               name="email" 
                               placeholder="Your Gmail" 
                               required
                               value="{{ old('email') }}"
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg placeholder-white/70 text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                        @error('email')
                            <p class="mt-1 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="flex gap-6">
                    <div class="flex-1">
                        <label for="message" class="block text-sm font-medium mb-2">Message</label>
                        <textarea name="message" 
                                  id="message"
                                  rows="4" 
                                  placeholder="Your Comment" 
                                  required
                                  class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg placeholder-white/70 text-white focus:outline-none focus:ring-2 focus:ring-white/50 resize-none">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex-shrink-0 flex items-end">
                        <button type="submit" 
                                class="bg-white text-amber-800 px-8 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors shadow-lg">
                            Send
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-400">
                    Rwanda Resilience Hub is a technology-driven flood early warning and disaster resilience system to enhance Rwanda's flood preparedness and response capabilities.
                </p>
                <div class="mt-4 flex justify-center space-x-6">
                    <span class="text-gray-400">📞 0782367835</span>
                    <span class="text-gray-400">✉️ rrh@gmail.com</span>
                    <span class="text-gray-400">🌐 www.rrh.com</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('success-message').style.display = 'none';
            }, 5000);
        </script>
    @endif

    @if(session('error'))
        <div id="error-message" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('error-message').style.display = 'none';
            }, 5000);
        </script>
    @endif
</body>
</html>