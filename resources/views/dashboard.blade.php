<!DOCTYPE html>
<html>
<head>
    <title>Quantum Hotel Dashboard</title>
</head>
<body>

<h2>Quantum Hotel System</h2>

<p>
    Login sebagai: <b>{{ session('user') }}</b> 
    ({{ session('role') }})
</p>

<hr>

<h3>TABLE</h3>
<ul>
    <li><a href="/room-class">Room Class</a></li>
    <li><a href="/room">Room</a></li>
</ul>

<h3>TRANSAKSI</h3>
<ul>
    <li><a href="/checkin">Checkin</a></li>
    <li><a href="/checkout">Checkout</a></li>
</ul>

<h3>REPORT</h3>
<ul>
    <li><a href="/guest-in-house">Guest In House</a></li>
    <li><a href="/expected-departure">Expected Departure</a></li>
</ul>

<h3>TOOLS</h3>
<ul>
    <li><a href="/user">Maintenance User</a></li>
    <li><a href="/change-password">Change Password</a></li>
</ul>

<br>
<a href="/logout">Logout</a>

</body>
</html>