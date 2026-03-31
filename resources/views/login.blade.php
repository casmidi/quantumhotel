<!DOCTYPE html>
<html>
<head>
    <title>Quantum Hotel Login</title>
</head>
<body>

<h2>Login Quantum Hotel</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="/login">
    @csrf
    <input type="text" name="username" placeholder="User"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button type="submit">Login</button>
</form>

</body>
</html>