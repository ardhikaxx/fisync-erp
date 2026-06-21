<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FINSYNC ERP</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/finsync.css') }}">
    <style>
        body {
            background-color: var(--fs-bg-body);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--fs-bg-card);
            border-radius: 12px;
            padding: 3rem 2.5rem;
            border: 1px solid var(--fs-border);
        }
        .brand-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--fs-secondary);
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.8rem 1rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-logo">
        FINSYNC
    </div>
    <p class="text-muted text-center mb-5" style="font-size: 0.85rem;">Masuk ke sistem ERP Keuangan</p>

    @if ($errors->any())
        <div class="alert alert-danger" style="font-size: var(--fs-text-sm); border-radius: 8px; border: none; background-color: var(--fs-danger-bg); color: var(--fs-danger);">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-bold" style="font-size: 0.85rem;">Alamat Email</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold" style="font-size: 0.85rem;">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberCheck">
            <label class="form-check-label text-muted" for="rememberCheck" style="font-size: 0.85rem;">
                Ingat Saya
            </label>
        </div>
        <button class="btn btn-fs-primary w-100 py-2 fw-bold" style="border-radius: 8px;" type="submit">Masuk Sistem</button>
    </form>
</div>

</body>
</html>
