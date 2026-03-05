# wellora

## Overview
*This project was developed as part of the PIDEV -3A33 at **Esprit School of Engineering** (Academic Year 2025-2026).*

## Features
### 👥 Module Utilisateurs (Med Taher Zeidi)
- [ ] Inscription avec email et mot de passe
- [ ] Connexion sécurisée
- [ ] Gestion des rôles : Patient / Coach / Admin
- [ ] Modification du profil utilisateur
- [ ] Tableau de bord personnalisé

### 🩺 Module Consultations (Mariem Fakhfakh)
- [ ] Prise de rendez-vous en ligne
- [ ] Calendrier interactif des disponibilités
- [ ] Historique des consultations
- [ ] Notifications de rappel (email)
- [ ] Upload et téléchargement de documents médicaux

### 🏋️ Module Plans Sportifs (Chahd Maaloul)
- [ ] Création de programmes sportifs personnalisés
- [ ] Liste d'exercices avec séries, répétitions, repos
- [ ] Suivi de progression avec graphiques
- [ ] Intégration de vidéos explicatives
- [ ] Adaptation automatique selon les performances

### 🌿 Module Parcours de Santé (Ahmed Regaieg)
- [ ] Création de programmes bien-être
- [ ] Suivi des indicateurs santé (poids, tension, glycémie)
- [ ] Journal quotidien (humeur, sommeil, activité)
- [ ] Alertes personnalisées
- [ ] Génération de rapports d'évolution

### 🥗 Module Nutrition (Hamza Najjar)
- [ ] Création de plans alimentaires personnalisés
- [ ] Base de données d'aliments (calories, protéines, glucides, lipides)
- [ ] Calcul automatique des apports nutritionnels
- [ ] Génération de listes de courses
- [ ] Suggestions de recettes adaptées

### 📊 Dashboard Global
- [ ] Vue d'ensemble de tous les modules
- [ ] Graphiques de progression interactive
- [ ] Objectifs quotidiens/hebdomadaires
- [ ] Notifications centralisées


## Tech Stack
- **Frontend**:tailwindcss
- **Backend**: Symfony 
- **Database**: Mysgl
- **Architecture**: MVC

## Contributors
- [Mariem fakhfakh] - [gestion des consultations]
- [Chahd Maaloul] - [gestion des plans sportives]
- [Ahmed Regaieg] - [gestion des parcours de santés]
- [Hamza Najjar] - [gestion des plan de nutritions]
- [Med Taher Zeidi] - [gestion des utilisateurs ]
 

## Academic Context
Developed at **Esprit School of Engineering - Tunisia**  
[PI] - [3A33] | 2025-2026

## Getting Started
### Prérequis
- ### Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants sur votre machine :

- **XAMPP** (version 8.x ou supérieure) - [Télécharger XAMPP](https://www.apachefriends.org/fr/index.html)
  - Contient : Apache, MySQL (MariaDB), PHP, phpMyAdmin
- **Symfony CLI** (interface en ligne de commande) - [Installer Symfony CLI](https://symfony.com/download)
- **Composer** (gestionnaire de dépendances PHP) - [Télécharger Composer](https://getcomposer.org/)
- **Git** - [Télécharger Git](https://git-scm.com/)
- **VS Code** (éditeur de code) - [Télécharger VS Code](https://code.visualstudio.com/)
- **Node.js** et **npm** (si vous utilisez Webpack Encore ou des assets frontaux) - [Télécharger Node.js](https://nodejs.org/)
- **Python** (version 3.x) - [Télécharger Python](https://www.python.org/downloads/) *(si nécessaire pour des scripts)*
- **MySQL Workbench** (optionnel, pour gérer visuellement la base de données) - [Télécharger MySQL Workbench](https://www.mysql.com/products/workbench/)

### Extensions VS Code recommandées

- **PHP Intelephense** (pour l'autocomplétion PHP)
- **Twig Language 2** (pour les templates Twig)
- **Symfony for VSCode** (outils spécifiques Symfony)
- **GitLens** (pour mieux visualiser Git)
- **MySQL** (pour exécuter des requêtes depuis VS Code)

### Installation
```bash
git clone [URL du dépôt]
cd [nom du projet]
npm install
npm start
