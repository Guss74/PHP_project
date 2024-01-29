<?php
session_start();

$recipesFile = 'recipes.json';
$recipes = json_decode(file_get_contents($recipesFile), true);

$users = [];

$usersFile = 'users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
}

// Per registrarsi
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

// Per effettuare il login
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

// Per effettuare il logout
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

    // Dopo la parte di gestione degli ingredienti e delle istruzioni
    $recipeImage = '';

    // Verifica se è stata caricata un'immagine
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Assicurati di creare questa cartella nel tuo progetto
        $uploadFile = $uploadDir . basename($_FILES['recipe_image']['name']);

        // Sposta l'immagine nella cartella di upload
        move_uploaded_file($_FILES['recipe_image']['tmp_name'], $uploadFile);

        $recipeImage = $uploadFile;
    }

    // Aggiungi il percorso dell'immagine alla tua ricetta
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            $user['recipes'][] = [
                'name' => $recipeName,
                'ingredients' => $ingredients,
                'instructions' => $instructions,
                'image' => $recipeImage, // Aggiungi questo campo
                'comments' => [], // Inizializza l'array dei commenti
            ];
            break;
        }
    }

    // Salva le modifiche nel file JSON
    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}

// Aggiunta della parte per rimuovere le ricette
if (isUserAuthenticated() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_recipe'])) {
    $recipeIndex = $_POST['recipe_index'];

    // Rimuovi la ricetta solo se l'utente attuale è quello che l'ha postata
    foreach ($users as &$user) {
        if ($user['username'] === $_SESSION['username']) {
            if (isset($user['recipes'][$recipeIndex])) {
                unset($user['recipes'][$recipeIndex]);
            }
            break;
        }
    }

    // Salva le modifiche nel file JSON
    file_put_contents($usersFile, json_encode($users));
    header('Location: index.php');
    exit;
}

// Aggiunta della parte per aggiungere commenti alle ricette
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
                break 2; // Break due livelli per uscire dai due loop
            }
        }
    }

    // Salva le modifiche nel file JSON
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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #222; /* Nero antracite */
            color: #fff; /* Bianco */
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
            width: calc(33.33% - 20px); /* 33.33% width for each box with a 20px gap */
            box-sizing: border-box;
            margin-bottom: 20px;
            background-color: #333; /* Grigio scuro */
            color: #fff; /* Bianco */
        }

        .comment-container {
            background-color: #444; /* Grigio scuro leggermente più chiaro */
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .comment {
            margin: 5px 0;
            color: #fff; /* Bianco */
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

        /* Aggiungi altri stili secondo necessità */
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

                        <!-- Aggiunta della sezione dei commenti -->
                        <div class="comment-container">
                            <h4>Commenti:</h4>
                            <?php if (isset($recipe['comments']) && is_array($recipe['comments'])): ?>
                                <?php foreach ($recipe['comments'] as $comment): ?>
                                    <p class="comment"><strong><?php echo $comment['user']; ?>:</strong> <?php echo $comment['comment']; ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Aggiunta del modulo per aggiungere commenti -->
                        <form action="index.php" method="POST">
                            <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                            <label for="comment_text">Aggiungi commento:</label>
                            <textarea name="comment_text" required></textarea><br>
                            <input type="submit" name="add_comment" value="Aggiungi Commento">
                        </form>

                        <!-- Aggiunta del modulo per rimuovere la ricetta -->
                        <form action="index.php" method="POST">
                            <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                            <input type="submit" name="remove_recipe" value="Remove Recipe">
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <p><a href="register.php">Register</a> or <a href="login.php">Login</a> per aggiungere ricette</p>
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

                <!-- Aggiunta della sezione dei commenti -->
                <div class="comment-container">
                    <h4>Commenti:</h4>
                    <?php if (isset($recipe['comments']) && is_array($recipe['comments'])): ?>
                        <?php foreach ($recipe['comments'] as $comment): ?>
                            <p class="comment"><strong><?php echo $comment['user']; ?>:</strong> <?php echo $comment['comment']; ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Aggiunta del modulo per aggiungere commenti -->
                <form action="index.php" method="POST">
                    <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                    <label for="comment_text">Aggiungi commento:</label>
                    <textarea name="comment_text" required></textarea><br>
                    <input type="submit" name="add_comment" value="Aggiungi Commento">
                </form>

                <!-- Aggiunta del modulo per rimuovere la ricetta -->
                <?php if (isUserAuthenticated() && $user['username'] === $_SESSION['username']): ?>
                    <form action="index.php" method="POST">
                        <input type="hidden" name="recipe_index" value="<?php echo $recipeIndex; ?>">
                        <input type="submit" name="remove_recipe" value="Remove Recipe">
                    </form>
                <?php endif; ?>
                
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

        <input type="submit" name="add_recipe" value="Add Recipe">
    </form>
<?php endif; ?>
</body>
</html>