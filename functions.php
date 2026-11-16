<?php
// Démarrage de la session
session_start();

// Constantes du projet
define('FICHIER', __DIR__ . '/data/etudiants.txt');
define('FILIERES', ['GI', 'IDIA', 'GE', 'GC']);
define('UTILISATEURS', [
    'admin' => 'password123',
    'prof' => 'secret'
]);

/**
 * Protège l'affichage contre les failles XSS
 */
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function connecte(): bool {
    return isset($_SESSION['user']);
}

/**
 * Exige la connexion (redirige vers index.php si non connecté)
 */
function exiger_connexion(): void {
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Nettoie une chaîne de caractères
 */
function nettoyer(string $s): string {
    return trim(str_replace([';', "\n", "\r"], '', $s));
}

/**
 * Lit les étudiants depuis le fichier texte et retourne un tableau associatif indexé par l'id
 */
function lire_etudiants(): array {
    if (!file_exists(FICHIER)) {
        return [];
    }
    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $etudiants = [];
    foreach ($lignes as $ligne) {
        $parties = explode(';', $ligne);
        if (count($parties) >= 6) {
            $id = (int)$parties[0];
            $etudiants[$id] = [
                'id' => $id,
                'nom' => $parties[1],
                'prenom' => $parties[2],
                'filiere' => $parties[3],
                'note' => (float)$parties[4],
                'photo' => $parties[5]
            ];
        }
    }
    return $etudiants;
}

/**
 * Sauvegarde le tableau des étudiants dans le fichier texte
 */
function sauver_etudiants(array $liste): void {
    $contenu = '';
    foreach ($liste as $e) {
        $contenu .= implode(';', [
            $e['id'],
            $e['nom'],
            $e['prenom'],
            $e['filiere'],
            $e['note'],
            $e['photo']
        ]) . "\n";
    }
    file_put_contents(FICHIER, $contenu);
}