<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $passwordSetup ? 'Create your password' : 'Verify your email' }} | GasGo</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#fff7ed;color:#243247;font:16px/1.6 system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px}main{width:100%;max-width:460px;background:white;padding:32px;border-radius:20px;box-shadow:0 12px 40px #24324712}.brand{font-weight:800;color:#b84400;font-size:24px}h1{font-size:26px;line-height:1.25}p{overflow-wrap:anywhere}label{display:block;font-weight:600;margin-top:18px}input{width:100%;padding:12px;border:1px solid #788392;border-radius:8px;font:inherit}button{width:100%;padding:12px;margin-top:20px;border:0;border-radius:8px;background:#b84400;color:white;font:inherit;font-weight:600;cursor:pointer}.logout{background:#eef1f5;color:#243247}:focus-visible{outline:3px solid #2563eb;outline-offset:3px}.notice{padding:12px;background:#eff6ff;border-radius:8px}.error{color:#a11919}
    </style>
</head>
<body>
<main>
    <div class="brand">GasGo</div>
    <h1>{{ $passwordSetup ? 'Create your GasGo password' : 'Verify your email' }}</h1>
    @if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
    @if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
    @if(session('error'))<p class="error" role="alert">{{ session('error') }}</p>@endif
    @if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($passwordSetup)
        <p>Finish setting up your account with a separate GasGo password. You can then sign in with Google or your email and password.</p>
        <form method="POST" action="{{ route('signup.password.store') }}">
            @csrf
            <label for="password">New GasGo password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" aria-describedby="password-help" required>
            <p id="password-help">Use at least 8 characters, including uppercase and lowercase letters, a number, and a symbol.</p>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
            <button type="submit">Save password and continue</button>
        </form>
    @else
        <p>Confirm <strong>{{ auth()->user()->email }}</strong> before continuing. Open the verification link in your email while signed in to this account.</p>
        <p>Check your spam folder too. If your link has expired, request a new one below.</p>
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button type="submit">Resend verification email</button></form>
    @endif
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Log out</button></form>
</main>
</body>
</html>
