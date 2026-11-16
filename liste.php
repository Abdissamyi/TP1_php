<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// 1. Recherche (Filtre par nom ou prénom)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $etudiants = array_filter($etudiants, function($e) use ($q) {
        return mb_stripos($e['nom'], $q) !== false || mb_stripos($e['prenom'], $q) !== false;
    });
}

// 2. Tri

$tri = $_GET['tri'] ?? 'defaut';
switch ($tri) {
    case 'nom':
        uasort($etudiants, function($a, $b) {
            return strcasecmp($a['nom'], $b['nom']);
        });
        break;
    case 'filiere':
        uasort($etudiants, function($a, $b) {
            return strcasecmp($a['filiere'], $b['filiere']);
        });
        break;
    case 'note_desc':
        uasort($etudiants, function($a, $b) {
            return $b['note'] <=> $a['note'];
        });
        break;
}

// 3. Statistiques
$nb_etudiants = count($etudiants);
$moyenne = 0;
$meilleure_note = 0;

if ($nb_etudiants > 0) {
    $notes = array_column($etudiants, 'note');
    $moyenne = array_sum($notes) / $nb_etudiants;
    $meilleure_note = max($notes);
}

$titre = 'Liste des Étudiants';
include 'header.php';
?>

<h2>Liste des Étudiants</h2>

<?php if ($message): ?>
    <div class="alert success"><?= h($message) ?></div>
<?php endif; ?>

<!-- Formulaire de recherche et de tri (Soumission purement PHP) -->
<form method="get" action="" class="filter-form">
    <input type="text" name="q" placeholder="Rechercher par nom/prénom..." value="<?= h($q) ?>">
    <select name="tri">
        <option value="defaut" <?= $tri === 'defaut' ? 'selected' : '' ?>>Ordre d'ajout</option>
        <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
        <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>Filière</option>
        <option value="note_desc" <?= $tri === 'note_desc' ? 'selected' : '' ?>>Note décroissante</option>
    </select>
    <button type="submit">Filtrer</button>
</form>

<!-- Affichage des statistiques-->
<div class="stats">
    <p><strong>Nombre d'étudiants :</strong> <?= $nb_etudiants ?></p>
    <p><strong>Moyenne générale :</strong> <?= $nb_etudiants > 0 ? number_format($moyenne, 2, '.', ' ') : 'N/A' ?></p>
    <p><strong>Meilleure note :</strong> <?= $nb_etudiants > 0 ? $meilleure_note : 'N/A' ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>Photo</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Filière</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($etudiants)): ?>
            <tr><td colspan="6" style="text-align: center;">Aucun étudiant</td></tr>
        <?php else: ?>
            <?php foreach ($etudiants as $e): ?>
                <tr>
                    <td>
                        <?php if (!empty($e['photo']) && file_exists('uploads/' . $e['photo'])): ?>
                            <img src="uploads/<?= h($e['photo']) ?>" alt="Photo" class="avatar">
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h(strtoupper($e['nom'])) ?></td>
                    <td><?= h(ucfirst($e['prenom'])) ?></td>
                    <td><?= h($e['filiere']) ?></td>
                    <!-- Note en vert si >= 10, sinon en rouge -->
                    <td style="color: <?= $e['note'] >= 10 ? 'green' : 'red' ?>;">
                        <?= h($e['note']) ?>
                    </td>
                    <td>
                        <a href="ajouter.php?id=<?= $e['id'] ?>">Modifier</a> | 
                        <a href="supprimer.php?id=<?= $e['id'] ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>