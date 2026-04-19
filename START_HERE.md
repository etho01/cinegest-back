# 🎯 Guide de Navigation - Pour Recruteurs & Tech Leads

> **Bienvenue !** Ce guide vous aidera à explorer efficacement ce projet et identifier rapidement les compétences techniques démontrées.

---

## ⚡ Démarrage Rapide (2 min)

**Si vous n'avez que quelques minutes**, consultez dans cet ordre :

1. **[PORTFOLIO.md](PORTFOLIO.md)** (5 min) → Vue d'ensemble technique complète
2. **[README.md](README.md)** (3 min) → Présentation générale du projet
3. **[TECHNICAL_HIGHLIGHTS.md](TECHNICAL_HIGHLIGHTS.md)** (10 min) → Exemples de code concrets

---

## 📊 Que Cherchez-Vous ?

Choisissez selon votre focus d'évaluation :

### 🏗️ Architecture & Design Patterns

**Vous évaluez :** Clean Architecture, DDD, SOLID principles

**Consultez :**
- 📄 [PORTFOLIO.md - Section Architecture](PORTFOLIO.md#1-architecture-polyvalente) (2 min)
- 📄 [TECHNICAL_HIGHLIGHTS.md - Clean Architecture](TECHNICAL_HIGHLIGHTS.md#1-clean-architecture---implémentation-complète) (5 min)
- 📁 [app/Domain/](app/Domain/) - Entités métier pures
- 📁 [app/Application/](app/Application/) - Use Cases & DTOs
- 📁 [app/Infrastructure/](app/Infrastructure/) - Implémentations
- 📄 [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) - Documentation détaillée

**Fichiers clés à examiner :**
```
app/Domain/Entity/Booking.php          → Entité métier pure (0 dépendance framework)
app/Domain/ValueObject/Email.php       → Value Object avec validation
app/Domain/Repository/BookingRepositoryInterface.php  → Contrat (interface)
app/Infrastructure/Persistence/Eloquent/EloquentBookingRepository.php → Implémentation
app/Infrastructure/Persistence/Mapper/BookingMapper.php → Conversion Domain ↔ Eloquent
```

---

### ⚡ Laravel (Niveau Expert)

**Vous évaluez :** Eloquent, API Resources, Middleware, Events, Queue

**Consultez :**
- 📄 [README.md - Compétences Laravel](README.md#-compétences-démontrées) (3 min)
- 📁 [app/Http/Controllers/](app/Http/Controllers/) - Controllers avec injection de dépendances
- 📁 [app/Http/Resources/](app/Http/Resources/) - API Resources
- 📁 [app/Http/Requests/](app/Http/Requests/) - Form Requests (validation)
- 📁 [app/Listeners/](app/Listeners/) - Event Listeners (Stripe webhooks)
- 📁 [app/Models/](app/Models/) - Eloquent Models avec relations

**Fichiers clés :**
```
app/Http/Controllers/Api/Site/BookingController.php → Controller + DI
app/Http/Resources/BookingResource.php → API Resource transformation
app/Http/Requests/BookingPaymentIntentRequest.php → Validation
app/Listeners/StripeWebhookListener.php → Webhook handling
app/Models/Booking.php → Eloquent avec relations & scopes
```

---

### ☸️ DevOps & Kubernetes

**Vous évaluez :** Kubernetes, CI/CD, Docker, Security

**Consultez :**
- 📄 [PORTFOLIO.md - Infrastructure](PORTFOLIO.md#2-infrastructure-production-ready) (3 min)
- 📄 [TECHNICAL_HIGHLIGHTS.md - Kubernetes](TECHNICAL_HIGHLIGHTS.md#6-kubernetes---configuration-production-ready) (7 min)
- 📄 [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) - Infrastructure détaillée (15 min)
- 📁 [k8s/](k8s/) - Manifests Kubernetes
- 📁 [.github/workflows/](.github/workflows/) - Pipeline CI/CD
- 📄 [Dockerfile](Dockerfile) - Multi-stage build

**Configuration clés :**
```
k8s/deployment.yaml          → SecurityContext, Health checks, Resources
k8s/externalsecret.yaml      → Sync Vault → Kubernetes (zéro secret Git)
k8s/ingress.yaml             → SSL/TLS + Rate limiting
k8s/hpa.yaml                 → Auto-scaling
.github/workflows/deploy.yml → Pipeline CI/CD complet
Dockerfile                   → Multi-stage optimisé
```

**Points forts :**
- ✅ SecurityContext (runAsUser: 33, readOnlyRootFilesystem)
- ✅ Zero-downtime deployments (RollingUpdate)
- ✅ HashiCorp Vault (secrets jamais dans Git)
- ✅ External Secrets Operator (auto-refresh 15min)
- ✅ SSL/TLS automatique (Let's Encrypt)
- ✅ Health checks (liveness + readiness)
- ✅ HPA (Horizontal Pod Autoscaling)

---

### 💳 Intégrations APIs Externes

**Vous évaluez :** Stripe, Webhooks, APIs tierces

**Consultez :**
- 📄 [PORTFOLIO.md - Intégrations](PORTFOLIO.md#3-intégrations-externes-complexes) (2 min)
- 📁 [app/Listeners/StripeWebhookListener.php](app/Listeners/StripeWebhookListener.php) - Webhook Stripe
- 📁 [app/Services/](app/Services/) - Services d'intégration
- 📁 [app/UseCase/Site/Booking/](app/UseCase/Site/Booking/) - Use Cases Stripe

**Fichiers clés :**
```
app/Listeners/StripeWebhookListener.php → Validation signature + handling
app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php → Payment Intent
app/UseCase/Site/Booking/ConfirmBookingPayment.php → Confirmation + Email
```

---

### 🧪 Tests & Qualité

**Vous évaluez :** Tests unitaires, tests d'intégration, TDD

**Consultez :**
- 📄 [PORTFOLIO.md - Tests](PORTFOLIO.md#4-tests--qualité-de-code) (1 min)
- 📁 [tests/Unit/](tests/Unit/) - Tests unitaires
- 📁 [tests/Feature/](tests/Feature/) - Tests d'intégration
- 📄 [phpunit.xml](phpunit.xml) - Configuration PHPUnit

**Coverage :** 80%+ (tests unitaires + intégration)

---

### 🔒 Sécurité

**Vous évaluez :** Security-first approach, Kubernetes Security

**Consultez :**
- 📄 [docs/SECURITY.md](docs/SECURITY.md) - Pratiques de sécurité (10 min)
- 📄 [k8s/deployment.yaml](k8s/deployment.yaml) - SecurityContext
- 📄 [TECHNICAL_HIGHLIGHTS.md - Kubernetes Security](TECHNICAL_HIGHLIGHTS.md#6-kubernetes---configuration-production-ready)

**Mesures appliquées :**
```
✅ SecurityContext (runAsNonRoot, readOnlyRootFilesystem)
✅ Capabilities dropped (principe least privilege)
✅ Secrets dans Vault (jamais dans Git)
✅ SSL/TLS automatique (Let's Encrypt)
✅ Rate limiting API (Traefik middleware)
✅ Security headers HTTP
✅ Network policies
✅ CSRF, XSS, SQL injection protection
```

---

## 🎯 Par Niveau de Détail

### 🏃 Rapide (5-10 min)

Si vous voulez comprendre rapidement le projet :

1. [PORTFOLIO.md](PORTFOLIO.md) (5 min) → Points forts + Stack technique
2. [README.md - Highlights](README.md#-highlights---ce-qui-rend-ce-projet-unique) (2 min)
3. Parcourir [app/Domain/](app/Domain/) (3 min) → Voir la Clean Architecture

**Total : ~10 minutes**

---

### 📚 Approfondi (30-45 min)

Pour une évaluation technique complète :

1. [PORTFOLIO.md](PORTFOLIO.md) (5 min) → Vue d'ensemble
2. [TECHNICAL_HIGHLIGHTS.md](TECHNICAL_HIGHLIGHTS.md) (15 min) → Exemples de code
3. Examiner les fichiers clés :
   - [app/Domain/Entity/Booking.php](app/Domain/Entity/Booking.php) (2 min)
   - [app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php](app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php) (3 min)
   - [k8s/deployment.yaml](k8s/deployment.yaml) (3 min)
   - [.github/workflows/deploy.yml](.github/workflows/deploy.yml) (2 min)
4. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) (10 min) → Infrastructure détaillée
5. [docs/SECURITY.md](docs/SECURITY.md) (5 min) → Sécurité

**Total : ~45 minutes**

---

### 🔬 Exhaustif (1-2h)

Pour une revue de code complète :

1. Lire toute la documentation (30 min)
2. Explorer l'arborescence du code :
   - [app/Domain/](app/Domain/) → Entités, Value Objects, Repositories
   - [app/Application/](app/Application/) → Use Cases, DTOs
   - [app/Infrastructure/](app/Infrastructure/) → Implémentations
   - [app/Http/](app/Http/) → Controllers, Resources, Middleware
3. Examiner les tests :
   - [tests/Unit/](tests/Unit/)
   - [tests/Feature/](tests/Feature/)
4. Analyser l'infrastructure :
   - [k8s/](k8s/) → Tous les manifests
   - [.github/workflows/](.github/workflows/) → CI/CD
5. Cloner et tester localement :
   ```bash
   git clone <repo-url>
   docker-compose up -d
   docker-compose exec app composer install
   docker-compose exec app php artisan test
   ```

**Total : ~2 heures**

---

## 📁 Structure du Projet (Référence Rapide)

```
cinegest-back/
├── 📄 PORTFOLIO.md                  ← Points forts techniques (COMMENCEZ ICI)
├── 📄 TECHNICAL_HIGHLIGHTS.md       ← Exemples de code détaillés
├── 📄 README.md                     ← Vue d'ensemble générale
├── 📄 START_HERE.md                 ← Ce fichier
│
├── app/
│   ├── Domain/                      ← Clean Architecture (0 dépendance framework)
│   │   ├── Entity/                  → Entités métier pures
│   │   ├── ValueObject/             → Value Objects immuables
│   │   └── Repository/              → Interfaces (contrats)
│   │
│   ├── Application/                 ← Use Cases & DTOs
│   │   ├── DTO/                     → Data Transfer Objects
│   │   └── UseCase/                 → Logique applicative
│   │
│   ├── Infrastructure/              ← Implémentations techniques
│   │   └── Persistence/
│   │       ├── Eloquent/            → Repositories Eloquent
│   │       └── Mapper/              → Conversion Domain ↔ Eloquent
│   │
│   ├── Http/                        ← Controllers, Resources, Requests
│   │   ├── Controllers/             → API Controllers
│   │   ├── Resources/               → API Resources (transformation)
│   │   └── Requests/                → Form Requests (validation)
│   │
│   ├── Models/                      ← Eloquent Models
│   ├── Repository/                  ← Repositories (Laravel classique)
│   ├── UseCase/                     ← Use Cases (Laravel classique)
│   └── Listeners/                   ← Event Listeners (Stripe webhooks)
│
├── tests/
│   ├── Unit/                        ← Tests unitaires (Domain, Services)
│   └── Feature/                     ← Tests d'intégration (API endpoints)
│
├── k8s/                             ← Kubernetes manifests
│   ├── deployment.yaml              → Deployment (SecurityContext, Health checks)
│   ├── externalsecret.yaml          → Sync Vault → Kubernetes
│   ├── ingress.yaml                 → SSL/TLS + Rate limiting
│   ├── hpa.yaml                     → Auto-scaling
│   └── vault/                       → Configuration Vault
│
├── .github/workflows/               ← Pipeline CI/CD
│   └── deploy.yml                   → Tests → Build → Deploy
│
├── docs/                            ← Documentation détaillée
│   ├── ARCHITECTURE.md              → Infrastructure CI/CD complète
│   ├── SECURITY.md                  → Pratiques de sécurité
│   └── ...
│
├── Dockerfile                       ← Multi-stage build optimisé
├── docker-compose.yml               ← Dev local
├── phpunit.xml                      ← Configuration tests
└── composer.json                    ← Dépendances PHP
```

---

## 🎖️ Compétences Clés Démontrées

### Backend (PHP/Laravel)
- ✅ Clean Architecture & DDD
- ✅ SOLID Principles
- ✅ Design Patterns (Repository, DTO, Value Objects, Mapper)
- ✅ Laravel 11 (Eloquent, API Resources, Events, Queue)
- ✅ PHP 8.2+ (typage strict, attributes, enums)
- ✅ Tests automatisés (PHPUnit - 80%+ coverage)

### DevOps & Infrastructure
- ✅ Kubernetes (Deployment, Service, Ingress, HPA, SecurityContext)
- ✅ HashiCorp Vault (secret management)
- ✅ External Secrets Operator (sync automatique)
- ✅ GitHub Actions (CI/CD complet)
- ✅ Docker (multi-stage, optimisé)
- ✅ SSL/TLS automatique (Let's Encrypt)
- ✅ Zero-downtime deployments

### Sécurité
- ✅ SecurityContext (non-root, read-only FS)
- ✅ Secrets management (Vault - zéro secret Git)
- ✅ Rate limiting & security headers
- ✅ Health checks & monitoring
- ✅ CSRF, XSS, SQL injection protection

### Intégrations
- ✅ Stripe (paiements + webhooks)
- ✅ TMDB API (films + cache)
- ✅ Mailjet (emails transactionnels)

---

## 🚀 Tester le Projet Localement

### Option 1 : Docker (Rapide - 5 min)

```bash
# Cloner le projet
git clone <repo-url>
cd cinegest-back

# Démarrer l'environnement
docker-compose up -d

# Installer les dépendances
docker-compose exec app composer install

# Générer la clé
docker-compose exec app php artisan key:generate

# Migrations
docker-compose exec app php artisan migrate --seed

# Tests
docker-compose exec app php artisan test

# API disponible
curl http://localhost:8000/up
```

### Option 2 : Kubernetes (Complet - 10 min)

```bash
# Installation automatique complète
./setup-infrastructure.sh

# OU suivre le guide manuel
# Voir QUICKSTART.md
```

---

## 📞 Questions Fréquentes

### Pourquoi deux architectures (Clean Architecture + Laravel MVC) ?

Démontrer la capacité à choisir l'architecture adaptée au contexte :
- **Clean Architecture** → Logique complexe (réservation/paiement) nécessitant maintenabilité
- **Laravel MVC** → CRUD simples (administration) où la rapidité de développement prime

### Le projet est-il en production ?

Ce projet démontre une configuration **production-ready** complète (Kubernetes, Vault, CI/CD), mais peut être adapté selon vos besoins.

### Couverture de tests ?

**80%+** de couverture avec tests unitaires (Domain, Services) et tests d'intégration (API endpoints).

### Temps de développement ?

Projet développé sur ~3-4 semaines (architecture + développement + infrastructure + documentation).

---

## 📧 Contact

**Développeur :** [Votre Nom]  
**Email :** [Votre email]  
**LinkedIn :** [Lien vers profil]  
**GitHub :** [Lien vers profil]

---

<div align="center">

**🌟 Merci d'évaluer ce projet ! 🌟**

**N'hésitez pas à me contacter pour toute question ou clarification.**

---

📄 [PORTFOLIO.md](PORTFOLIO.md) • 💻 [TECHNICAL_HIGHLIGHTS.md](TECHNICAL_HIGHLIGHTS.md) • 📚 [README.md](README.md)

</div>
