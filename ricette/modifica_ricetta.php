<?php
session_start();

$recipeIndex = isset($_GET['recipe_index']) ? $_GET['recipe_index'] : null;

if ($recipeIndex === null) {
    // Gestisci il caso in cui non sia stato fornito un indice di ricetta valido
    header('Location: index.php');
    exit;
}

// Recupera la ricetta da modificare
$recipeToEdit = null;

foreach ($users as $user) {
    if ($user['username'] === $_SESSION['username']) {
        if (isset($user['recipes'][$recipeIndex])) {
            $recipeToEdit = $user['recipes'][$recipeIndex];
            break;
        }
    }
}

if ($recipeToEdit === null) {
    // Gestisci il caso in cui la ricetta da modificare non sia stata trovata
    header('Location: index.php');
    exit;
}

// Processa il form di modifica quando viene inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_recipe'])) {
    // Recupera i dati dal form
    $updatedRecipeName = $_POST['recipe_name'];
    $updatedIngredients = explode(',', $_POST['ingredients']);
    $updatedInstructions = $_POST['instructions'];

    // Aggiorna i dati della ricetta
    $users[$_SESSION['username']]['recipes'][$recipeIndex]['name'] = $updatedRecipeName;
    $users[$_SESSION['username']]['recipes'][$recipeIndex]['ingredients'] = $updatedIngredients;
    $users[$_SESSION['username']]['recipes'][$recipeIndex]['instructions'] = $updatedInstructions;

    // Salva le modifiche nel file JSON
    file_put_contents($usersFile, json_encode($users));
    
    // Reindirizza alla pagina delle ricette
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
            background-color: #222; /* Nero antracite */
            color: #fff; /* Bianco */
        }

        form {
            margin-top: 20px;
            clear: both;
            width: 50%;
        }

        form label,
        form input,
        form textarea {
            display: block;
            margin-bottom: 10px;
            width: 100%;
            color: #fff; /* Bianco */
        }

        form input[type="submit"] {
            background-color: #1E90FF; /* Colore modificato */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: auto;
        }

        form input[type="submit"]:hover {
            background-color: #104e8b; /* Colore leggermente più scuro in hover */
        }
    </style>
</head>
<body>

<h2>Modifica Ricetta</h2>

<form action="modifica_ricetta.php?recipe_index=<?php echo $recipeIndex; ?>" method="POST">
    <label for="recipe_name">Nome Ricetta:</label>
    <input type="text" name="recipe_name" value="<?php echo $recipeToEdit['name']; ?>" required><br>

    <label for="ingredients">Ingredienti:</label>
    <input type="text" name="ingredients" value="<?php echo implode(', ', $recipeToEdit['ingredients']); ?>" required><br>

    <label for="instructions">Istruzioni:</label>
    <textarea name="instructions" required><?php echo $recipeToEdit['instructions']; ?></textarea><br>

    <input type="submit" name="update_recipe" value="Salva Modifiche">
</form>

</body>
</html>
