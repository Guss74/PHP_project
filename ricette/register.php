<!DOCTYPE html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="index.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <input type="submit" name="register" value="Register">
    </form>
</body>
</html>
