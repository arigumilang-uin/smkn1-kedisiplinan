<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMKN 1 Siak Lubuk Dalam</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: '#2563eb', // Blue 600
                        dark: '#0f172a',    // Slate 900
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Pattern Background Halus */
        .bg-pattern {
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-white text-slate-800 h-screen overflow-hidden">

    <div class="flex h-full w-full">
        
        <div class="hidden lg:flex w-1/2 bg-slate-900 relative items-center justify-center overflow-hidden">
            
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-slate-900 opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
            <div class="absolute top-1/2 -right-24 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s"></div>
            <div class="absolute -bottom-24 left-1/2 w-80 h-80 bg-teal-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 4s"></div>

            <div class="relative z-10 text-center px-12">
                <div class="mb-6 inline-flex p-4 bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L12 2 4 7.5V20a2 2 0 0 0 2 2z"/><path d="M12 18v-7"/><path d="M9 13v2"/><path d="M15 13v2"/></svg>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4 tracking-tight">Sistem Kedisiplinan</h2>
                <p class="text-blue-100 text-lg leading-relaxed">
                    SMKN 1 Siak Lubuk Dalam.<br>
                    Mewujudkan lingkungan sekolah yang tertib, disiplin, dan berkarakter.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white bg-pattern relative">
            
            <div class="w-full max-w-md animate-fade-in">
                
                <div class="text-center lg:text-left mb-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-4 border border-blue-100">
                        👋 Selamat Datang
                    </div>
                    <h1 class="text-3xl font-bold text-slate-900 mb-2">Login Akun</h1>
                    <p class="text-slate-500">Silakan masukkan kredensial Anda untuk melanjutkan.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <div class="text-sm text-rose-700">
                            <strong>Login Gagal!</strong>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username / Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus 
                                class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium"
                                placeholder="Contoh: 12345 atau admin@sekolah.id">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <input type="password" id="password" name="password" required 
                                class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transform transition-all hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600">
                        Masuk ke Sistem
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 ml-1 -mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>

                </form>

                <p class="mt-8 text-center text-sm text-slate-400">
                    &copy; {{ date('Y') }} SMKN 1 Siak Lubuk Dalam v1.0
                </p>
            </div>
        </div>
    </div>

</body>
</html>