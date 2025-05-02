<?php
// Vérifier si une session est déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    // Si aucune session n'est démarrée, démarrer une nouvelle session
    session_start();
}

// Vérifie si le jeton CSRF est défini dans la session, sinon le génère
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

require_once(__DIR__ . '/../../Core/config.php');
require_once(__DIR__ . '/../../Controllers/DisqueController.php');
require_once(__DIR__ . '/../../Controllers/EmprunteurController.php');

// Inclure le fichier contenant la classe DisqueModel
//require_once(__DIR__ . '/../../Models/DisqueModel.php');

// Utiliser l'espace de noms correct pour DisqueModel
//use App\Models\DisqueModel;

// Récupère le titre pour l'affichage
$title = htmlspecialchars("Gestion des disques - Suppression d'un disque", ENT_QUOTES, 'UTF-8');
?>

<div class="alert alert-warning" role="alert">
    <?php if (isset($disque) && !empty($disque)) : ?>
        <p>Voulez-vous supprimer le disque : <strong><?php echo htmlspecialchars($disque->getTitre(), ENT_QUOTES, 'UTF-8'); ?></strong> ?</p>
        <!-- Formulaire de suppression avec champ caché pour le jeton CSRF -->
        <form action="#" method="POST">
            <!-- Champ caché pour l'identifiant du disque à supprimer -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($disque->getIdDisque(), ENT_QUOTES, 'UTF-8'); ?>">
            <!-- Champ caché pour le jeton CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input class="btn btn-danger" type="submit" name="confirm" value="OUI">
            <input class="btn btn-primary" type="submit" name="cancel" value="NON">
        </form>
    <?php else : ?>
        <p>Le disque à supprimer n'a pas pu être trouvé.</p>
    <?php endif; ?>
</div>