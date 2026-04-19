# 🎬 CineGest - Système de Gestion de Cinéma

> **Application backend complète de gestion de cinéma** démontrant l'expertise en architecture logicielle, DevOps et développement Laravel moderne.

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Kubernetes](https://img.shields.io/badge/Kubernetes-Production-326CE5?logo=kubernetes&logoColor=white)](https://kubernetes.io/)
[![Vault](https://img.shields.io/badge/Vault-Secrets-000000?logo=vault&logoColor=white)](https://www.vaultproject.io/)
[![GitHub Actions](https://img.shields.io/badge/CI/CD-GitHub_Actions-2088FF?logo=github-actions&logoColor=white)](https://github.com/features/actions)

**[🎯 START HERE - Guide Recruteurs](START_HERE.md)** • **[📊 Portfolio Technique](PORTFOLIO.md)** • **[💻 Exemples de Code](TECHNICAL_HIGHLIGHTS.md)** • **[🚀 Quick Start](QUICKSTART.md)**

</div>

> **👔 Recruteurs/Tech Leads :** Consultez **[START_HERE.md](START_HERE.md)** pour un guide de navigation optimisé selon votre focus d'évaluation.

---

## 🌟 Highlights - Ce qui rend ce projet unique

<table>
<tr>
<td width="50%" valign="top">

### 🏗️ **Architecture Hybride**
- ✅ **Clean Architecture (DDD)** pour logique complexe
- ✅ **Laravel MVC** pour CRUD standards
- ✅ Séparation Domain/Application/Infrastructure
- ✅ Code 100% testable et découplé

</td>
<td width="50%" valign="top">

### ☸️ **DevOps Production-Ready**
- ✅ **Kubernetes (K3s)** avec auto-scaling
- ✅ **HashiCorp Vault** pour secrets
- ✅ **CI/CD complet** GitHub Actions
- ✅ **Zero-downtime** deployments

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🔒 **Sécurité Avancée**
- ✅ SecurityContext (non-root, read-only FS)
- ✅ Secrets jamais dans Git (Vault)
- ✅ SSL/TLS automatique (Let's Encrypt)
- ✅ Rate limiting & security headers

</td>
<td width="50%" valign="top">

### 💳 **Intégrations Complexes**
- ✅ **Stripe** (paiements + webhooks)
- ✅ **TMDB API** avec cache intelligent
- ✅ **Mailjet** (emails transactionnels)
- ✅ Retry logic & circuit breaker

</td>
</tr>
</table>

> **🎖️ Pour les recruteurs :** Consultez [PORTFOLIO.md](PORTFOLIO.md) pour un aperçu technique complet et les compétences démontrées.

## � Guides de démarrage rapide

| Guide | Description | Durée |
|-------|-------------|-------|
| **[⚡ QUICKSTART.md](QUICKSTART.md)** | Configuration infrastructure en commandes essentielles | 5 min |
| **[📝 SETUP.md](SETUP.md)** | Guide complet étape par étape avec toutes les commandes | 20 min |
| **[🤖 setup-infrastructure.sh](setup-infrastructure.sh)** | Script automatique de configuration complète | 10 min |

**Pour déployer en production Kubernetes:**
```bash
./setup-infrastructure.sh  # Configuration automatique
# OU
# Suivre QUICKSTART.md pour version manuelle
```

---

## �📋 Table des matières
- [À propos du projet](#à-propos-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Architecture](#architecture)
- [Installation](#installation)
- [Compétences démontrées](#compétences-démontrées)

---

## 🎯 À propos du projet

**CineGest** est une **application backend complète de gestion de cinéma** déployée en production sur **Kubernetes** avec une infrastructure moderne et sécurisée. Ce projet démontre la maîtrise de l'écosystème Laravel, des architectures avancées (Clean Architecture/DDD), et des pratiques DevOps professionnelles.

### 📊 Métriques du Projet

| Métrique | Valeur | Description |
|----------|--------|-------------|
| **Lignes de code** | ~15 000+ | Application + Tests + Configuration |
| **Couverture tests** | 80%+ | Tests unitaires et d'intégration |
| **Architecture** | Hybride | Clean Architecture + Laravel MVC |
| **APIs intégrées** | 3 | Stripe, TMDB, Mailjet |
| **Temps déploiement** | ~2 min | CI/CD automatisé avec rollback |
| **Uptime cible** | 99.9% | Zero-downtime deployments |

### 🎯 Objectifs & Fonctionnalités Clés

- ✅ **Gestion complète :** Cinémas, salles, séances avec tarification flexible
- ✅ **Réservation en ligne :** Gestion des stocks, sélection de places, confirmations emails
- ✅ **Paiements sécurisés :** Intégration Stripe avec webhooks et gestion des remboursements
- ✅ **API RESTful :** Endpoints documentés pour frontend/mobile
- ✅ **Cache intelligent :** Redis pour optimisation des performances (films, sessions)
- ✅ **Intégrations externes :** TMDB (films), Stripe (paiements), Mailjet (emails)

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

## ☸️ Déploiement Kubernetes

Ce projet est configuré pour un déploiement en production sur Kubernetes (K3s) avec intégration complète Vault et CI/CD GitHub Actions.

### 🏗️ Infrastructure

- **Kubernetes (K3s)** - Orchestration de conteneurs
- **HashiCorp Vault** - Gestion sécurisée des secrets
- **External Secrets Operator** - Synchronisation Vault → K8s
- **Traefik** - Ingress controller avec TLS
- **Cert-Manager** - Certificats SSL automatiques (Let's Encrypt)
- **GitHub Actions** - CI/CD automatisé

### 🔒 Sécurité

- ✅ Secrets stockés dans Vault (jamais dans Git)
- ✅ SecurityContext avec utilisateur non-root
- ✅ Capabilities drop (principe least privilege)
- ✅ Rate-limiting sur l'API
- ✅ Security headers HTTP
- ✅ TLS/SSL automatique
- ✅ Network policies
- ✅ ReadOnlyRootFilesystem

### 🚀 Pipeline CI/CD

```
Push sur main → Tests → Build Docker → Deploy K8s → Migrations → Health checks
                 ↓                                                    ↓
              PHPUnit                                         Rollback auto si échec
```

**Caractéristiques** :
- Tests automatiques avant chaque déploiement
- Build multi-stage Docker optimisé
- Déploiement zero-downtime (RollingUpdate)
- Migrations automatiques avec rollback
- Synchronisation secrets depuis Vault
- Monitoring et health checks

### 📚 Documentation déploiement

| Document | Description |
|----------|-------------|
| [Workflows GitHub Actions](.github/README.md) | Guide complet des workflows CI/CD |
| [Pipeline + Vault](.github/PIPELINE-VAULT.md) | Configuration pipeline avec Vault |
| [Guide rapide Vault](k8s/VAULT-QUICKSTART.md) | Installation et configuration Vault |
| [Vault complet](k8s/vault/README.md) | Documentation détaillée Vault |
| [Manifests Kubernetes](k8s/) | Configuration K8s (deployment, service, ingress) |

### ⚡ Déploiement rapide

```bash
# 1. Installer Vault et ESO
./k8s/vault/install.sh

# 2. Configurer Vault (une seule fois)
./k8s/vault/configure-vault.sh <ROOT_TOKEN>

# 3. Push code → Pipeline automatique
git push origin main
```

### 🎯 Fonctionnalités production

- **Zero-downtime deployments** - RollingUpdate avec maxUnavailable: 0
- **Auto-scaling** - HPA (Horizontal Pod Autoscaler) configuré
- **Health monitoring** - Liveness & Readiness probes
- **Rollback automatique** - En cas d'échec de déploiement
- **Secrets rotation** - Via Vault avec sync automatique
- **SSL/TLS** - Certificats Let's Encrypt automatiques
- **Rate limiting** - Protection contre les abus API
- **Logging centralisé** - Logs pods agrégés

---

## 💼 Compétences Démontrées

> **Ce projet est une vitrine complète de compétences backend, DevOps et architecturales recherchées en entreprise.**

<details open>
<summary><strong>🎨 Architecture & Design Patterns</strong></summary>

<br>

| Pattern/Principe | Implémentation | Fichiers Clés |
|------------------|----------------|---------------|
| **Clean Architecture** | Séparation Domain/Application/Infrastructure | [app/Domain/](app/Domain/), [app/Application/](app/Application/) |
| **Domain-Driven Design** | Entités, Value Objects, Repositories | [Domain/Entity/](app/Domain/Entity/), [Domain/ValueObject/](app/Domain/ValueObject/) |
| **SOLID Principles** | Code découplé, interfaces, injection de dépendances | Tout le projet |
| **Repository Pattern** | Abstraction de la persistance | [Domain/Repository/](app/Domain/Repository/), [Infrastructure/Persistence/](app/Infrastructure/Persistence/) |
| **DTO Pattern** | Transfert de données type-safe | [Application/DTO/](app/Application/DTO/) |
| **Value Objects** | Validation métier encapsulée | [Domain/ValueObject/](app/Domain/ValueObject/) |

</details>

<details open>
<summary><strong>⚡ Laravel (Niveau Expert)</strong></summary>

<br>

<table>
<tr>
<td width="50%">

**ORM & Base de données :**
- ✅ Eloquent (relations, scopes, mutators)
- ✅ Query Builder & Raw queries
- ✅ Migrations & Seeders
- ✅ Transactions & Locks
- ✅ Eager loading optimisé
- ✅ Indexes & performances

</td>
<td width="50%">

**API & Authentification :**
- ✅ API Resources & Collections
- ✅ Form Requests (validation)
- ✅ Sanctum (API tokens)
- ✅ Rate limiting
- ✅ Middleware custom
- ✅ CORS configuration

</td>
</tr>
<tr>
<td width="50%">

**Architecture Laravel :**
- ✅ Service Container & DI
- ✅ Service Providers
- ✅ Events & Listeners
- ✅ Queue & Jobs
- ✅ Notifications & Mailing
- ✅ Cache (Redis)

</td>
<td width="50%">

**Bonnes pratiques :**
- ✅ Form Requests
- ✅ API Resources
- ✅ Repository Pattern
- ✅ Service Layer
- ✅ Exception Handling
- ✅ Logging stratégique

</td>
</tr>
</table>

</details>

<details open>
<summary><strong>☸️ DevOps & Infrastructure (Production-Ready)</strong></summary>

<br>

| Technologie | Usage | Fichiers |
|-------------|-------|----------|
| **Kubernetes (K3s)** | Orchestration conteneurs, auto-scaling | [k8s/deployment.yaml](k8s/deployment.yaml), [k8s/hpa.yaml](k8s/hpa.yaml) |
| **HashiCorp Vault** | Gestion centralisée secrets (zéro secrets dans Git) | [k8s/vault/](k8s/vault/) |
| **External Secrets Operator** | Synchronisation Vault → K8s (auto refresh 15min) | [k8s/externalsecret.yaml](k8s/externalsecret.yaml) |
| **GitHub Actions** | CI/CD complet (test → build → deploy → migrate) | [.github/workflows/](.github/workflows/) |
| **Docker Multi-stage** | Images optimisées (<200MB) | [Dockerfile](Dockerfile) |
| **Traefik** | Ingress controller + middleware rate-limiting | [k8s/ingress.yaml](k8s/ingress.yaml) |
| **Cert-Manager** | Certificats SSL/TLS automatiques (Let's Encrypt) | [k8s/clusterissuer.yaml](k8s/clusterissuer.yaml) |

**Pratiques avancées :**
- ✅ **Zero-downtime deployments** (RollingUpdate avec maxUnavailable: 0)
- ✅ **Auto-rollback** sur échec de health checks
- ✅ **SecurityContext** (runAsUser: 33, readOnlyRootFilesystem)
- ✅ **Health checks** (liveness/readiness probes)
- ✅ **Resource limits** (CPU/Memory)
- ✅ **Horizontal Pod Autoscaling** (HPA basé sur CPU)
- ✅ **Infrastructure as Code** (GitOps)

</details>

<details open>
<summary><strong>💳 Intégrations APIs & Services Externes</strong></summary>

<br>

| Service | Fonctionnalités | Complexité |
|---------|----------------|------------|
| **Stripe** | Paiements, Webhooks (signature verification), Remboursements | ⭐⭐⭐⭐⭐ |
| **TMDB API** | Catalogue films, Cache intelligent Redis, Retry logic | ⭐⭐⭐⭐ |
| **Mailjet** | Emails transactionnels, Templates, Tracking | ⭐⭐⭐ |

**Code clé :**
- [Listeners/StripeWebhookListener.php](app/Listeners/StripeWebhookListener.php) - Validation webhooks
- [Services/](app/Services/) - Intégrations externes
- [Interface/MovieApiInterface.php](app/Interface/MovieApiInterface.php) - Abstraction API

</details>

<details open>
<summary><strong>🧪 Tests & Qualité de Code</strong></summary>

<br>

```bash
✅ Tests unitaires (Domain, Services, Use Cases)
✅ Tests d'intégration (API endpoints complets)
✅ Test-Driven Development (TDD)
✅ Mocks & Stubs pour isolation
✅ Factories & Seeders pour données de test
✅ PHPUnit configuration avancée
✅ Code Coverage 80%+
```

**Fichiers de test :**
- [tests/Unit/](tests/Unit/) - Tests unitaires (logique métier)
- [tests/Feature/](tests/Feature/) - Tests d'intégration (endpoints API)
- [phpunit.xml](phpunit.xml) - Configuration avancée

</details>

<details>
<summary><strong>🔒 Sécurité (Security-First Approach)</strong></summary>

<br>

**Kubernetes Security :**
```yaml
✅ SecurityContext (runAsUser: 33 - www-data)
✅ ReadOnlyRootFilesystem
✅ Capabilities dropped (least privilege)
✅ Network policies
✅ Pod Security Standards
```

**Application Security :**
```bash
✅ Secrets dans Vault (jamais dans Git)
✅ SSL/TLS automatique (Let's Encrypt)
✅ Rate limiting API (middleware Traefik)
✅ Security headers HTTP
✅ CSRF protection
✅ SQL injection protection (Eloquent ORM)
✅ XSS protection (Laravel sanitization)
✅ Validation stricte des inputs (Form Requests)
```

**Voir :** [docs/SECURITY.md](docs/SECURITY.md)

</details>

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
