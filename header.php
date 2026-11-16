<?php
$titre = $titre ?? 'Gestion des Étudiants';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= h($titre) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <h1>Gestion des Étudiants</h1>
            <?php if (connecte()): ?>
                <ul>
                    <li><a href="liste.php">Liste</a></li>
                    <li><a href="ajouter.php">Ajouter un étudiant</a></li>
                    <li><a href="logout.php">Déconnexion (<?= h($_SESSION['user']) ?>)</a></li>
                </ul>
            <?php endif; ?>
        </nav>
    </header>
    <main>