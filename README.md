# <img title="WorkFlow" alt="Logo" src="https://i.imgur.com/KQGAJb0.png" width=50> WorkFlow — An HR managment website built with Symfony 6.4

[![en](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/NotSoHealthy/WorkFlow-Web/blob/master/README.md)
[![fr](https://img.shields.io/badge/lang-fr-blue.svg)](https://github.com/NotSoHealthy/WorkFlow-Web/blob/master/README.fr.md)

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Contributions](#contributions)
- [License](#license)

## Introduction

WorkFlow is a modern, lightweight, and flexible team management system designed to help teams streamline their operations, track progress, and boost productivity.
This project was developed as part of the coursework for PIDEV 3A at [Esprit School of Engineering](https://esprit.tn/)

### Built with

* [![Symfony](https://img.shields.io/badge/Symfony-black?logo=symfony)](https://symfony.com/)
* [![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)](https://www.php.net/)
* [![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)]()
* [![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)]()

## Features

- Role-based access control with Employees and Managers.
- Organize and manage users across different departments.
- Assign tasks to employees, monitor progress, and track completion in real-time.
- Managers can create and manage projects and tasks within their departments.
- Employees can enroll in company training sessions.
- Employees can sign up for company events and receive event details.
- Employees can file complaints directly to upper management.
- Employees can request leaves through a streamlined system.
- Managers can approve or deny leave requests, and oversee team activities.
- Managers can post job offers and internships for public access.
- Guest users can apply to job offers and receive interview details without needing an account.

## Requirements

- PHP 8.1.25+
- Symfony CLI version 5.11.0+
- Composer version 2.8.6+

## Installation

1. Clone the Repository
    
    ```sh
    git clone https://github.com/NotSoHealthy/WorkFlow-Web.git
    cd WorkFlow-Web
    ```
2. Install PHP

  * For windows [PHP](https://windows.php.net/download/)
  * For Linux [PHP](https://www.php.net/manual/en/install.unix.php)

3. Install Symfony CLI

  * Install Scoop
    ```sh
    Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
    Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression
    ```
  * Install Symfony CLI
    ```sh
    scoop install symfony-cli
    ```

4. Install Composer
  * Install [Composer](https://getcomposer.org/Composer-Setup.exe)

5. Install packages

  * In the project directory
    ```sh
    composer install
    ```

6. Install HTTPS certificate
    ```sh
    symfony server:ca:install
    ```

## Usage
* In the project directory
  ```sh
  symfony server:start --port=443
  ```
  Access the website through `https://127.0.0.1/`

## Contributions

We thank all those who contributed to this project !

### Contributors

The following people have contributed to this project by adding features, fixing bugs, or improving the documentation :

- [Mohamed Amin Ben Hamouda](https://github.com/NotSoHealthy) - Development of user management 
- [Fares Guermazi](https://github.com/FaresGuer) - Addition of the training management
- [Youssef Mlewhia](https://github.com/yssfmlha) - Implementation of events 
- [Amine Kerfai](https://github.com/AmineKerfai) - Development of job and internship offers 
- [Zakaria Bouchaddakh](https://github.com/zakariabouchaddakh) - Creation of the department and project management feature
- [Mahdi Hattab](https://github.com/MehdiHattab) - Addition of the employee complaints management feature

Please feel free to contribute by [submitting an issue](https://github.com/NotSoHealthy/WorkFlow-Web/issues). Each contribution helps us grow and improve.

We appreciate your support and look forward to making our product even better with your help!

## Licence

Distributed under the MIT License. See `LICENSE.txt` for more information.