# WorkFlow An HR managment website built with Symfony6.4

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## Introduction

WorkFlow is a modern, lightweight, and flexible team management system designed to help teams streamline their operations, track progress, and boost productivity.

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
  ```sh
  symfony server:start --port=443
  ```
  Access the website through `https://127.0.0.1/`
