<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FINSYNC ERP</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/finsync.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 3rem 2.5rem;
        }
        .brand-logo {
            font-size: var(--fs-text-2xl);
            font-weight: var(--fs-weight-extrabold);
            color: var(--fs-secondary);
            text-align: center;
            letter-spacing: -0.02em;
        }
        .brand-logo i { color: var(--fs-primary); }
    </style>
</head>
<body>

<div class="card login-card shadow-sm">
    <div class="brand-logo mb-1">
        <i class="fa-solid fa-chart-line me-2"></i>FINSYNC
    </div>
    <p class="text-muted text-center mb-4" style="font-size: var(--fs-text-sm);">Sistem Manajemen Akuntansi Terintegrasi</p>

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
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
            <label for="floatingEmail">Alamat Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
            <label for="floatingPassword">Password</label>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberCheck">
            <label class="form-check-label text-muted" for="rememberCheck" style="font-size: var(--fs-text-sm);">
                Ingat Saya
            </label>
        </div>
        <button class="btn btn-fs-primary w-100 py-2 fs-6" type="submit">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
        </button>
    </form>
</div>

</body>
</html>
