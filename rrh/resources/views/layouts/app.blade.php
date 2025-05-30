<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @routes
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Rwanda Resilience Hub')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @yield('styles')
    
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <a href="{{ route('dashboard') }}" class="logo">
                <div class="logo-icon"></div>
                <div class="logo-text">
                    Rwanda<br>Resilience Hub
                </div>
            </a>
            
            <nav>
                <ul class="nav-menu">
                    @auth
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link">HOME</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('predictions.index') }}" class="nav-link">ALERT</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('predictions.index') }}" class="nav-link">Flooding</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sensors.index') }}" class="nav-link">Data Collection</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link">Resources</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link">Report</a>
                        </li>
                    @endauth
                </ul>
            </nav>
            
            <div class="user-menu">
                @auth
                    <button class="user-button">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </button>
                @else
                    <a href="{{ route('login') }}" class="btn">Login</a>
                @endauth
            </div>
        </div>
    </header>
    
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <footer>
        <div>
            <p>Rwanda Resilience Hub is technology-driven flood early warning and disaster resilience system to enhance Rwanda's flood preparedness and response capabilities.</p>
            <div>
                <p>Contact us via:</p>
                <p>📞 0782367835 | ✉️ rrh@gmail.com</p>
                <p>You can visit us on our website www.rrh.com</p>
            </div>
        </div>
    </footer>
    
    @yield('scripts')
</body>
</html>