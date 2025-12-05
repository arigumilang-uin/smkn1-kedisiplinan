<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Kedisiplinan</title>
    <style>
        /* CSS Sederhana untuk membuat form di tengah */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-card h1 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            color: #333;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box; /* Penting untuk padding */
        }
        .form-group-remember {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .form-group-remember input {
            margin-right: 0.5rem;
        }
        .btn {
            width: 100%;
            padding: 0.85rem;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 0.75rem 1.25rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h1>LOGIN SISTEM</h1>
        <p style="text-align: center; margin-top: -1rem; margin-bottom: 1.5rem; color: #777;">SMKN 1 Siak Lubuk Dalam</p>

        <form action="{{ route('login') }}" method="POST">
            
            @csrf

            @error('username')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror

            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Masukkan username atau email Anda">
                <small style="color: #777; font-size: 0.85rem; display: block; margin-top: 0.25rem;">Anda bisa login menggunakan username atau email yang terdaftar</small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group-remember">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat Saya</label>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

    </div>

</body>
</html>