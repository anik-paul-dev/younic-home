<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Younic Home</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md p-8 glass-card">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-teal-400 tracking-wider mb-2">Younic <span class="text-amber-500">Home</span></h1>
            <p class="text-slate-400">Welcome back! Please login to your account.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/50 text-red-400 text-sm rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-field" required placeholder="Enter your email">
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="input-field" required placeholder="Enter your password">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-slate-300">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-slate-700 bg-slate-900/50 text-teal-500 focus:ring-teal-500">
                    Remember me
                </label>
            </div>
            <button type="submit" class="w-full btn-primary py-3 text-lg mt-4">Login</button>
        </form>

        <p class="text-center text-slate-400 mt-6 text-sm">
            Don't have an account? <a href="{{ route('register') }}" class="text-teal-400 hover:text-teal-300 font-medium">Register here</a>
        </p>
    </div>

</body>
</html>
