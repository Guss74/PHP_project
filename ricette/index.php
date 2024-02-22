<?php
session_start();

$recipesFile = 'recipes.json';
$recipes = json_decode(file_get_contents($recipesFile), true);

$users = [];

$usersFile = 'users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
}

// registrarsi
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

//login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            break;
        }
    }
    header('Location: index.php');
    exit;
}

//logout
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {
    $recipeName = $_POST['recipe_name'];
    $ingredients = explode(',', $_POST['ingredients']);
    $instructions = $_POST['instructions'];

    
    $recipeImage = '';

    // controlla se ha caricato la foto
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; 
        $uploadFile = $uploadDir . basename($_FILES['recipe_image']['name']);

        // Sposta l'immagine
        move_uploaded_file($_FILES['recipe_image']['tmp_name'], $uploadFile);

        $recipeImage = $uploadFile;
    }

    // percorso dell'immagine
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            $user['recipes'][] = [
                'name' => $recipeName,
                'ingredients' => $ingredients,
                'instructions' => $instructions,
                'image' => $recipeImage, 
                'comments' => [], 
            ];
            break;
        }
    }

    
    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}

// rimozione ricetta 
if (isUserAuthenticated() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_recipe'])) {
    $recipeIndex = $_POST['recipe_index'];

    // Rimuove la ricetta se è l'utente che la messa 
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            if (isset($user['recipes'][$recipeIndex])) {
                unset($user['recipes'][$recipeIndex]);
            }
            break;
        }
    }

    // Salva
    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}

//commenti alle ricette
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

    // Salva le modifiche
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
    <title>Ricette</title>
    <style>
        textarea {
        color: black !important;
    } 
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
.edit-button {
        color: black !important;
    }

    
    form .edit-button {
        color: black !important;
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
        textarea {
        color: black !important;
    }
    form textarea {
        color: black !important;
    }
    input {
        color: black !important;
    }

    
    form input {
        color: black !important;
    }
      
    </style>
</head>
<body>

<?php if (isUserAuthenticated()): ?>
    <p>Ciao <?php echo $_SESSION['username']; ?>! <a href="?logout">Logout</a></p>
    <h2>Tue Ricette</h2>

    <div class="recipe-container">
        <?php foreach ($users as $user): ?>
            <?php if ($user['username'] === $_SESSION['username']): ?>
                <?php foreach ($user['recipes'] as $recipeIndex => $recipe): ?>
                    <div class="recipe-box">
                        <h3><?php echo $recipe['name']; ?></h3>
                        <p>Ingredients: <?php echo implode(', ', $recipe['ingredients']); ?></p>
                        <p>Instructions: <?php echo $recipe['instructions']; ?></p>
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?php echo $recipe['image']; ?>" alt="Recipe Image" style="max-width: 100%;">
                        <?php endif; ?>

                        <!--commenti -->
                        <div class="comment-container">
                            <h4>Commenti:</h4>
                            <?php if (isset($recipe['comments']) && is_array($recipe['comments'])): ?>
                                <?php foreach ($recipe['comments'] as $comment): ?>
                                    <p class="comment"><strong><?php echo $comment['user']; ?>:</strong> <?php echo $comment['comment']; ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!--aggiungere commenti -->
                        <form action="index.php" method="POST">
                            <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                            <label for="comment_text">Aggiungi commento:</label>
                            <textarea name="comment_text" required></textarea><br>
                            <input type="submit" name="add_comment" value="Aggiungi Commento">
                        </form>
                        

                        <!--rimuovere la ricetta -->
                        <form action="index.php" method="POST">
                            <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                            <input type="submit" name="remove_recipe" value="Rimuovi Ricetta">
                        </form>
                        <!-- modificare la ricetta -->
<form action="modifica_ricetta.php" method="GET">
    <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
    <button type="submit" name="edit_recipe" class="btn btn-primary">Modifica Ricetta</button>
</form>
                        
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <p><a href="register.php">Registrati</a> o <a href="login.php">Accedi</a> per aggiungere ricette</p>
<?php endif; ?>

<h2>Lista Ricette</h2>

<div class="recipe-container">
    <?php foreach ($users as $user): ?>
        <?php foreach ($user['recipes'] as $recipeIndex => $recipe): ?>
            <div class="recipe-box">
                <h3><?php echo $recipe['name']; ?></h3>
                <p>Ingredients: <?php echo implode(', ', $recipe['ingredients']); ?></p>
                <p>Instructions: <?php echo $recipe['instructions']; ?></p>
                <?php if (!empty($recipe['image'])): ?>
                    <img src="<?php echo $recipe['image']; ?>" alt="Recipe Image" style="max-width: 100%;">
                <?php endif; ?>

                <!--commenti -->
                <div class="comment-container">
                    <h4>Commenti:</h4>
                    <?php if (isset($recipe['comments']) && is_array($recipe['comments'])): ?>
                        <?php foreach ($recipe['comments'] as $comment): ?>
                            <p class="comment"><strong><?php echo $comment['user']; ?>:</strong> <?php echo $comment['comment']; ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- aggiungere commenti -->
                <form action="index.php" method="POST">
                    <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                    <label for="comment_text">Aggiungi commento:</label>
                    <textarea name="comment_text" required></textarea><br>
                    <input type="submit" name="add_comment" value="Aggiungi Commento">
                </form>
                <!-- modificare la ricetta -->
    <form action="modifica_ricetta.php" method="GET">
        <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
        <button type="submit" name="edit_recipe" class="btn btn-primary">Modifica Ricetta</button>
    </form>



                
                
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<?php if (isUserAuthenticated()): ?>
    <h2>Aggiungi nuova ricetta</h2>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <label for="recipe_name">Ricetta</label>
        <input type="text" name="recipe_name" required><br>

        <label for="ingredients">Ingredienti :</label>
        <input type="text" name="ingredients" required><br>

        <label for="instructions">Istruzioni:</label>
        <textarea name="instructions" required></textarea><br>

        
        <label for="recipe_image">Foto Ricetta:</label>
        <input type="file" name="recipe_image" accept="image/*"><br>

        <input type="submit" name="add_recipe" value="Aggiungi Ricetta">
    </form>
    <form action="search.php" method="GET">
    <input type="text" name="query" placeholder="Cerca ricette...">
    <button type="submit">Cerca</button>
</form>
<?php endif; ?>
</body>
</html>
