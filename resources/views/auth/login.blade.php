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
            max-width: 420px;
            background: var(--fs-bg-card);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(13,115,119,0.08);
            padding: 2.5rem;
            border: 1px solid var(--fs-border);
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--fs-secondary);
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-logo">
        <i class="fa-solid fa-chart-line text-success"></i> FINSYNC
    </div>
    <h4 class="fw-bold mb-1 text-center">Selamat Datang</h4>
    <p class="text-muted text-center mb-4" style="font-size: var(--fs-text-sm);">Masuk ke sistem ERP Keuangan</p>

    @if ($errors->any())
        <div class="alert alert-danger" style="font-size: var(--fs-text-sm); border-radius: 8px;">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="emailInput" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
            <label for="emailInput">Alamat Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
            <label for="passwordInput">Password</label>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberCheck">
            <label class="form-check-label text-muted" for="rememberCheck" style="font-size: var(--fs-text-sm);">
                Ingat Saya
            </label>
        </div>
        <button class="btn btn-fs-primary w-100 py-2 fw-bold" style="border-radius: 8px;" type="submit">Masuk Sistem</button>
    </form>
</div>

</body>
</html>
