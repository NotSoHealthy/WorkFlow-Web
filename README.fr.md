[![en](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/NotSoHealthy/WorkFlow-Web/blob/master/README.md)

# WorkFlow An HR managment website built with Symfony6.4

## Table of Contents

- [Introduction](#introduction)
- [Fonctionnalités](#fonctionnalités)
- [Exigences](#exigences)
- [Installation](#installation)
- [Utilisation](#utilisation)

## Introduction

WorkFlow est un système de gestion d'équipe moderne, léger et flexible, conçu pour aider les équipes à rationaliser leurs opérations, suivre leur progression et améliorer leur productivité.

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

  * Installer Scooper
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
  ```sh
  symfony server:start --port=443
  ```
  Accéder au site via `https://127.0.0.1/`
