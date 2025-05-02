# GestionDisquesVinyles

Exercice / projet dans le cadre de ma formation.

Gestion d'utilisateurs, d'emprunteurs, d'emprunts et de disques vinyles en MVC.

Les fichiers de connections à la base de données (/Core/Dbconnect et connect) sont anonymisés. Des liens ont été modifiés (et sont donc incorrects).


Enoncé :

Vous devez développer une solution de gestion d'emprunt de disques vinyles. 

Cette solution doit comporter les fonctionnalités suivantes (Les principales fonctions de l'application) :

1) Faciliter l'emprunt des disques : prévoir un système d'inscription de l'emprunteur
2) Limiter l'emprunt des disques : un utilisateur peut emprunter 3 disques maximum, un utilisateur peut emprunter les disques pendant 3 semaines maximum
3) Faciliter la gestion d'emprunt pour le/la gestionnaire (boutique, disquaire, bibliothèque ou autre) : créer une interface gestionnaire
   
Nous vous demandons donc de :

- Faire le MCD, le MLD et le MPD à partir du logiciel jMerise
- Importer les tables ainsi produites dans votre base de données.
- Faire un diagramme de cas d'utilisation (user case) à partir du logiciel de votre choix (Modelio, ArgoUML...)
- Développer le CRUD (Create, Read, Update, Delete) pour chaque entité : personne emprunteur et disque en intégrant les recommandations de sécurité web.
- Sécurité et Validation des données :
Assurez-vous que votre application gère correctement la validation des entrées utilisateur pour éviter les injections SQL et les autres vulnérabilités de sécurité.
Utilisez des préparations de requêtes SQL et validez toutes les entrées utilisateur avant de les traiter.

=> Cross-Site Scripting (XSS), Cross-Site Request Forgery (CSRF), Détournement de session, Injection SQL, Upload, Include et stockage du mot de passe :
Voici une liste des fichiers concernés par chaque mesure de sécurité, avec des exemples de codes à intégrer :

Cross-Site Scripting (XSS) :

Fichiers : Toutes les vues (par exemple, base.php, index.php, etc.).

Exemple de code : Utilisez htmlspecialchars() pour échapper les données avant de les afficher dans les vues.

Cross-Site Request Forgery (CSRF) :

Fichiers : Tous les fichiers de formulaire (par exemple, add.php, updateCreation.php, etc.) et les contrôleurs correspondants.

Exemple de code : Générez et incluez un jeton CSRF dans les formulaires, puis vérifiez-le lors de la soumission du formulaire dans le contrôleur.

Détournement de session :

Fichiers : Tous les contrôleurs et les fichiers de configuration de session (par exemple, index.php).

Exemple de code : Utilisez des cookies sécurisés et régénérez l'identifiant de session après une connexion réussie.

Injection SQL :

Fichiers : Tous les contrôleurs et les fichiers de modèle (par exemple, CreationModel.php).

Exemple de code : Utilisez des requêtes préparées pour toutes les interactions avec la base de données.

Upload :

Fichiers : Contrôleurs de gestion des téléchargements de fichiers (par exemple, uploadController.php).

Exemple de code : Validez le type et la taille des fichiers uploadés avant de les traiter.

Include :

Fichiers : Tous les fichiers qui incluent d'autres fichiers (par exemple, Router.php, Autoloader.php).

Exemple de code : Utilisez des chemins absolus pour inclure des fichiers et restreignez l'accès aux fichiers sensibles.

Stockage du mot de passe en clair :

Fichiers : Contrôleurs et modèles liés à la gestion des utilisateurs et des authentifications (par exemple, UserController.php, User.php).

Exemple de code : Utilisez une fonction de hachage sécurisée comme bcrypt pour stocker les mots de passe.


  
L'application devra impérativement être développée en PHP Objet et PHP5. Elle devra respecter le design patern MVC (Model View Controler)...


Impératifs de l'exercice : 

 - Mise en place les bases d'une application MVC pour la gestion d'utilisateurs et de disques vinyles (CRUD).
 - Mise en place des mesures de sécurité dans cette application
 - Impératifs : 3 emprunts par utilisateur pour une durée de 3 semaines.
 - La gestion d'utilisateurs est faites afin de permettre la gestion des 3 entitées (disque, emprunteur et emprunt) et concerne donc un gestionnaire principal (entreprise de location ou autre) et peut être multi-utilisateurs
 - Les 3 tables (Disque, Emprunteur et Emprunt) sont croisées via SQL afin de facilité les recherches...

Les fichiers de connections à la base de données (/Core/Dbconnect et connect) sont anonymisés. Des liens ont été modifiés (et sont donc incorrects).

Voici l'arborescence  :  

- dossier Controllers (avec à l'intérieur : fichiers Controller.php (à la place de MainController.php), UserController.php, EmprunteurController.php,DisqueController, HomeController.php), 
- dossier Core (avec à l'intérieur fichiers DbConnect.php, Form.php, Router.php, Auth.php, Connect.php, Register.php, Logout.php), 
- dossier Entities (avec à l'intérieur fichier CreationDisque.php et CreationEmprunteur.php), 
- dossier Models (avec à l'intérieur fichiers UserModel.php, EmprunteurModel.php, DisqueModel.php), 
- dossier public (avec à l'intérieur fichier index.php, style.css, sous-dossier images et dans ce sous-dossier des images format jpg et webp), 
- dossier Views avec le fichier base.php, avec :
	- un sous-dossier emprunteur (et dans ce dossier creation les fichiers suivants : addEmprunteur.php, editEmprunteur.php,.deleteEmprunteur.php, index.php, showEmprunteur.php et updateEmprunteur.php) 
	- un sous-dossier disques (et dans ce dossier creation les fichiers suivants : addDisque.php, editDisque.php,.deleteDisque.php, index.php, showDisque.php et updateDisque.php)
	- un sous-dossier "home" (toujours dans le dossier "Views") avec le fichier index.php et pour finir, 
- à la racine du projet, les fichiers Autoloader.php et index.php

Et maintenant les tables de la base de données : 

- table Disque (cette table stocke les informations sur les disques disponibles à la bibliothèque) :
	id_disque INT AUTO_INCREMENT NOT NULL,
   	titre VARCHAR(255) NOT NULL,
    	auteur VARCHAR(255) NOT NULL,
    	genre VARCHAR(255) NOT NULL,
    	isbn VARCHAR(20) NOT NULL,
    	disponibilite TINYINT NOT NULL,
    	date_emprunt DATE,
    	date_retour_prevue DATE,
    	id_emprunteur INT,
    	PRIMARY KEY (id_livre),
    	CONSTRAINT FK_Disque_Emprunteur FOREIGN KEY (id_emprunteur) REFERENCES Emprunteur(id_emprunteur),
    	CONSTRAINT Check_Date_Retour CHECK (date_retour_prevue <= DATE_ADD(date_emprunt, INTERVAL 3 WEEK))

- table Emprunteur (cette table contient les informations sur les personnes emprunteuses inscrites) :
	id_emprunteur INT AUTO_INCREMENT NOT NULL,
    	nom VARCHAR(255) NOT NULL,
    	prenom VARCHAR(255) NOT NULL,
    	date_naissance DATE NOT NULL,
    	adresse VARCHAR(255) NOT NULL,
    	numero_telephone VARCHAR(10) NOT NULL,
    	email VARCHAR(50) NOT NULL,
    	date_inscription DATE NOT NULL,
    	PRIMARY KEY (id_emprunteur)

- table Emprunt (cette table enregistre les informations sur les emprunts de disques) :
	id_emprunt INT AUTO_INCREMENT NOT NULL,
    	id_emprunteur INT NOT NULL,
    	id_disque INT NOT NULL,
    	date_emprunt DATE NOT NULL,
    	date_retour_prevue DATE NOT NULL,
    	date_retour_effectif DATE,
    	PRIMARY KEY (id_emprunt),
    	FOREIGN KEY (id_emprunteur) REFERENCES Emprunteur(id_emprunteur),
    	FOREIGN KEY (id_disque) REFERENCES Livre(id_livre)

-table users (cette table contient les informations de connexion des utilisateurs du logiciel) :
	id_users INT AUTO_INCREMENT NOT NULL,
    	email VARCHAR(255) NOT NULL,
   	 password VARCHAR(255) NOT NULL,
    	username VARCHAR(255) NOT NULL,
    	PRIMARY KEY (id_users)


Rôles des fichiers :

- Le routeur (Router.php) va récupérer l'URL demandée par l'utilisateur. Nous allons donc définir une structure type des URLs.

Ces URLs vont contenir comme paramètres, le nom du contrôleur et le nom de la méthode à exécuter. 
Elles seront donc formatées ainsi : https://monsite.com/index.php?controller=nomDuControleur&action=nomDeLaMethode. exemple : http://localhost/public/index.php?controller=home&action=index

Le routeur va donc être une classe que l'on va créer à l'intérieur du dossier "Core".
Il est le point d'entrée de l'application, on va donc l'instancier dans un fichier appelé "index.php" dans le dossier "public".

Les contrôleurs contiennent tout le code de fonctionnement des pages de l'application en réceptionnant les informations demandées au modèle, pour ensuite les proposer à la vue. 
Ou alors, en proposant des valeurs directement au modèle pour ensuite les traités dans la base de données. 
Un contrôleur est composé de méthodes, qui elles-mêmes représentent une action exécutée sur une des pages de l'application. 

Pour commencer, nous allons créer un contrôleur mère pour profiter de l'abstraction et de l'héritage de la POO. Nous allons ainsi mettre les méthodes communes à tous les contrôleurs. 

Ensuite, nous allons utiliser un contrôleur pour la page d'accueil, puis un autre pour les pages des créations des emprunteurs, puis un autre pour les pages des créations des disques et puis un autre pour les pages des créations des utilisateurs (utilisateurs du logiciel).
Le modèle sera représenté dans la structure MVC en deux parties : un représentant une entité de la base de données et l'autre exécutant les requêtes en direction de la base de données. 

Dans certains cas, il retournera pour le contrôleur concerné, les informations récupérées en base de données. Il sera donc représenté par deux classes distinctes.
Pour qu'il puisse réaliser ces actions, il devra en amont être connecté à la base de données. 
Pour cela, nous allons créer une classe appelée "DbConnect.php", stockée dans le dossier "Core", qui va nous servir de connexion à la base de données de l'application. 
Elle sera une classe mère pour le modèle. Les requêtes seront mises en place dans des classes appelées : "UserModel.php" pour les utilisateurs, EmprunteurModel.php pour les emprunteurs, DisqueModel.php pour les disques, stockées dans le dossier "Models". 

Une autre classe "connect" servira elle aussi de connexion à la base de données mais pour les users (utilisateurs du logiciel).

Dans notre exemple, nous allons créer deux seule entités qui représenteront les tables "Disque" et "Emprunteur". Nous allons donc créer deux nouvelles classes appelées "CreationEmprunteur.php" et "CreationDisque.php" et elles seront stockées dans le dossier "Entities". 

La vue va récupérer les informations envoyées par les contrôleurs pour les afficher au milieu d'une structure HTML. 
Nous utiliserons tout de même le langage PHP, par exemple pour boucler sur la liste des créations de disques ou d'emprunteurs.
Une vue représente une page, nous allons donc créer un fichier par vue et chacun des fichiers sera stocké dans un dossier. 
Pour la page d'accueil de l'application, nous allons créer dans le dossier "Views" un dossier appelé "home" reprenant par convention le préfixe du contrôleur "HomeController", et nous allons y créer à l'intérieur un fichier nommé "index.php" reprenant le nom de la méthode exécutée. 
Ensuite pour la page listant les disques, nous allons faire la même chose, c'est-à-dire créer un dossier appelé "Disques", dans lequel un fichier "index.php" sera créé et pareil pour les emprunteurs : la page listant les emprunteurs, nous allons créer un dossier appelé "Emprunteurs", dans lequel un fichier "index.php" sera créé 

Pour exploiter cette arborescence, nous allons donc créer une méthode appelée "render()" dans le contrôleur principal "Controller.php". 
Elle va permettre de sélectionner la vue que l'on souhaite pour y envoyer des informations. Elle sera exécutée dans chacun des contrôleurs. 

Le templating est un élément essentiel aux bonnes pratiques de développement. Il permet d'éviter de répéter son code le rendant plus lisible. 
Son rôle est d'afficher la structure principale de l'application sur toutes les pages, comme l'en-tête, le pied de page... 

Dans la structure MVC, le templating fait partie de la vue, nous allons donc, tout comme les vues créées précédemment, utiliser le langage HTML pour la structure et le langage PHP qui est lui-même un langage de templating. 

Nous allons créer dans le dossier "Views" un fichier appelé "base.php". Ce fichier sera donc le template ou gabarit de nos pages. 
Ensuite, pour afficher notre template, nous allons modifier la méthode "render()". 

Dans notre application, nous allons avoir besoin d'utiliser des formulaires pour ajouter, modifier, voir, supprimer des disques et des emprunteurs. 
Nous allons mettre en place un générateur de formulaire à l'aide d'une nouvelle classe. Ainsi, nous pourrons créer autant de formulaires que l'on souhaite en utilisant le même code. 
Le formulaire créé sera donc représenté en tant qu'objet.
Dans le dossier "Core" de l'application, nous allons créer une classe appelée "Form". Cette classe permettra l'ajout des champs, générer le corps du formulaire et vérifier la saisit dans les champs. 
Le formulaire sera construit dans un contrôleur, pour être ensuite envoyé à une vue.

Nous allons créer les vues "addDisque.php" et "addEmprunteur.php" dans le dossier "creation" et leurs sous-dossiers respectifs (emprunteurs et disques) pour afficher le formulaire. 
Enfin, l'utilisateur (de l'application) devra pouvoir ajouter un disque ou un emprunteur : nous allons traiter le formulaire que nous avons construit juste avant pour nous permettre d'ajouter un disque et/ou un emprunteur dans la table création. 
Pour cela nous allons tout d'abord tester les champs s'ils sont vides et si ce n'est pas le cas, nous allons "hydrater" (ajouter des valeurs dans chaque propriété de la classe "Creation") notre objet par les données reçues du formulaire. 
Avant cela, nous allons compléter nos liens dans le menu afin d'accéder aux différentes pages créées en modifiant le fichier "base.php". 
Nous allons également ajouter un bouton sur la vue "index.php" dans le dossier "creation", pour accéder au formulaire d'ajout.
Pour le traitement du formulaire d'ajout, nous nous dirigerons vers le fichier "CreationController.php" afin de compléter la méthode "add()". 
Ensuite l'utilisateur pourra afficher les disques et/ou les emprunteurs : nous allons traiter l'affichage de la liste des disques et des emprunteurs. 
Une fois le formulaire d'ajout mis en place permettant d'ajouter en base de données des disques et/ou des emprunteurs, nous allons remplacer notre tableau saisi en début de développement dans le contrôleur "DisqueController.php" et/ou "EmprunteurController.php" par l'exécution de la méthode "findAll()" déclarée dans les classes modèles "DisqueController.php" et/ou "EmprunteurController.php" et qui permettent d'aller récupérer les informations des disques et emprunteurs dans la base de données. 

Ensuite, nous allons modifier la vue "index.php" dans les sous-dossiers "emprunteurs et disques" pour correctement afficher la liste des disques et emprunteurs dans le tableau. 
Toujours dans ces fichiers, nous allons gérer/créer une feuille de style : cette feuille de styles s'appellera "style.css" et sera créée dans le dossier "public" et le sous-dossier css, à la racine. 
Nous allons donc ajouter le lien de la feuille de style dans le template "base.php". Il pourra aussi afficher les informations des disques et emprunteurs : pour cela nous allons modifier les fichiers "index" dans les dossiers "disques" et "emprunteurs" en ajoutant un lien sous forme d'une icône dans le tableau afin de rediriger l'utilisateur (de l'application) vers la page affichant les informations choisies. 

Nous allons utiliser les icônes "Font Awesome" et ajouter le CDN dans le fichier "base.php". 
Pour respecter la structure MVC, nous allons créer une nouvelle méthode dans le contrôleur "DisqueController.php" et "EmprunteurController.php" appelée "showDisque()" et "showEmprunteur()". 
Pour afficher les informations sélectionnées, nous allons créer une nouvelle vue dans le dossier "creation", appelée "showDisque.php" et "showemprunteur.php". 

L'utilisateur (de l'application) doit pouvoir ensuite mettre à jour ces informations : nous allons traiter la mise à jour de celles-ci. Pour cela nous allons modifier le fichier "index" dans les dossiers "disques" et "emprunteurs" en ajoutant un lien sous forme d'une icône dans le tableau pour rediriger l'utilisateur vers la page affichant le formulaire de modification des disques et/ou emprunteurs sélectionnés. 
Nous allons créer une nouvelle méthode dans le contrôleur "DisquesController.php" et "EmprunteurController.php" appelée "updateDisque()" et "updateEmprunteur()" pour traiter les informations. 
Pour afficher le formulaire de mise à jour, nous allons créer une nouvelle vue dans les dossiers "disques" et "emprunteurs", appelée "updateDisque.php" et "UpdateEmprunteur.php". 

Et pour terminer, l'utilisateur doit pouvoir supprimer un disque ou un emprunteur : nous allons traiter la suppression de ces informations. 
Pour cela nous allons modifier le fichier "index" dans les dossiers "disques" et "emprunteurs" en ajoutant un lien sous forme d'une icône dans le tableau pour rediriger l'utilisateur vers la page affichant un message demandant une confirmation de suppression. 
Nous allons créer une nouvelle méthode dans le contrôleur "DisqueController.php" et "Emprunteur.php appelée "deleteDisque()" et "deleteEmprunteur()" pour traiter les informations. 
Pour afficher le message de confirmation, nous allons créer une nouvelle vue dans le dossier "disques" et le dossier "emprunteurs", appelée "deleteDisque()" et "deleteEmprunteur()".  

Pour la gestion des users (de l'application) :
- un controlleur est crée dans le dossier Controller : UserController.php : il contiend la logique de gestion des utilisateurs.
Dans le contrôleur "UserController.php", création d' une classe appelée "UserController" qui étendra la classe de contrôleur principale. 
Cette classe a des méthodes pour gérer l'inscription, la connexion, la déconnexion, la récupération des informations de l'utilisateur, etc.
Un modèle de gestion a été mis en place : création d'une classe "UserModel".
Cette classe est responsable de l'interaction avec la base de données pour gérer les utilisateurs, telles que l'inscription, la vérification des informations d'identification, etc.
Dans le dossier Models, un fichier "UserModel.php" qui contiend la classe "UserModel" a été fait.
Dans la vue, création des fichiers pour afficher les formulaires d'inscription (register.php) et de connexion (login.php), ainsi que les messages de confirmation ou d'erreur liés à ces opérations.
Dans le contrôleur "UserController", implémentation des méthodes nécessaires pour gérer l'inscription, la connexion, la déconnexion, etc., en utilisant les fonctionnalités fournies par le modèle "UserModel".
Dans les vues, mide en place les formulaires d'inscription et de connexion, ainsi que de la gestion de l'affichage des messages de confirmation ou d'erreur.

Le fichier index à la racine de l'application invite l' utilisateur de l'application à se connecter pour acceder à la gestion, ou alors à s'inscrire...
