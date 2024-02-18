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
    // Ricevi i dati inviati dal modulo di modifica
    $recipe['name'] = $_POST['recipe_name'];
    $recipe['ingredients'] = explode(',', $_POST['ingredients']);
    $recipe['instructions'] = $_POST['instructions'];

    // Aggiorna la ricetta nell'array degli utenti
    $users[array_search($_SESSION['username'], array_column($users, 'username'))]['recipes'][$recipeIndex] = $recipe;

    // Salva le modifiche nel file JSON degli utenti
    file_put_contents('users.json', json_encode($users));

    // Reindirizza alla home
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
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #222; /* Nero antracite */
    color: #fff; /* Bianco */
}

.form-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background-color: #333; /* Grigio scuro */
    color: #fff; /* Bianco */
    width: 50%;
    max-width: 600px;
}

.form-container label,
.form-container input,
.form-container textarea {
    display: block;
    margin-bottom: 10px;
    width: 100%;
    color: #fff; /* Bianco */
    border: 1px solid #666; /* Grigio scuro */
    background-color: #444; /* Grigio scuro leggermente più chiaro */
    padding: 8px;
    border-radius: 4px;
}

.form-container textarea {
    resize: vertical; /* Per consentire il ridimensionamento verticale */
    min-height: 100px; /* Altezza minima della textarea */
}

.form-container input[type="submit"],
.form-container button[type="submit"] {
    background-color: #1E90FF; /* Colore modificato */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: auto;
}

.form-container input[type="submit"]:hover,
.form-container button[type="submit"]:hover {
    background-color: #104e8b; /* Colore leggermente più scuro in hover */
}
.btn-primary {
    background-color: #1E90FF; /* Colore modificato */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary:hover {
    background-color: #104e8b; /* Colore leggermente più scuro in hover */
}

/* Aggiungi altri stili secondo necessità */

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
