<?php

namespace App\Controllers;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Core\Form;
use App\Entities\Disque;
use App\Entities\Emprunt;
use App\Models\DisqueModel;
use App\Models\EmprunteurModel;
use App\Models\EmpruntModel;
use PDO;


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class DisqueController extends Controller
{
     // Méthode pour le calcul de la date de retour d'un disque
    public function afficherDateRetour($idDisque)
    {
        $empruntModel = new EmpruntModel();
        $emprunt = $empruntModel->findByDisqueId(intval($idDisque));

        if ($emprunt) {
            return htmlspecialchars($emprunt->getDateRetourPrevue());
        } else {
            return null;
        }
    }

    // Fonction pour valider le format de date (exemple)
    function validateDate($date, $format = 'Y-m-d')
    {
        $dateTimeObj = \DateTime::createFromFormat($format, $date);
        return $dateTimeObj && $dateTimeObj->format($format) === $date;
    }

    // Méthode pour afficher la liste des disques
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            session_regenerate_id();
        }

        $disques = new DisqueModel();
        $list = $disques->findAll();
        $this->render('disques/index', ['list' => $list]);
    }

    // Méthode pour ajouter un disque
    public function addDisque()
    {
        // Générer un token CSRF
        $csrfToken = bin2hex(random_bytes(32));
        // Stocker le token CSRF dans la session de l'utilisateur
        $_SESSION['csrf_token'] = $csrfToken;

        // Exemple de vérification d'authentification
        if (isset($_SESSION['user_id'])) {
            // Régénérer l'identifiant de session
            session_regenerate_id();
        }

        $emprunteursModel = new EmprunteurModel();
        $emprunteursList = $emprunteursModel->getEmprunteursList();
        $disque = new disque();
        $erreur = "";

        // Contrôle si les champs du formulaire sont remplis
        if (Form::validatePost($_POST, ['titre', 'auteur', 'genre', 'isbn'])) {

            // Hydratation de l'entité avec les données du formulaire
            $disque->setTitre(htmlspecialchars($_POST['titre']));
            $disque->setAuteur(htmlspecialchars($_POST['auteur']));
            $disque->setGenre(htmlspecialchars($_POST['genre']));
            $disque->setIsbn(htmlspecialchars($_POST['isbn']));

            // Instanciation de l'entité "disque" - Mise à jour du nom de la classe
            $disques = new disqueModel();
            $disques->create($disque);

            // Redirection vers la liste des disques
            header("Location: index.php?controller=disque&action=index");
            exit();
        } else {
            // Affichage d'un message d'erreur si le formulaire n'a pas été correctement rempli
            $erreur = !empty($_POST) ? "Le formulaire n'a pas été correctement rempli" : "";
        }

        // Construction du formulaire d'ajout
        $form = new Form();
        $form->startForm("#", "POST");
        // Ajout du token CSRF au formulaire
        $form->addInput("hidden", "csrf_token", ["value" => $csrfToken]);
        $form->addLabel("titre", "Titre", ["class" => "form-label"]);
        $form->addInput("text", "titre", ["id" => "titre", "class" => "form-control", "placeholder" => "Titre"]);
        $form->addLabel("auteur", "Auteur", ["class" => "form-label"]);
        $form->addInput("text", "auteur", ["id" => "auteur", "class" => "form-control", "placeholder" => "Auteur"]);
        $form->addLabel("genre", "Genre", ["class" => "form-label"]);
        $form->addInput("text", "genre", ["id" => "genre", "class" => "form-control", "placeholder" => "Genre"]);
        $form->addLabel("isbn", "ISBN", ["class" => "form-label"]);
        $form->addInput("text", "isbn", ["id" => "isbn", "class" => "form-control", "placeholder" => "ISBN"]);
        $form->addInput("submit", "add", ["value" => "Ajouter", "class" => "btn btn-primary"]);
        $form->endForm();

        // Envoi du formulaire dans la vue adddisque.php
        $this->render('disques/addDisque', ["addForm" => $form->getFormElements(), "erreur" => $erreur]);
    }


    // Méthode pour afficher les détails d'un disque
    public function showDisque()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id === 0) {
            echo "Identifiant du disque invalide.";
            return;
        }

        $disques = new disqueModel();
        $disque = $disques->find($id);

        if (!$disque) {
            echo "Le disque avec l'identifiant $id n'existe pas.";
            return;
        }

        $dateRetourPrevue = $this->afficherDateRetour($id);
        $this->render('disques/showDisque', ['disque' => $disque, 'dateRetourPrevue' => $dateRetourPrevue]);
    }

    // Méthode pour la mise à jour d'un disque
    public function updateDisque($id)
    {
        // Déclaration de la variable $updateForm
        $updateForm = null;

        // Génération d'un jeton CSRF
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Vérification de l'ID du disque
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Instanciation du modèle disque
        $disqueModel = new disqueModel();

        // Chargement des informations du disque à mettre à jour
        $disque = $disqueModel->find($id);

        $date_emprunt = htmlspecialchars($disque->getDateEmprunt(), ENT_QUOTES, 'UTF-8');
        $date_retour_prevue = htmlspecialchars($disque->getDateRetourPrevue(), ENT_QUOTES, 'UTF-8');

        // Initialisation du message d'erreur - de la variable $erreur
        $erreur = "";

        // Initialisation de la variable $id_emprunteur
        $id_emprunteur = isset($_POST['id_emprunteur']) ? intval($_POST['id_emprunteur']) : null;

        // Validation du formulaire
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Récupération des données de formulaire
            $date_emprunt = !empty($_POST['date_emprunt']) ? $_POST['date_emprunt'] : null;
            $date_retour_prevue = !empty($_POST['date_retour_prevue']) ? $_POST['date_retour_prevue'] : null;

            // Nettoyage des données POST
            $_POST = array_map('htmlspecialchars', $_POST);

            // Vérification des champs requis
            if (Form::validatePost($_POST, ['titre', 'auteur', 'genre', 'isbn'])) {

                // Création d'un nouvel objet disque
                $disque = new disque();

                // Mise à jour des champs du disque
                $disque->setTitre(htmlspecialchars($_POST['titre']));
                $disque->setAuteur(htmlspecialchars($_POST['auteur']));
                $disque->setGenre(htmlspecialchars($_POST['genre']));
                $disque->setIsbn(htmlspecialchars($_POST['isbn']));

                // Validation des dates si elles ne sont pas vides
                if (!empty($date_emprunt) && !empty($date_retour_prevue)) {
                    if ($this->validateDate($date_emprunt) && $this->validateDate($date_retour_prevue)) {
                        $disque->setDateEmprunt($date_emprunt);
                        $disque->setDateRetourPrevue($date_retour_prevue);
                        $disque->setIdEmprunteur($id_emprunteur);
                    } else {
                        // Gestion des dates invalides
                        $erreur = "Les dates saisies ne sont pas valides. Veuillez saisir des dates au format YYYY-MM-DD.";

                        // Récupération des emprunteurs pour le champ de sélection
                        $emprunteursModel = new EmprunteurModel();
                        $emprunteursList = $emprunteursModel->getEmprunteursList();
                        $updateForm = $this->createUpdateForm($disque, $csrfToken, $emprunteursList, $erreur, $date_emprunt, $date_retour_prevue);

                        // Rendu du formulaire avec les erreurs
                        $this->render('disques/updateDisque', ['disque' => $disque, 'emprunteursList' => $emprunteursList, 'updateForm' => $updateForm, 'erreur' => $erreur, 'date_emprunt' => $date_emprunt, 'date_retour_prevue' => $date_retour_prevue]);
                        // Arrête l'exécution de la méthode
                        return;
                    }
                } else {
                    // Si les dates sont vides, réinitialiser les valeurs dans le disque
                    $disque->setDateEmprunt(null);
                    $disque->setDateRetourPrevue(null);
                }

                // Si le disque est rendu et disponible, les champs de date et d'emprunteur peuvent être vides
                if ($id_emprunteur === null) {
                    $date_emprunt = null;
                    $date_retour_prevue = null;
                }

                // Mise à jour du disque dans la base de données
                try {
                    // Mettre à jour la disponibilité du disque en fonction des dates d'emprunt et de retour prévues
                    if (!empty($date_emprunt) && !empty($date_retour_prevue)) {
                        // Si le disque est emprunté, définissez sa disponibilité sur 0
                        $disque->setDisponibilite(0);
                    } else {
                        // Sinon, définissez-la sur 1
                        $disque->setDisponibilite(1);
                    }

                    // Enregistrement du disque mis à jour dans la base de données
                    $disqueModel->update($id, $disque);

                    header("Location: index.php?controller=disque&action=index");
                    exit();
                } catch (\PDOException $e) {
                    $erreur = "Erreur lors de la mise à jour du disque : " . $e->getMessage();
                }
            } else {
                $erreur = !empty($_POST) ? "Le formulaire n'a pas été correctement rempli" : "";
            }
        }

        // Préparation des emprunteurs pour le champ de sélection
        $emprunteursModel = new EmprunteurModel();
        $emprunteursList = $emprunteursModel->getEmprunteursList();

        // Création du formulaire de mise à jour
        $updateForm = $this->createUpdateForm($disque, $csrfToken, $emprunteursList, $erreur, $date_emprunt, $date_retour_prevue);

        // Ajout des valeurs des champs de date d'emprunt et de retour prévue à la vue
        $this->render('disques/updateDisque', ["updateForm" => $updateForm, "erreur" => $erreur, "date_emprunt" => $disque->getDateEmprunt(), "date_retour_prevue" => $disque->getDateRetourPrevue()]);
        //  }
    }
    // Méthode pour créer le formulaire de mise à jour
    private function createUpdateForm($disque, $csrfToken, $emprunteursList, $erreur, $date_emprunt, $date_retour_prevue)
    {
        $updateForm = new Form();
        $updateForm->startForm();

        // Ajout du token CSRF
        $updateForm->addInput("hidden", "csrf_token", ["value" => $csrfToken]);

        $updateForm->addLabel("titre", "Titre", ["class" => "form-label"]);
        $updateForm->addInput("text", "titre", ["id" => "titre", "class" => "form-control", "placeholder" => "Titre", "value" => htmlspecialchars($disque->getTitre())]);
        $updateForm->addLabel("auteur", "Auteur", ["class" => "form-label"]);
        $updateForm->addInput("text", "auteur", ["id" => "auteur", "class" => "form-control", "placeholder" => "Auteur", "value" => htmlspecialchars($disque->getAuteur())]);
        $updateForm->addLabel("genre", "Genre", ["class" => "form-label"]);
        $updateForm->addInput("text", "genre", ["id" => "genre", "class" => "form-control", "placeholder" => "Genre", "value" => htmlspecialchars($disque->getGenre())]);
        $updateForm->addLabel("isbn", "ISBN", ["class" => "form-label"]);
        $updateForm->addInput("text", "isbn", ["id" => "isbn", "class" => "form-control", "placeholder" => "ISBN", "value" => htmlspecialchars($disque->getIsbn())]);

        // Ajout des champs de date d'emprunt et de date de retour prévue
        $updateForm->addLabel("date_emprunt", "Date d'emprunt", ["class" => "form-label"]);
        $updateForm->addInput("date", "date_emprunt", ["id" => "date_emprunt", "class" => "form-control", "value" => htmlspecialchars($date_emprunt)]);
        $updateForm->addLabel("date_retour_prevue", "Date de retour prévue", ["class" => "form-label"]);
        $updateForm->addInput("date", "date_retour_prevue", ["id" => "date_retour_prevue", "class" => "form-control", "value" => htmlspecialchars($date_retour_prevue)]);


        // Ajout du champ pour l'emprunteur avec l'option "néant"
        $updateForm->addLabel("id_emprunteur", "Emprunteur", ["class" => "form-label"]);
        $updateForm->addSelect("id_emprunteur", ['' => 'Néant'] + $emprunteursList, ["class" => "form-select", "value" => htmlspecialchars($disque->getIdEmprunteur())]);

        // Ajout du champ caché pour la disponibilité
        $updateForm->addInput("hidden", "disponibilite", ["id" => "disponibilite", "value" => htmlspecialchars($disque->getDisponibilite(), ENT_QUOTES, 'UTF-8')]);

        // Bouton de soumission
        $updateForm->addInput("submit", "update", ["value" => "Mettre à jour", "class" => "btn btn-primary", "name" => "updateButton1"]);

        $updateForm->endForm();

        return $updateForm;
    }

    // Méthode pour supprimer un disque
    public function deleteDisque($id)
    {
        // Vérification du jeton CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            // Gérer l'erreur CSRF ici
            // Par exemple, afficher un message d'erreur et rediriger l'utilisateur
            $error_message = "Erreur CSRF : Token CSRF invalide.";
            $_SESSION['error'] = $error_message;
            header("Location: index.php?controller=disque&action=index");
            exit();
        }
        // Exemple de vérification d'authentification
        if (isset($_SESSION['user_id'])) {
            session_regenerate_id();
        }
        // Convertir l'identifiant en entier
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        // On récupère le disque avec la méthode find()
        $disques = new disqueModel();
        $disque = $disques->find($id);

        // Vérifie si le disque a été trouvé
        if (!$disque) {
            // Gérer l'erreur ici, par exemple, rediriger vers une page d'erreur
            $error_message = "Le disque avec l'identifiant $id n'a pas été trouvé.";
            $_SESSION['error'] = $error_message;
            header("Location: index.php?controller=disque&action=index");
            exit();
        }
        // Logique de suppression si l'utilisateur confirme la suppression
        if (isset($_POST['confirm'])) {
            // On instancie la classe disqueModel pour exécuter la suppression avec la méthode delete()
            // en récupérant l'id du disque du lien
            $disques = new disqueModel();
            $disques->delete($id);
            // On redirige l'utilisateur vers la liste des disques
            header("Location: index.php?controller=disque&action=index");
            exit();
        } elseif (isset($_POST['cancel'])) {
            // Redirection si l'utilisateur annule la suppression, on redirige l'utilisateur vers la liste des disques
            header("Location: index.php?controller=disque&action=index");
            exit();
        }
        // On renvoie vers la vue le disque sélectionné avec la variable $disque définie
        $this->render('disques/deleteDisque', ["disque" => $disque]);
    }
    // Méthode pour créer un nouvel emprunt
    public function createEmprunt($idEmprunteur, $iddisque, $dateEmprunt, $dateRetourPrevue)
    {
        $empruntModel = new EmpruntModel();

        // Vérifiez si le disque est déjà emprunté
        if ($empruntModel->isdisqueEmprunte($iddisque)) {
            // Gérer l'erreur (par exemple, afficher un message d'erreur à l'utilisateur)
            $this->render('disques/createEmprunt', ['error' => 'Le disque est déjà emprunté.']);
            return;
        }

        // Créer un nouvel objet Emprunt
        $emprunt = new Emprunt();
        $emprunt->setIdEmprunteur($idEmprunteur);
        $emprunt->setIddisque($iddisque);
        $emprunt->setDateEmprunt($dateEmprunt);
        $emprunt->setDateRetourPrevue($dateRetourPrevue);

        // Procéder à l'emprunt
        $empruntModel->create($emprunt);

        // Rediriger ou afficher une vue de succès
        $this->render('emprunts/addEmprunt', ['success' => 'Emprunt créé avec succès.']);
    }
}
