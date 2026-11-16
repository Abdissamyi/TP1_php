<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode_edition = ($id > 0);

$nom = '';
$prenom = '';
$filiere = 'GI';
$note = '';
$photo_actuelle = '';
$erreurs = [];

if ($mode_edition) {
    if (!isset($etudiants[$id])) {
        header('Location: liste.php');
        exit;
    }
    $etudiant = $etudiants[$id];
    $nom = $etudiant['nom'];
    $prenom = $etudiant['prenom'];
    $filiere = $etudiant['filiere'];
    $note = $etudiant['note'];
    $photo_actuelle = $etudiant['photo'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = nettoyer($_POST['nom'] ?? '');
    $prenom = nettoyer($_POST['prenom'] ?? '');
    $filiere = $_POST['filiere'] ?? '';
    $note_saisie = $_POST['note'] ?? '';

    if ($nom === '') {
        $erreurs[] = "Le nom ne doit pas être vide.";
    }
    if ($prenom === '') {
        $erreurs[] = "Le prénom ne doit pas être vide.";
    }
    if (!in_array($filiere, FILIERES)) {
        $erreurs[] = "Filière invalide.";
    }
    if (!is_numeric($note_saisie) || (float)$note_saisie < 0 || (float)$note_saisie > 20) {
        $erreurs[] = "La note doit être un nombre compris entre 0 et 20.";
    }

    $nom_photo = $photo_actuelle;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['photo'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extensions_autorisees = ['jpg', 'jpeg', 'png'];
            $taille_max = 1024 * 1024;

            if (!in_array($extension, $extensions_autorisees)) {
                $erreurs[] = "Extension de photo non autorisée (jpg, jpeg, png uniquement).";
            } elseif ($file['size'] > $taille_max) {
                $erreurs[] = "La taille de la photo dépasse 1 Mo.";
            } else {
                $nom_photo = uniqid('etu_') . '.' . $extension;
                move_uploaded_file($file['tmp_name'], 'uploads/' . $nom_photo);

                if ($mode_edition && !empty($photo_actuelle) && file_exists('uploads/' . $photo_actuelle)) {
                    unlink('uploads/' . $photo_actuelle);
                }
            }
        } else {
            $erreurs[] = "Erreur lors du téléchargement du fichier.";
        }
    }

    if (empty($erreurs)) {
        $note = (float)$note_saisie;

        if ($mode_edition) {
            $etudiants[$id] = [
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'filiere' => $filiere,
                'note' => $note,
                'photo' => $nom_photo
            ];
            $_SESSION['flash'] = "Étudiant modifié avec succès.";
        } else {
            $nouvel_id = 1;
            if (!empty($etudiants)) {
                $nouvel_id = max(array_keys($etudiants)) + 1;
            }
            $etudiants[$nouvel_id] = [
                'id' => $nouvel_id,
                'nom' => $nom,
                'prenom' => $prenom,
                'filiere' => $filiere,
                'note' => $note,
                'photo' => $nom_photo
            ];
            $_SESSION['flash'] = "Étudiant ajouté avec succès.";
        }

        sauver_etudiants($etudiants);
        header('Location: liste.php');
        exit;
    }
}

$titre = $mode_edition ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include 'header.php';
?>

<div class="card">
    <h2><?= $titre ?></h2>

    <?php if (!empty($erreurs)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <div>
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?= h($nom) ?>" required>
        </div>
        <div>
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?= h($prenom) ?>" required>
        </div>
        <div>
            <label for="filiere">Filière :</label>
            <select id="filiere" name="filiere">
                <?php foreach (FILIERES as $f): ?>
                    <option value="<?= $f ?>" <?= $filiere === $f ? 'selected' : '' ?>><?= $f ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="note">Note (/20) :</label>
            <input type="number" step="0.5" min="0" max="20" id="note" name="note" value="<?= h($note) ?>" required>
        </div>
        <div>
            <label for="photo">Photo de profil :</label>
            <?php if ($photo_actuelle && file_exists('uploads/' . $photo_actuelle)): ?>
                <br><img src="uploads/<?= h($photo_actuelle) ?>" alt="Photo actuelle" class="avatar"><br>
            <?php endif; ?>
            <input type="file" id="photo" name="photo">
        </div>
        <button type="submit"><?= $mode_edition ? 'Enregistrer les modifications' : 'Ajouter' ?></button>
    </form>
</div>

<?php include 'footer.php'; ?>