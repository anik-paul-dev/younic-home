<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Younic Home</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex items-center justify-center min-h-screen py-10">
    
    <div class="w-full max-w-lg p-8 glass-card">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-teal-400 tracking-wider mb-2">Younic <span class="text-amber-500">Home</span></h1>
            <p class="text-slate-400">Create your account to request a seat.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/50 text-red-400 text-sm rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-field" required placeholder="John Doe">
            </div>
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-field" required placeholder="john@example.com">
            </div>
            <div>
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="input-field" required placeholder="01XXXXXXXXX">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="input-field" required placeholder="••••••••">
                </div>
                <div>
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="input-field" required placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="w-full btn-primary py-3 text-lg mt-4">Create Account</button>
        </form>

        <p class="text-center text-slate-400 mt-6 text-sm">
            Already have an account? <a href="{{ route('login') }}" class="text-teal-400 hover:text-teal-300 font-medium">Login here</a>
        </p>
    </div>

</body>
</html>
