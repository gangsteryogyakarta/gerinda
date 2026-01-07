<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gerindra Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-gerindra-primary { background-color: #B70000; }
        .text-gerindra-primary { color: #B70000; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            outline: none;
        }
        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="h-screen w-full flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat relative" style="background-image: url('{{ asset('img/bg.jpg') }}');">
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black/40 z-0"></div>

    <div class="relative z-10 max-w-md w-full glass-panel rounded-2xl overflow-hidden transform transition-all hover:scale-[1.01] duration-300">
        <!-- Header Section -->
        <div class="p-8 pb-6 border-b border-white/10 flex flex-col items-center">
            <img src="{{ asset('img/logo-gerindra.png') }}" alt="Logo Gerindra" class="h-28 w-auto drop-shadow-lg mb-4">
            <h2 class="text-2xl font-bold text-white text-center tracking-wide text-shadow">Gerindra Event</h2>
            <p class="text-gray-200 text-sm mt-1 text-center font-light">Sistem Manajemen Event Partai Gerindra</p>
        </div>

        <!-- Form Section -->
        <div class="p-8 pt-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-500/20 border-l-4 border-red-500 p-4 rounded-r backdrop-blur-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-100 font-medium">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2 shadow-sm">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-300 group-focus-within:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" required 
                            class="block w-full pl-10 pr-3 py-2.5 glass-input rounded-lg transition-all"
                            placeholder="admin@gerindra.id" value="{{ old('email') }}">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2 shadow-sm">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-300 group-focus-within:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="block w-full pl-10 pr-3 py-2.5 glass-input rounded-lg transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded bg-white/80">
                        <label for="remember" class="ml-2 block text-sm text-white drop-shadow-sm pointer-events-none">
                            Ingat Saya
                        </label>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-lg text-sm font-bold text-white bg-gerindra-primary hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 transform hover:-translate-y-0.5">
                    Masuk Dashboard
                </button>
            </form>

            <div class="mt-4 p-3 bg-blue-500/20 border border-blue-400/30 rounded-lg backdrop-blur-sm">
                <p class="text-xs text-blue-100 text-center">
                    <span class="font-bold">Demo Acount:</span><br>
                    User: admin@gerindra.id | Pass: password
                </p>
            </div>
        </div>
        
        <div class="bg-black/20 px-8 py-4 border-t border-white/10">
            <p class="text-xs text-center text-white/70">
                &copy; {{ date('Y') }} Partai Gerindra DI Yogyakarta. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
