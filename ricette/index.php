<?php
session_start();
$recipesFile = 'recipes.json';
$recipes = json_decode(file_get_contents($recipesFile), true);

$users = [];

$usersFile = 'users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
}
//per reggistrarsi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    $user = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'recipes' => [], 
    ];

    
    $users[] = $user;

    
    file_put_contents($usersFile, json_encode($users));
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            break;
        }
    }
    header('Location: index.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

function isUserAuthenticated()
{
    return isset($_SESSION['username']);
}

// parte ricetta
if (isUserAuthenticated() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {
    $recipeName = $_POST['recipe_name'];
    $ingredients = explode(',', $_POST['ingredients']);
    $instructions = $_POST['instructions'];

    
    $currentUser = null;
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            $currentUser = &$user;
            break;
        }
    }

    
    $currentUser['recipes'][] = [
        'name' => $recipeName,
        'ingredients' => $ingredients,
        'instructions' => $instructions,
    ];

    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<head>
    <title>Ricette</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            margin-top: 0;
        }

        .recipe-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .recipe-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            width: calc(25% - 20px); /* 25% width for each box with a 20px gap */
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
        }

        form label, form input, form textarea {
            display: block;
            margin-bottom: 10px;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
    
        
</head>
<body>

    <?php if (isUserAuthenticated()): ?>
        <p>Ciao <?php echo $_SESSION['username']; ?>! <a href="?logout">Logout</a></p>
        <h2>Tue Ricette</h2>
        <?php
        
        foreach ($users as $user) {
            if ($user['username'] === $_SESSION['username']) {
                foreach ($user['recipes'] as $recipe) {
                    echo '<h3>' . $recipe['name'] . '</h3>';
                    echo '<p>Ingredients: ' . implode(', ', $recipe['ingredients']) . '</p>';
                    echo '<p>Instructions: ' . $recipe['instructions'] . '</p>';
                }
                break;
            }
        }
        ?>
    <?php else: ?>
        <p><a href="register.php">Register</a> or <a href="login.php">Login</a> per aggiungere ricette</p>
    <?php endif; ?>

    <h2>Lista Ricette</h2>
    <?php
    
    foreach ($recipes as $recipe) {
        echo '<h3>' . $recipe['name'] . '</h3>';
        echo '<p>Ingredients: ' . implode(', ', $recipe['ingredients']) . '</p>';
        echo '<p>Instructions: ' . $recipe['instructions'] . '</p>';
    }
    ?>

    <?php if (isUserAuthenticated()): ?>
        
        <h2>Aggiungi nuova ricetta</h2>
        <form action="index.php" method="POST">
            <label for="recipe_name">Ricetta</label>
            <input type="text" name="recipe_name" required><br>

            <label for="ingredients">Ingredienti :</label>
            <input type="text" name="ingredients" required><br>

            <label for="instructions">Istruzioni:</label>
            <textarea name="instructions" required></textarea><br>

            <input type="submit" name="add_recipe" value="Add Recipe">
        </form>
    <?php endif; ?>
</body>
</html>

