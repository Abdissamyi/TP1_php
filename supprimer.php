<?php
require_once 'functions.php';
exiger_connexion();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$etudiants = lire_etudiants();

if ($id <= 0 || !isset($etudiants[$id])) {
    header('Location: liste.php');
    exit;
}

$etudiant = $etudiants[$id];

// Si l'utilisateur a cliqué sur le bouton "Confirmer"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($etudiant['photo']) && file_exists('uploads/' . $etudiant['photo'])) {
        unlink('uploads/' . $etudiant['photo']);
    }

    unset($etudiants[$id]);
    sauver_etudiants($etudiants);

    $_SESSION['flash'] = "Étudiant supprimé avec succès.";
    header('Location: liste.php');
    exit;
}

$titre = 'Confirmer la suppression';
include 'header.php';
?>

<div class="card">
    <h2>Confirmer la suppression</h2>
    <p>Êtes-vous sûr de vouloir supprimer l'étudiant <strong><?= h(strtoupper($etudiant['nom'])) ?> <?= h(ucfirst($etudiant['prenom'])) ?></strong> ?</p>
    
    <form method="post" action="">
        <button type="submit" style="background-color: #e74c3c;">Oui, supprimer</button>
        <a href="liste.php" style="margin-left: 10px; text-decoration: none; padding: 10px 15px; background: #bdc3c7; color: white; border-radius: 4px;">Annuler</a>
    </form>
</div>

<?php include 'footer.php'; ?>