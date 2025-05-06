#  <img title="WorkFlow" alt="Logo" src="https://i.imgur.com/KQGAJb0.png" width=50>  WorkFlow — Un site de gestion des ressources humaines développé avec Symfony 6.4

[![en](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/NotSoHealthy/WorkFlow-Web/blob/master/README.md)

## Table des matières

- [Introduction](#introduction)
- [Fonctionnalités](#fonctionnalités)
- [Exigences](#exigences)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contributions](#contributions)
- [License](#license)

## Introduction

WorkFlow est un système de gestion d'équipe moderne, léger et flexible, conçu pour aider les équipes à rationaliser leurs opérations, suivre leur progression et améliorer leur productivité.
Ce projet a été développé dans le cadre du cours PIDEV 3A à [ESPRIT École d'ingénieurs](https://esprit.tn/)

### Conçu avec

* [![Symfony](https://img.shields.io/badge/Symfony-black?logo=symfony)](https://symfony.com/)
* [![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)](https://www.php.net/)
* [![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)]()
* [![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)]()

## Fonctionnalités

- Contrôle d'accès basé sur les rôles avec Employés et Managers.
- Organisez et gérez les utilisateurs à travers différents départements.
- Assignez des tâches aux employés, surveillez leur avancement et suivez leur achèvement en temps réel.
- Les managers peuvent créer et gérer des projets et des tâches au sein de leurs départements.
- Les employés peuvent s'inscrire à des sessions de formation de l'entreprise.
- Les employés peuvent s'inscrire à des événements de l'entreprise et recevoir les détails des événements.
- Les employés peuvent déposer des plaintes directement à la direction.
- Les employés peuvent faire des demandes de congé via un système simplifié.
- Les managers peuvent approuver ou refuser les demandes de congé et superviser les activités de l'équipe.
- Les managers peuvent publier des offres d'emploi et des stages accessibles au public.
- Les utilisateurs invités peuvent postuler à des offres d'emploi et recevoir les détails des entretiens sans avoir besoin d'un compte.

## Exigences

- PHP 8.1.25+
- Symfony CLI version 5.11.0+
- Composer version 2.8.6+

## Installation

1. Cloner le dépôt
    
    ```sh
    git clone https://github.com/NotSoHealthy/WorkFlow-Web.git
    cd WorkFlow-Web
    ```
2. Installer PHP

  * Pour windows [PHP](https://windows.php.net/download/)
  * Pour Linux [PHP](https://www.php.net/manual/en/install.unix.php)

3. Installer Symfony CLI

  * Installer Scoop
    ```sh
    Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
    Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression
    ```
  * Installer Symfony CLI
    ```sh
    scoop install symfony-cli
    ```

4. Installer Composer
  * Installer [Composer](https://getcomposer.org/Composer-Setup.exe)

5. Installer les paquets

  * Dans le répertoire du projet
    ```sh
    composer install
    ```

6. Installer le certificat HTTPS
    ```sh
    symfony server:ca:install
    ```

## Utilisation
* Dans le répertoire du projet
  ```sh
  symfony server:start --port=443
  ```
  Accéder au site via `https://127.0.0.1/`

## Contributions

Nous remercions tous ceux qui ont contribué à ce projet !

### Contributeurs

Les personnes suivantes ont contribué à ce projet en ajoutant des fonctionnalités, en corrigeant des bugs ou en améliorant la documentation :

- [Mohamed Amin Ben Hamouda](https://github.com/NotSoHealthy) - Développement du module de gestion des utilisateurs 
- [Fares Guermazi](https://github.com/FaresGuer) - Ajout du module de gestion des formations
- [Youssef Mlewhia](https://github.com/yssfmlha) - Mise en place du module d'événements 
- [Amine Kerfai](https://github.com/AmineKerfai) - Développement du module des offres d'emploi et de stages
- [Zakaria Bouchaddakh](https://github.com/zakariabouchaddakh) - Création du module de gestion des départements et des projets
- [Mahdi Hattab](https://github.com/MehdiHattab) - Ajout du module de gestion des réclamations des employés

N'hésitez pas à contribuer en [soumettant un ticket](https://github.com/NotSoHealthy/WorkFlow-Web/issues). Chaque contribution nous aide à évoluer et à nous améliorer.

Nous vous remercions pour votre soutien et avons hâte d’améliorer notre produit grâce à votre aide !

## Licence

Distribué sous la licence MIT. Voir `LICENSE.txt` pour plus d'informations.