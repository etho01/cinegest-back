# 🎬 CineGest - Système de Gestion de Cinéma

> Application de gestion de cinéma développée en Laravel, démontrant la maîtrise de deux approches architecturales : Architecture Laravel classique et Clean Architecture (DDD).

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)

## 📋 Table des matières
- [À propos du projet](#à-propos-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Architecture](#architecture)
- [Installation](#installation)
- [Compétences démontrées](#compétences-démontrées)

---

## 🎯 À propos du projet

**CineGest** est une application backend complète de gestion de cinéma permettant la gestion des films, séances, réservations et paiements. Ce projet démontre ma capacité à concevoir et développer des applications Laravel robustes avec deux approches architecturales distinctes selon les besoins.

### Objectifs du projet

- Gérer les cinémas, salles et séances de manière efficace
- Système de réservation en ligne avec gestion des stocks
- Intégration de paiements sécurisés (Stripe)
- API RESTful pour une future application mobile/frontend
- Cache intelligent des données de films
- Intégration avec des APIs externes (TMDB, AlloCiné)

---

## ✨ Fonctionnalités

### 🎪 Gestion Cinéma (Admin)
- CRUD complet pour les cinémas, salles et séances
- Système de tarification flexible
- Gestion des programmations hebdomadaires
- Calcul automatique des semaines cinéma (jeudi-mercredi)

### 🎫 Réservation (Utilisateur)
- Consultation des films et séances disponibles
- Système de réservation avec sélection de places
- Gestion des réservations (annulation, modification)
- Confirmation par email

### 💳 Paiements
- Intégration Stripe pour les paiements sécurisés
- Webhooks Stripe pour la validation des transactions
- Gestion des remboursements

### 📊 API Externe
- Intégration TMDB pour récupérer les informations des films
- Cache intelligent pour optimiser les performances
- Synchronisation automatique des données

---

## 🛠 Technologies utilisées

### Backend
- **PHP 8.2+** - Langage backend
- **Laravel 11.x** - Framework web PHP
- **MySQL 8.0** - Base de données relationnelle
- **Redis** - Cache et sessions

### Architecture & Patterns
- **Clean Architecture** (DDD, Hexagonal Architecture)
- **SOLID Principles**
- **Repository Pattern**
- **DTO Pattern**
- **Value Objects**

### Outils & DevOps
- **Docker & Docker Compose** - Conteneurisation
- **PHPUnit** - Tests unitaires et d'intégration
- **Stripe API** - Paiements en ligne
- **Mailjet** - Service d'emailing
- **Composer** - Gestionnaire de dépendances

### APIs externes
- **TMDB API** - Base de données de films
- **Stripe API** - Traitement des paiements

---

## 🏗 Architecture

Ce projet implémente **deux approches architecturales** pour démontrer ma polyvalence :

### 1️⃣ Architecture Laravel Classique (`/app`)

Approche pragmatique utilisant les conventions Laravel pour un développement rapide.

**Structure :**
```
app/
├── Http/Controllers/    # Logique de contrôle
├── Models/             # Eloquent Models (Active Record)
├── Repository/         # Abstraction persistence
└── UseCase/           # Logique métier
```

**Avantages :** Développement rapide, idéal pour MVP et CRUD simples

### 2️⃣ Clean Architecture (`/site`)

Approche avancée suivant les principes DDD et Clean Architecture pour une maintenabilité maximale.

**Structure :**
```
app/
├── Domain/                        # Cœur métier (0 dépendance framework)
│   ├── Entity/                    # Entités métier pures
│   ├── ValueObject/               # Objets valeur immuables
│   └── Repository/                # Interfaces de persistence
│
├── Application/                   # Cas d'utilisation
│   ├── DTO/                       # Data Transfer Objects
│   └── UseCase/                   # Logique applicative
│
└── Infrastructure/                # Implémentations techniques
    ├── Persistence/Eloquent/      # Repository Eloquent
    └── Persistence/Mapper/        # Mappers Domain ↔ Eloquent
```

**Principes appliqués :**
- ✅ **Separation of Concerns** - Chaque couche a une responsabilité unique
- ✅ **Dependency Inversion** - Le Domain ne dépend de rien
- ✅ **Single Responsibility** - Une classe, une responsabilité
- ✅ **Open/Closed Principle** - Extensible sans modification
- ✅ **Interface Segregation** - Interfaces spécifiques et ciblées

**Avantages :** Testabilité maximale, code maintenable, indépendance du framework

### 📊 Comparaison des approches

| Critère | Laravel Classique | Clean Architecture |
|---------|-------------------|-------------------|
| **Complexité** | Faible | Élevée |
| **Développement initial** | Très rapide | Plus lent |
| **Maintenabilité long terme** | Moyenne | Excellente |
| **Testabilité** | Correcte | Excellente |
| **Indépendance framework** | Faible | Totale |
| **Évolutivité** | Moyenne | Excellente |

**Choix d'architecture :**
- **Laravel Classique** → MVP, CRUD simples, petites équipes, time-to-market critique
- **Clean Architecture** → Projets complexes, long terme, grandes équipes, maintenabilité critique


---

## 🚀 Installation

### Prérequis

- Docker & Docker Compose
- Git

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/votre-username/cinegest-back.git
cd cinegest-back
```

2. **Configurer les variables d'environnement**
```bash
cp .env.example .env
# Éditer .env avec vos configurations
```

3. **Démarrer les conteneurs Docker**
```bash
docker-compose up -d
```

4. **Installer les dépendances**
```bash
docker-compose exec app composer install
```

5. **Générer la clé d'application**
```bash
docker-compose exec app php artisan key:generate
```

6. **Exécuter les migrations**
```bash
docker-compose exec app php artisan migrate --seed
```

7. **Accéder à l'application**
```
API: http://localhost:8000
Documentation API: http://localhost:8000/api/documentation
```

### Tests

```bash
# Tests unitaires
docker-compose exec app php artisan test

# Tests avec couverture
docker-compose exec app php artisan test --coverage
```

---

## 💼 Compétences démontrées

### Architecture & Design Patterns
- ✅ Clean Architecture (Hexagonal Architecture)
- ✅ Domain-Driven Design (DDD)
- ✅ SOLID Principles
- ✅ Repository Pattern
- ✅ DTO Pattern
- ✅ Value Objects
- ✅ Dependency Injection
- ✅ Service Provider Pattern

### Laravel
- ✅ Eloquent ORM (relations, scopes, mutators)
- ✅ API Resources & Collections
- ✅ Form Requests (validation)
- ✅ Service Container & Dependency Injection
- ✅ Middleware
- ✅ Events & Listeners
- ✅ Queue & Jobs
- ✅ Notifications & Mailing
- ✅ API Authentication (Sanctum)
- ✅ Database Migrations & Seeders

### Base de données
- ✅ Modélisation relationnelle complexe
- ✅ Optimisation des requêtes (eager loading, indexes)
- ✅ Transactions
- ✅ Relations many-to-many avec pivot
- ✅ Soft deletes

### API & Intégrations
- ✅ RESTful API design
- ✅ Webhooks (Stripe)
- ✅ Intégration APIs tierces (TMDB, Stripe, Mailjet)
- ✅ Authentification & autorisations
- ✅ Rate limiting

### DevOps & Outils
- ✅ Docker & Docker Compose
- ✅ Git & GitHub
- ✅ Tests automatisés (PHPUnit)
- ✅ Configuration multi-environnement
- ✅ Logging & monitoring

### Bonnes pratiques
- ✅ Code testable et découplé
- ✅ Gestion des erreurs et exceptions custom
- ✅ Validation des données
- ✅ Cache stratégique
- ✅ Documentation claire
- ✅ Principe de responsabilité unique
- ✅ Code review friendly

---

## 📚 Structure du projet

```
cinegest-back/
├── app/
│   ├── Application/         # Use Cases & DTOs (Clean Architecture)
│   ├── Domain/             # Entities, Value Objects, Interfaces (Clean Architecture)
│   ├── Infrastructure/     # Implémentations techniques (Clean Architecture)
│   ├── Http/               # Controllers, Middleware, Requests, Resources
│   ├── Models/             # Eloquent Models
│   ├── Repository/         # Repositories (Laravel classique)
│   ├── UseCase/           # Use Cases (Laravel classique)
│   └── Exceptions/        # Exceptions custom
├── config/                 # Configuration Laravel
├── database/
│   ├── migrations/        # Migrations de base de données
│   └── seeders/           # Seeders
├── routes/                # Définition des routes API
├── tests/                 # Tests unitaires et d'intégration
│   ├── Unit/
│   └── Feature/
├── docker-compose.yml     # Configuration Docker
└── README.md

```

---

## 🎓 Réflexions architecturales

### Pourquoi deux architectures ?

Ce projet démontre ma compréhension que **l'architecture doit servir le besoin**, pas l'inverse :

- **Laravel Classique** : Utilisée pour les fonctionnalités CRUD standards où la rapidité de développement est prioritaire
- **Clean Architecture** : Utilisée pour la partie réservation/paiement où la logique métier est complexe et nécessite une maintenabilité maximale

### Leçons apprises

1. **Clean Architecture n'est pas toujours la réponse** - Sur-architecturer un simple CRUD peut ralentir le développement sans bénéfice réel
2. **Les tests guident l'architecture** - Une architecture testable révèle naturellement les dépendances problématiques
3. **Le Domain doit rester pur** - Aucune dépendance au framework dans la couche Domain garantit la portabilité
4. **Les Value Objects évitent les bugs** - Encapsuler les validations dans des Value Objects (Email, Money) élimine une catégorie entière de bugs
5. **Les interfaces permettent la flexibilité** - Changer d'Eloquent à Doctrine nécessiterait uniquement de réécrire l'Infrastructure

---
