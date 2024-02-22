<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #222; 
            color: #fff; 
        }

        h2 {
            margin-bottom: 10px;
            color: #fff; 
        }

        form {
            margin-top: 20px;
            clear: both;
            width: 100%;
        }

        form label,
        form input {
            display: block;
            margin-bottom: 10px;
            width: 100%;
            color: #333; 
            background-color: #555; 
            padding: 10px;
            border: none;
            border-radius: 4px;
        }

        form input[type="submit"] {
            background-color: #1E90FF; 
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: auto;
        }

        form input[type="submit"]:hover {
            background-color: #007acc; 
        }
    </style>
</head>
<body>
    <h2>Login</h2>
    <form action="index.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <input type="submit" name="login" value="Login">
    </form>
</body>
</html>
