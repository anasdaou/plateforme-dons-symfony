# Plateforme de dons Symfony

## Description
Plateforme web permettant de créer et gérer des campagnes de dons, réaliser des dons et suivre les contributions.

## Technologies
- PHP 8+
- Symfony
- Doctrine ORM
- Twig
- Bootstrap 5
- MySQL / MariaDB

## Installation
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony serve
