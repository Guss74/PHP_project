<?php
session_start();

$users = [];

$usersFile = 'users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $recipeIndex = $_POST['recipe_index'];
    $commentText = $_POST['comment_text'];

    foreach ($users as &$user) {
        foreach ($user['recipes'] as $recipeIndexUser => &$recipe) {
            if (
                isset($recipe['comments']) && is_array($recipe['comments']) &&
                $recipeIndex == $recipeIndexUser
            ) {
                $recipe['comments'][] = [
                    'user' => $_SESSION['username'],
                    'comment' => $commentText,
                ];
                break 2; 
            }
        }
    }


    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Commento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #222;
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
            background-color: #104e8b; 
        }
    </style>
</head>
<body>

<h2>Aggiungi Commento</h2>
<form action="comment.php" method="POST">
    <input type="hidden" name="recipe_index" value="<?php echo $_POST['recipe_index']; ?>">
    <label for="comment_text">Aggiungi commento:</label>
    <textarea name="comment_text" required></textarea><br>
    <input type="submit" name="add_comment" value="Aggiungi Commento">
</form>

</body>
</html>
