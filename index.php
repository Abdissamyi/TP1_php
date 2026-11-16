<?php
require_once 'functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login_cookie = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $mdp = $_POST['mdp'] ?? '';

    if (isset(UTILISATEURS[$login]) && UTILISATEURS[$login] === $mdp) {
        $_SESSION['user'] = $login;
        setcookie('dernier_user', $login, time() + 7 * 24 * 60 * 60);
        header('Location: liste.php');
        exit;
    } else {
        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}

$titre = 'Connexion';
include 'header.php';
?>

<div class="card">
    <h2>Connexion</h2>
    <?php if ($erreur): ?>
        <div class="alert error"><?= h($erreur) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="login">Login :</label>
            <input type="text" id="login" name="login" value="<?= h($login_cookie) ?>" required>
        </div>
        <div>
            <label for="mdp">Mot de passe :</label>
            <input type="password" id="mdp" name="mdp" required>
        </div>
        <button type="submit">Se connecter</button>
    </form>
</div>

<?php include 'footer.php'; ?>