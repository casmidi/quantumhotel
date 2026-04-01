<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Quantum Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo">
        <b>Quantum Hotel</b>
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <p class="login-box-msg">Sign in to start your session</p>

            <form method="POST" action="/login">
                @csrf

                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="User">
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <button class="btn btn-primary btn-block">Login</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>