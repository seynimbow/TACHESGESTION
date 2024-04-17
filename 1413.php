<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$serveur = "localhost";
$utilisateur_db = "root";
$mot_de_passe_db = "root";
$base_de_donnees = "ehitec";

try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$base_de_donnees", $utilisateur_db, $mot_de_passe_db);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour récupérer les tâches non archivées avec les commentaires et la priorité
    $sql = "SELECT taches.id, date_echeance, taches.nom_tache, taches.avancement, travailleurs.username, taches.commentaire, taches.date_creation, priorites.nom_priorite
            FROM taches
            LEFT JOIN travailleurs ON taches.worker_id = travailleurs.id
            LEFT JOIN taches_priorites ON taches.id = taches_priorites.tache_id
            LEFT JOIN priorites ON taches_priorites.priorite_id = priorites.id
            WHERE taches.archived = 0
            ORDER BY taches.date_creation DESC";  // Modification de l'ordre décroissant

    $result = $connexion->query($sql);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Tâches</title>
    <link rel="stylesheet" type="text/css" href="gestion_taches.css">
    <script src="confirm.js"></script>
</head>

<body>

<header>
    <div>
        <h2>Bienvenue, <?php echo $_SESSION["username"]; ?>!</h2>

        <div class="p-plateau">
            <p class="plateau-title">PLATEAU 1413</p>
        </div>

        <nav id="menu" class="fixed-menu">
            <p>
                <a href="ajouter_tache.php">AJOUTER UNE NOUVELLE TACHE</a>
                <a href="afficher_tache_archive.php">AFFICHER LES TACHES ARCHIVEES</a>
                <a href="restaurer_tache.php">RESTAURER LES TACHES ARCHIVEES</a>
            </p>
        </nav>
    </div>

    <div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <nav id="menu" class="fixed-menu">
            <p><a href="gestion_taches.php">Retour au choix du plateau</a></p>
        </nav>
    </div>
</header>

<main>

    <?php
    if ($result->rowCount() > 0) {
        echo '<table class="task-table" id="mon_table">';
        echo '<thead>';
        echo '<tr><th>Tâche</th><th>Avancement</th><th>Date d\'échéance</th><th>Travailleur</th><th>Commentaires</th><th>Statut</th><th>Date de Création</th><th>Priorité</th><th>Pièce jointe</th><th>Actions</th></tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td>' . $row["nom_tache"] . '</td>';
            echo '<td>' . $row["avancement"] . '%</td>';
            echo '<td>' . $row["date_echeance"] . '</td>';
            echo '<td>' . $row["username"] . '</td>';
            $commentaire = isset($row["commentaire"]) ? $row["commentaire"] : "";
            echo '<td>' . $commentaire . '</td>';
            echo '<td>' . ($row["avancement"] == 100 ? 'Terminé' : 'En cours') . '</td>';
            echo '<td>' . $row["date_creation"] . '</td>';
            echo '<td>' . $row["nom_priorite"] . '</td>';
            echo '<td>';
            // Formulaire pour sélectionner un fichier
            echo '<form action="upload_excel.php" method="post" enctype="multipart/form-data">';
            echo '<input type="hidden" name="task_id" value="' . $row["id"] . '">';
            echo '<input type="file" name="excel_file">';
            echo '</form>';
            echo '</td>';
            echo '<td>
                    <button class="button-link" onclick="window.location.href=\'modifier_tache.php?edit_id=' . $row["id"] . '\'">Modifier</button> 
                    <button class="button-link" onclick="if(confirmDelete()) window.location.href=\'supprimer_tache.php?delete_id=' . $row["id"] . '\'">Supprimer</button>
                    <button class="button-link" onclick="if(confirmArchive()) window.location.href=\'archiver_tache.php?archive_id=' . $row["id"] . '\'">Archiver</button>
                  </td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'Aucune tâche trouvée.';
    }
    ?>

</main>

<div class="footer">
    <p><a href="logout.php">Se déconnecter</a></p>
</div>

</body>
</html>
