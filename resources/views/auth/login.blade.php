<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · HRIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="text-center mb-2">
                <h1 class="text-3xl font-bold text-primary">HRIS</h1>
                <p class="text-sm text-base-content/60">Sign in to your account</p>
            </div>

            <x-ui.flash />

            <form method="POST" action="{{ route('login') }}" class="space-y-2">
                @csrf
                <x-form.input label="Email" name="email" type="email" required autofocus />
                <x-form.input label="Password" name="password" type="password" required />

                <label class="label cursor-pointer justify-start gap-2 mt-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                    <span class="label-text">Remember me</span>
                </label>

                <button type="submit" class="btn btn-primary w-full mt-2">Sign In</button>
            </form>

            <div class="text-xs text-center text-base-content/50 mt-4">
                Demo: admin@hris.local / password
            </div>
        </div>
    </div>
</body>
</html>
