<?php
session_start();

$recipesFile = 'recipes.json';
$users = json_decode(file_get_contents('users.json'), true);

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$recipeIndex = isset($_GET['recipe_index']) ? $_GET['recipe_index'] : null;
$recipe = null;

foreach ($users as &$user) {
    if ($user['username'] === $_SESSION['username'] && isset($user['recipes'][$recipeIndex])) {
        $recipe = $user['recipes'][$recipeIndex];
        break;
    }
}

if (!$recipe) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_recipe'])) {
    $recipe['name'] = $_POST['recipe_name'];
    $recipe['ingredients'] = explode(',', $_POST['ingredients']);
    $recipe['instructions'] = $_POST['instructions'];

    // Aggiorna 
    $users[array_search($_SESSION['username'], array_column($users, 'username'))]['recipes'][$recipeIndex] = $recipe;

    // Salva 
    file_put_contents('users.json', json_encode($users));

    // manda indietro
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Ricetta</title>
    <style>
    
    
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #222; 
        color: #fff; 
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

    .edit-button {
        background-color: #1E90FF;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .edit-button:hover {
        background-color: #104e8b;
    }

    .btn {
        background-color: #1E90FF; 
        color: black;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: auto;
    }

    .btn:hover {
        background-color: #104e8b; 
    }

    .recipe-box {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        width: calc(33.33% - 20px); 
        box-sizing: border-box;
        margin-bottom: 20px;
        background-color: #333; 
        color: #fff; 
    }

    .comment-container {
        background-color: #444; 
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
    }

    .comment {
        margin: 5px 0;
        color: #fff; 
    }

    form {
        margin-top: 20px;
        clear: both;
        width: 100%;
    }

    form label,
    form input,
    form textarea {
        display: block;
        margin-bottom: 10px;
        width: 100%;
        color: #fff; 
    }

    form textarea {
        min-height: 150px;
    }

    form input[type="submit"],
    form button[type="submit"] {
        background-color: #1E90FF; 
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: auto;
    }

    form input[type="submit"]:hover,
    form button[type="submit"]:hover {
        background-color: #104e8b; 
    }
    textarea {
        color: black !important;
    }
    form input[type="text"],
form input[type="password"],
form textarea {
    color: black; 
}
</style>


</head>
<body>
    <h2>Modifica Ricetta</h2>
    <form action="modifica_ricetta.php?recipe_index=<?php echo $recipeIndex; ?>" method="POST">
        <label for="recipe_name">Ricetta</label>
        <input type="text" name="recipe_name" value="<?php echo $recipe['name']; ?>" required><br>

        <label for="ingredients">Ingredienti :</label>
        <input type="text" name="ingredients" value="<?php echo implode(', ', $recipe['ingredients']); ?>" required><br>

        <label for="instructions">Istruzioni:</label>
        <textarea name="instructions" required><?php echo $recipe['instructions']; ?></textarea><br>

        <input type="submit" name="update_recipe" value="Salva Modifiche">
    </form>
</body>
</html>
