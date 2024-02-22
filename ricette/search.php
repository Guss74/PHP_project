<?php
$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);
$searchResults = [];

if (isset($_GET['query'])) {
    $query = strtolower($_GET['query']); 

    
    foreach ($users as $user) {
        foreach ($user['recipes'] as $recipe) {
            if (stripos($recipe['name'], $query) !== false || stripos($recipe['instructions'], $query) !== false) {
                $searchResults[] = $recipe;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati della ricerca</title>
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

    </style>
</head>
<body>

<h1>Risultati della ricerca</h1>

<?php if (isset($_GET['query'])): ?>
    <?php if (!empty($searchResults)): ?>
        <ul>
            <?php foreach ($searchResults as $result): ?>
                <li><?php echo $result['name']; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nessuna ricetta trovata per "<?php echo $_GET['query']; ?>"</p>
    <?php endif; ?>
<?php else: ?>
    <p>Nessuna query di ricerca specificata.</p>
<?php endif; ?>
<?php if (isset($_GET['query'])): ?>
    <?php if (!empty($searchResults)): ?>
        <div class="recipe-container">
            <?php foreach ($searchResults as $result): ?>
                <div class="recipe-box">
                    <h3><?php echo $result['name']; ?></h3>
                    <p>Ingredients: <?php echo implode(', ', $result['ingredients']); ?></p>
                    <p>Instructions: <?php echo $result['instructions']; ?></p>
                    <?php if (!empty($result['image'])): ?>
                        <img src="<?php echo $result['image']; ?>" alt="Recipe Image" style="max-width: 100%;">
                    <?php endif; ?>

                    <!--commenti -->
                    <div class="comment-container">
                        <h4>Commenti:</h4>
                        <?php if (isset($result['comments']) && is_array($result['comments'])): ?>
                            <?php foreach ($result['comments'] as $comment): ?>
                                <p class="comment"><strong><?php echo $comment['user']; ?>:</strong> <?php echo $comment['comment']; ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nessuna ricetta trovata per "<?php echo $_GET['query']; ?>"</p>
    <?php endif; ?>
<?php else: ?>
    <p>Nessuna query di ricerca specificata.</p>
<?php endif; ?>
<form action="index.php" method="GET">
    <button type="submit">Indietro</button>
</form>

</body>
</html>
