# Module d'Authentification et Gestion des Utilisateurs - WellCare Connect

## 📋 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture Technique](#architecture-technique)
3. [Entités et Modèle de Données](#entités-et-modèle-de-données)
4. [Sécurité et Authentification](#sécurité-et-authentification)
5. [Fonctionnalités](#fonctionnalités)
6. [Contrôleurs](#contrôleurs)
7. [Services](#services)
8. [Formulaires](#formulaires)
9. [Templates et Vues](#templates-et-vues)
10. [Configuration](#configuration)
11. [API Endpoints](#api-endpoints)
12. [Flux d'Authentification](#flux-dauthentification)
13. [Sécurité Implémentée](#sécurité-implémentée)
14. [Bugs Corrigés](#bugs-corrigés)

---

## Vue d'ensemble

Le module d'authentification de WellCare Connect est un système complet de gestion des utilisateurs implémenté avec **Symfony 6.4** en utilisant le système d'authentification natif de Symfony (sans FOSUserBundle).

### Contraintes Techniques Respectées

| Contrainte | Statut |
|------------|--------|
| Symfony 6.4 | ✅ |
| Pas de FOSUserBundle | ✅ |
| Pas de AdminBundle | ✅ |
| Images en URL seulement | ✅ |
| Sécurité Symfony Native | ✅ |

### Acteurs du Système

Le système gère 5 types d'utilisateurs avec des rôles distincts :

| Acteur | Rôle | Description |
|--------|------|-------------|
| **Patient** | `ROLE_PATIENT` | Utilisateur standard cherchant des services de santé |
| **Médecin** | `ROLE_MEDECIN` | Professionnel de santé offrant des consultations |
| **Coach** | `ROLE_COACH` | Coach sportif et bien-être |
| **Nutritionniste** | `ROLE_NUTRITIONIST` | Spécialiste en nutrition |
| **Administrateur** | `ROLE_ADMIN` | Gestionnaire de la plateforme |

---

## Architecture Technique

### Structure des Fichiers

```
wellcare-connect3/
├── src/
│   ├── Entity/
│   │   ├── User.php              # Entité abstraite de base
│   │   ├── Patient.php           # Patient (hérite de User)
│   │   ├── Medecin.php           # Médecin (hérite de User)
│   │   ├── Coach.php             # Coach (hérite de User)
│   │   ├── Nutritionist.php      # Nutritionniste (hérite de User)
│   │   └── Administrator.php     # Administrateur (hérite de User)
│   ├── Controller/
│   │   └── AuthController.php    # Contrôleur d'authentification
│   ├── Security/
│   │   ├── Authenticator.php     # Authentificateur personnalisé
│   │   └── AccessDeniedHandler.php
│   ├── EventSubscriber/
│   │   ├── LogoutEventSubscriber.php
│   │   └── SessionSecuritySubscriber.php
│   ├── Service/
│   │   ├── LoginValidationService.php
│   │   ├── PasswordResetService.php
│   │   └── EmailVerificationService.php
│   ├── Form/
│   │   ├── LoginFormType.php
│   │   ├── RegistrationFormType.php
│   │   └── ChangePasswordFormType.php
│   └── Repository/
│       └── UserRepository.php
├── templates/
│   ├── auth/
│   │   ├── login.html.twig
│   │   ├── register-patient.html.twig
│   │   ├── register-professional.html.twig
│   │   ├── forgot-password.html.twig
│   │   ├── reset-password.html.twig
│   │   └── verify-email.html.twig
│   └── layouts/
│       ├── auth.html.twig
│       └── app.html.twig
└── config/
    └── packages/
        └── security.yaml
```

### Patron de Conception

- **Single Table Inheritance (STI)** : Toutes les entités utilisateur partagent une seule table `users` avec une colonne discriminatrice `role`
- **Repository Pattern** : Accès aux données via `UserRepository`
- **Service Layer** : Logique métier dans les services dédiés

---

## Entités et Modèle de Données

### Entité User (Abstraite)

```php
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'role', type: 'string')]
#[ORM\DiscriminatorMap([
    'ROLE_PATIENT' => Patient::class,
    'ROLE_MEDECIN' => Medecin::class,
    'ROLE_COACH' => Coach::class,
    'ROLE_NUTRITIONIST' => Nutritionist::class,
    'ROLE_ADMIN' => Administrator::class,
])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
```

### Attributs Communs

| Attribut | Type | Contraintes | Description |
|----------|------|-------------|-------------|
| `uuid` | string(36) | Unique | Identifiant UUID v4 |
| `email` | string(180) | Unique, NotBlank, Email | Adresse email |
| `password` | string | Hashed | Mot de passe hashé |
| `firstName` | string(100) | NotBlank, min 2 chars | Prénom |
| `lastName` | string(100) | NotBlank, min 2 chars | Nom |
| `birthdate` | date | Nullable | Date de naissance |
| `phone` | string(20) | Nullable, Regex | Numéro de téléphone |
| `avatarUrl` | string(500) | Nullable, URL | URL de l'avatar |
| `address` | string(255) | Nullable | Adresse |
| `licenseNumber` | string(100) | Nullable | Numéro de licence (professionnels) |
| `isActive` | boolean | Default true | Compte actif |
| `createdAt` | datetime | Auto | Date de création |
| `updatedAt` | datetime | Nullable | Date de modification |
| `resetToken` | string(255) | Nullable | Token de réinitialisation |
| `resetTokenExpiresAt` | datetime | Nullable | Expiration du token |
| `lastLoginAt` | datetime | Nullable | Dernière connexion |
| `loginAttempts` | integer | Default 0 | Tentatives de connexion |
| `lockedUntil` | datetime | Nullable | Verrouillé jusqu'à |
| `isEmailVerified` | boolean | Default false | Email vérifié |
| `emailVerificationToken` | string(255) | Nullable | Token de vérification |
| `lastSessionId` | string(128) | Nullable | ID de session |

### Entités Spécialisées

#### Patient
```php
class Patient extends User
{
    // Hérite de tous les attributs de User
    // Rôle: ROLE_PATIENT
}
```

#### Medecin
```php
class Medecin extends User
{
    private ?string $specialite = null;      // Spécialité médicale
    private ?int $yearsOfExperience = null;  // Années d'expérience
    private ?string $diplomaUrl = null;      // URL du diplôme
    private bool $isVerifiedByAdmin = false; // Vérifié par admin
    private ?\DateTime $verificationDate = null;
    private float $rating = 0.0;             // Note moyenne
}
```

#### Coach
```php
class Coach extends User
{
    private ?string $specialite = null;      // Spécialité (fitness, yoga, etc.)
    private ?int $experience = null;         // Années d'expérience
}
```

#### Nutritionist
```php
class Nutritionist extends User
{
    private ?string $specialite = null;      // Spécialité nutritionnelle
    private ?int $experience = null;         // Années d'expérience
}
```

#### Administrator
```php
class Administrator extends User
{
    private ?string $token = null;           // Token d'administration
}
```

### Énumération des Spécialités Médicales

```php
enum MedicalSpecialty: string
{
    case CARDIOLOGY = 'CARDIOLOGY';
    case DERMATOLOGY = 'DERMATOLOGY';
    case ENDOCRINOLOGY = 'ENDOCRINOLOGY';
    case GASTROENTEROLOGY = 'GASTROENTEROLOGY';
    case NEUROLOGY = 'NEUROLOGY';
    case PSYCHIATRY = 'PSYCHIATRY';
    case PHYSIOTHERAPY = 'PHYSIOTHERAPY';
    case PEDIATRICS = 'PEDIATRICS';
    case GYNECOLOGY = 'GYNECOLOGY';
    case OPHTHALMOLOGY = 'OPHTHALMOLOGY';
    case OTHER = 'OTHER';
}
```

---

## Sécurité et Authentification

### Configuration Security.yaml

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
    
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    
    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            custom_authenticator: App\Security\Authenticator
            logout:
                path: app_logout
                target: app_login
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800  # 7 jours
                path: /
                always_remember_me: true
            login_throttling:
                max_attempts: 5
                interval: '15 minutes'
    
    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/register, roles: PUBLIC_ACCESS }
        - { path: ^/forgot-password, roles: PUBLIC_ACCESS }
        - { path: ^/reset-password, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/doctor, roles: ROLE_MEDECIN }
        - { path: ^/coach, roles: ROLE_COACH }
        - { path: ^/nutritionist, roles: ROLE_NUTRITIONIST }
```

### Authentificateur Personnalisé

```php
class Authenticator extends AbstractLoginFormAuthenticator
{
    // URL de login
    public function getLoginUrl(Request $request): string
    
    // Authentification
    public function authenticate(Request $request): Passport
    
    // Succès - Redirection selon le rôle
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // ROLE_ADMIN → admin_trail_analytics
        // ROLE_MEDECIN → doctor_patient_queue
        // ROLE_COACH → coach_dashboard
        // ROLE_NUTRITIONIST → nutrition_nutritionniste_dashboard
        // ROLE_PATIENT → appointment_patient_dashboard
    }
    
    // Échec - Gestion des tentatives
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
}
```

---

## Fonctionnalités

### 1. Inscription

#### Patient
- **Route**: `/register/patient`
- **Template**: `auth/register-patient.html.twig`
- **Étapes**: Formulaire multi-étapes avec validation en temps réel
- **Champs**: Email, mot de passe, nom, prénom, date de naissance, téléphone

#### Professionnel (Médecin, Coach, Nutritionniste)
- **Routes**: `/register/medecin`, `/register/coach`, `/register/nutritionist`
- **Template**: `auth/register-professional.html.twig`
- **Champs supplémentaires**: Spécialité, numéro de licence, années d'expérience, diplôme
- **Vérification admin requise**: Les comptes professionnels doivent être vérifiés

### 2. Connexion

- **Route**: `/login`
- **Template**: `auth/login.html.twig`
- **Fonctionnalités**:
  - Validation des identifiants
  - Protection contre le brute force (5 tentatives / 15 min)
  - Verrouillage du compte après 5 échecs
  - Remember me (7 jours)
  - Redirection selon le rôle

### 3. Déconnexion

- **Route**: `/logout`
- **Fonctionnalités**:
  - Invalidation de session
  - Message flash de confirmation
  - Redirection vers login

### 4. Réinitialisation du Mot de Passe

- **Route demande**: `/forgot-password`
- **Route réinitialisation**: `/reset-password?token=xxx`
- **Durée du token**: 1 heure
- **Template**: `auth/forgot-password.html.twig`, `auth/reset-password.html.twig`

### 5. Vérification d'Email

- **Route**: `/verify-email`
- **Template**: `auth/verify-email.html.twig`
- **Renvoi**: `/api/resend-verification`

---

## Contrôleurs

### AuthController.php

| Méthode | Route | Description |
|---------|-------|-------------|
| `login()` | `/login` | Affiche et traite le formulaire de connexion |
| `logout()` | `/logout` | Déconnexion (interceptée par Symfony) |
| `accessDenied()` | `/access-denied` | Page d'accès refusé |
| `forgotPassword()` | `/forgot-password` | Demande de réinitialisation |
| `resetPassword()` | `/reset-password` | Réinitialisation du mot de passe |
| `registerPatient()` | `/register/patient` | Inscription patient |
| `registerMedecin()` | `/register/medecin` | Inscription médecin |
| `registerCoach()` | `/register/coach` | Inscription coach |
| `registerNutritionist()` | `/register/nutritionist` | Inscription nutritionniste |
| `verifyEmail()` | `/verify-email` | Vérification de l'email |

### API Endpoints

| Méthode | Route | Description |
|---------|-------|-------------|
| `validateLogin()` | `POST /api/login/validate` | Validation AJAX des identifiants |
| `checkEmailAvailability()` | `POST /api/check-email` | Vérification disponibilité email |
| `checkPhoneAvailability()` | `POST /api/check-phone` | Vérification disponibilité téléphone |
| `apiForgotPassword()` | `POST /api/forgot-password` | API pour mot de passe oublié |

---

## Services

### LoginValidationService

```php
class LoginValidationService
{
    // Validation des identifiants
    public function validateCredentials(string $email, string $password): array
    
    // Validation de la force du mot de passe
    public function validatePasswordStrength(string $password): array
    
    // Scénarios de connexion (pour tests)
    public function getLoginScenarios(): array
}
```

### PasswordResetService

```php
class PasswordResetService
{
    // Demande de réinitialisation
    public function requestPasswordReset(string $email): array
    
    // Validation du token
    public function validateResetToken(string $token): ?User
    
    // Réinitialisation effective
    public function resetPassword(User $user, string $newPassword): array
}
```

### EmailVerificationService

```php
class EmailVerificationService
{
    // Envoi de l'email de vérification
    public function sendVerificationEmail(User $user): void
    
    // Vérification du token
    public function verifyEmail(string $token): array
    
    // Renvoi de l'email
    public function resendVerification(User $user): array
}
```

---

## Formulaires

### LoginFormType

```php
class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('_remember_me', CheckboxType::class, ['required' => false])
        ;
    }
}
```

### RegistrationFormType

```php
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('phone', TelType::class, ['required' => false])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
            ])
            ->add('agreeTerms', CheckboxType::class)
        ;
    }
}
```

---

## Templates et Vues

### Layouts

| Template | Description |
|----------|-------------|
| `base.html.twig` | Layout de base pour pages publiques |
| `layouts/auth.html.twig` | Layout pour pages d'authentification |
| `layouts/app.html.twig` | Layout pour pages authentifiées avec sidebar |

### Pages d'Authentification

| Template | Description | Fonctionnalités |
|----------|-------------|-----------------|
| `login.html.twig` | Page de connexion | Validation client, Remember me, Messages d'erreur |
| `register-patient.html.twig` | Inscription patient | Multi-étapes, Validation email/phone en temps réel |
| `register-professional.html.twig` | Inscription professionnel | Upload diplôme, Sélection spécialité |
| `forgot-password.html.twig` | Mot de passe oublié | Formulaire email, Messages de sécurité |
| `reset-password.html.twig` | Réinitialisation | Nouveau mot de passe, Confirmation |
| `verify-email.html.twig` | Vérification email | Renvoi email, Instructions |

---

## Configuration

### Session (framework.yaml)

```yaml
framework:
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
        gc_maxlifetime: 1800      # 30 minutes
        cookie_lifetime: 1800     # 30 minutes
```

### Base de Données (.env.local)

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/wellora?serverVersion=8.0&charset=utf8mb4"
```

---

## Flux d'Authentification

### Diagramme de Séquence - Connexion

```
┌─────────┐     ┌─────────┐     ┌──────────────┐     ┌───────────┐
│  User   │     │ Browser │     │ Authenticator│     │ Database  │
└────┬────┘     └────┬────┘     └──────┬───────┘     └─────┬─────┘
     │               │                  │                   │
     │ 1. Submit     │                  │                   │
     │──────────────>│                  │                   │
     │               │ 2. POST /login   │                   │
     │               │─────────────────>│                   │
     │               │                  │ 3. Find user     │
     │               │                  │──────────────────>│
     │               │                  │                   │
     │               │                  │ 4. User data     │
     │               │                  │<──────────────────│
     │               │                  │                   │
     │               │                  │ 5. Check password │
     │               │                  │───────────────────│
     │               │                  │                   │
     │               │ 6. Create session│                   │
     │               │<─────────────────│                   │
     │               │                  │                   │
     │ 7. Redirect   │                  │                   │
     │<──────────────│                  │                   │
     │               │                  │                   │
```

### Diagramme de Séquence - Inscription

```
┌─────────┐     ┌─────────────┐     ┌──────────────┐     ┌───────────┐
│  User   │     │ AuthController│   │   Services   │     │ Database  │
└────┬────┘     └──────┬──────┘     └──────┬───────┘     └─────┬─────┘
     │                 │                   │                   │
     │ 1. Fill form    │                   │                   │
     │────────────────>│                   │                   │
     │                 │ 2. Validate       │                   │
     │                 │──────────────────>│                   │
     │                 │                   │                   │
     │                 │ 3. Check email    │                   │
     │                 │───────────────────────────────────>│
     │                 │                   │                   │
     │                 │ 4. Create user    │                   │
     │                 │──────────────────>│                   │
     │                 │                   │ 5. Hash password  │
     │                 │                   │──────────────────>│
     │                 │                   │                   │
     │                 │ 6. Persist user   │                   │
     │                 │───────────────────────────────────>│
     │                 │                   │                   │
     │                 │ 7. Send verification email          │
     │                 │──────────────────>│                   │
     │                 │                   │                   │
     │ 8. Redirect     │                   │                   │
     │<────────────────│                   │                   │
```

---

## Sécurité Implémentée

### Protection CSRF

```twig
{# Dans tous les formulaires #}
<input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
```

### Protection XSS

```twig
{# Twig auto-escape activé par défaut #}
{{ user.email|e }}  {# Échappement automatique #}
```

### Protection SQL Injection

```php
// Utilisation de Doctrine avec paramètres
$user = $this->entityManager->getRepository(User::class)
    ->findOneBy(['email' => $email]);  // Paramétré automatiquement
```

### Protection Brute Force

```yaml
# security.yaml
login_throttling:
    max_attempts: 5
    interval: '15 minutes'
```

```php
// Verrouillage du compte après 5 tentatives
if ($user->getLoginAttempts() >= 5) {
    $user->setLockedUntil(new \DateTime('+30 minutes'));
}
```

### Validation des Inputs

```php
#[Assert\NotBlank(message: 'L\'email est obligatoire')]
#[Assert\Email(message: 'Veuillez entrer une adresse email valide')]
private ?string $email = null;

#[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')]
#[Assert\Regex(pattern: '/[A-Z]/', message: 'Doit contenir une majuscule')]
#[Assert\Regex(pattern: '/[0-9]/', message: 'Doit contenir un chiffre')]
```

### Session Security

```php
class SessionSecuritySubscriber implements EventSubscriberInterface
{
    // Timeout de session (30 minutes)
    public function checkSessionTimeout(RequestEvent $event)
    
    // Détection de sessions concurrentes
    public function onInteractiveLogin(InteractiveLoginEvent $event)
}
```

---

## Bugs Corrigés

### Bug Critique: JavaScript Form Interception

**Problème**: Le JavaScript interceptait les soumissions de formulaire et empêchait l'établissement de session.

**Fichiers affectés**:
- `templates/auth/login.html.twig`
- `templates/auth/register-patient.html.twig`
- `templates/auth/register-professional.html.twig`

**Solution**: Suppression de l'interception JavaScript et laisser les formulaires se soumettre normalement vers Symfony.

### Bug: Flash Message après Invalidation Session

**Problème**: Le message flash de déconnexion était ajouté après l'invalidation de la session.

**Fichier**: `src/EventSubscriber/LogoutEventSubscriber.php`

**Solution**: Ajouter le message flash AVANT d'invalider la session.

### Bug: Timeout Session Non Alignés

**Problème**: `gc_maxlifetime` (1800s) et `cookie_lifetime` (3600s) n'étaient pas alignés.

**Fichier**: `config/packages/framework.yaml`

**Solution**: Aligner les deux valeurs à 1800 secondes (30 minutes).

### Bug: Double Tracking des Connexions

**Problème**: `lastLoginAt` et `loginAttempts` étaient mis à jour deux fois (dans Authenticator et SessionSecuritySubscriber).

**Fichier**: `src/EventSubscriber/SessionSecuritySubscriber.php`

**Solution**: Supprimer le doublon dans SessionSecuritySubscriber.

---

## Tests

### Commandes de Test

```bash
# Vider le cache
php bin/console cache:clear

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (si disponibles)
php bin/console doctrine:fixtures:load

# Exécuter les tests
php bin/phpunit
```

### Scénarios de Test

| Scénario | Résultat Attendu |
|----------|------------------|
| Inscription Patient | Compte créé, email de vérification envoyé |
| Inscription Professionnel | Compte créé, en attente de vérification admin |
| Connexion Valide | Redirection vers le dashboard approprié |
| Connexion Invalide | Message d'erreur, tentative incrémentée |
| 5 Échecs Consécutifs | Compte verrouillé 30 minutes |
| Déconnexion | Session invalidée, redirection vers login |
| Mot de passe oublié | Email avec lien de réinitialisation |
| Réinitialisation | Mot de passe modifié, redirection vers login |

---

## Maintenance

### Logs

Les logs d'authentification sont disponibles dans:
- `var/log/dev.log` (environnement de développement)
- `var/log/prod.log` (environnement de production)

### Commandes Utiles

```bash
# Vérifier la configuration de sécurité
php bin/console security:hash-password

# Lister les utilisateurs
php bin/console doctrine:query:sql "SELECT email, role FROM users"

# Vider les tokens expirés
php bin/console doctrine:query:sql "DELETE FROM users WHERE reset_token_expires_at < NOW()"
```

---

## Conclusion

Le module d'authentification de WellCare Connect est un système complet et sécurisé qui respecte toutes les contraintes techniques du projet PIDEV. Il utilise les meilleures pratiques de Symfony 6.4 et implémente toutes les fonctionnalités nécessaires pour gérer les 5 types d'utilisateurs du système.

### Points Forts

- ✅ Architecture Single Table Inheritance (STI) efficace
- ✅ Sécurité native Symfony sans bundles externes
- ✅ Protection contre les attaques courantes (CSRF, XSS, SQL Injection, Brute Force)
- ✅ Gestion des sessions avec timeout
- ✅ Vérification d'email obligatoire
- ✅ Réinitialisation de mot de passe sécurisée
- ✅ Redirection intelligente selon le rôle

---

*Document généré pour le projet WellCare Connect - Module d'Authentification*
*Version: 1.0.0*
*Date: Février 2026*
