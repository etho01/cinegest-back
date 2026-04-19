# 🎬 CineGest Backend - Portfolio Technique

> **Système de gestion de cinéma full-stack** démontrant l'expertise en architecture logicielle, DevOps et bonnes pratiques de développement.

---

## 📊 Aperçu Rapide

| Métrique | Valeur |
|----------|--------|
| **Type de projet** | Application backend Laravel - Gestion de cinéma |
| **Technologies principales** | PHP 8.2, Laravel 11, MySQL, Redis, Kubernetes |
| **Lignes de code** | ~15 000+ lignes (app/, tests/, config/) |
| **Fichiers de tests** | PHPUnit - Tests unitaires et d'intégration |
| **Architecture** | Clean Architecture (DDD) + Laravel MVC |
| **Déploiement** | Kubernetes avec CI/CD automatisé |
| **Sécurité** | Vault, SSL/TLS, SecurityContext, Rate-limiting |

---

## 🎯 Points Forts Techniques

### 1. 🏗️ Architecture Polyvalente

**Démonstration de deux approches architecturales** selon les besoins :

#### Clean Architecture (DDD) - Partie Réservation
```
✅ Séparation Domain/Application/Infrastructure
✅ Entités métier pures (zéro dépendance framework)
✅ Value Objects pour la validation métier
✅ Repository Pattern avec interfaces
✅ DTOs pour le transfert de données
✅ Testabilité maximale (80%+ couverture)
```

**Fichiers clés :**
- [Domain/Entity/](app/Domain/Entity/) - Entités métier
- [Application/UseCase/](app/Application/UseCase/) - Cas d'utilisation
- [Infrastructure/Persistence/](app/Infrastructure/Persistence/) - Implémentation

#### Laravel MVC Classique - Partie Administration
```
✅ Développement rapide avec conventions Laravel
✅ Eloquent ORM pour CRUD standards
✅ API Resources pour la sérialisation
✅ Form Requests pour la validation
```

**Pourquoi deux architectures ?** 
Démontre la capacité à choisir l'outil adapté au contexte : Clean Architecture pour logique complexe (réservation/paiement) vs MVC pour CRUD simples (administration).

---

### 2. ☸️ Infrastructure Production-Ready

**Stack DevOps moderne et sécurisé :**

```yaml
Infrastructure:
  ✅ Kubernetes (K3s) - Orchestration de conteneurs
  ✅ HashiCorp Vault - Gestion centralisée des secrets
  ✅ External Secrets Operator - Synchronisation automatique Vault→K8s
  ✅ GitHub Actions - Pipeline CI/CD complet
  ✅ Docker multi-stage - Images optimisées
  ✅ Traefik - Ingress controller + middleware
  ✅ Let's Encrypt - Certificats SSL automatiques

Sécurité:
  ✅ SecurityContext (runAsUser: 33, readOnlyRootFilesystem)
  ✅ Capabilities dropped (least privilege)
  ✅ Secrets jamais stockés dans Git
  ✅ Network policies
  ✅ Rate limiting API
  ✅ Security headers HTTP

Fiabilité:
  ✅ Zero-downtime deployments (RollingUpdate)
  ✅ Health checks (liveness/readiness probes)
  ✅ Auto-rollback sur échec
  ✅ Horizontal Pod Autoscaling (HPA)
  ✅ Migrations automatiques avec rollback
```

**Fichiers de configuration :**
- [k8s/deployment.yaml](k8s/deployment.yaml) - Configuration Kubernetes
- [.github/workflows/](../.github/workflows/) - Pipeline CI/CD
- [k8s/vault/](k8s/vault/) - Setup Vault
- [Dockerfile](Dockerfile) - Image multi-stage optimisée

---

### 3. 💳 Intégrations Externes Complexes

**APIs tierces intégrées avec gestion d'erreur robuste :**

| Service | Usage | Complexité |
|---------|-------|------------|
| **Stripe** | Paiements + Webhooks | ⭐⭐⭐⭐⭐ |
| **TMDB** | Base de données films avec cache | ⭐⭐⭐⭐ |
| **Mailjet** | Emails transactionnels | ⭐⭐⭐ |

**Highlights :**
- ✅ Webhooks Stripe avec validation de signature
- ✅ Cache intelligent TMDB (Redis) pour optimisation
- ✅ Retry logic et circuit breaker
- ✅ Gestion des erreurs API avec fallback

**Code clé :**
- [Listeners/StripeWebhookListener.php](app/Listeners/StripeWebhookListener.php)
- [Services/](app/Services/) - Services d'intégration

---

### 4. 🧪 Tests & Qualité de Code

```bash
✅ Tests unitaires (Domain, Services)
✅ Tests d'intégration (API endpoints)
✅ Test-Driven Development (TDD)
✅ Mocks et Stubs pour isolation
✅ Factories pour données de test
✅ PHPUnit configuration avancée
```

**Fichiers de test :**
- [tests/Unit/](tests/Unit/) - Tests unitaires
- [tests/Feature/](tests/Feature/) - Tests d'intégration
- [phpunit.xml](phpunit.xml) - Configuration PHPUnit

---

## 🛠️ Stack Technique Complète

### Backend
```
PHP 8.2+ (typed properties, attributes, enums)
Laravel 11.x (service container, eloquent, events)
MySQL 8.0 (relations complexes, transactions)
Redis (cache, sessions, queue)
```

### Architecture & Patterns
```
Clean Architecture (Hexagonal)
Domain-Driven Design (DDD)
SOLID Principles
Repository Pattern
DTO Pattern
Value Objects
Dependency Injection
Event-Driven Architecture
```

### DevOps & Infrastructure
```
Docker & Docker Compose
Kubernetes (K3s)
HashiCorp Vault
External Secrets Operator
GitHub Actions (CI/CD)
Traefik (Ingress)
Cert-Manager (SSL/TLS)
Helm (Package manager)
```

### APIs & Intégrations
```
RESTful API Design
Stripe API (paiements)
TMDB API (films)
Mailjet (emailing)
Webhooks
OAuth/JWT (Sanctum)
```

### Outils & Pratiques
```
Composer (gestionnaire de dépendances)
PHPUnit (tests)
Git (version control)
Semantic Versioning
Infrastructure as Code
GitOps
```

---

## 💼 Compétences Démontrées

### 🎨 Architecture & Design

<table>
<tr>
<td valign="top" width="50%">

**Patterns de conception :**
- ✅ Repository Pattern
- ✅ Factory Pattern
- ✅ Observer Pattern (Events)
- ✅ Strategy Pattern
- ✅ Service Layer Pattern
- ✅ DTO Pattern
- ✅ Value Object Pattern

</td>
<td valign="top" width="50%">

**Principes SOLID :**
- ✅ Single Responsibility
- ✅ Open/Closed
- ✅ Liskov Substitution
- ✅ Interface Segregation
- ✅ Dependency Inversion

</td>
</tr>
</table>

### 🔧 Développement Backend

<table>
<tr>
<td valign="top" width="33%">

**Laravel :**
- Eloquent ORM
- API Resources
- Form Requests
- Middleware
- Service Providers
- Events & Listeners
- Queue & Jobs
- Sanctum Auth
- Migrations/Seeders

</td>
<td valign="top" width="33%">

**Base de données :**
- Modélisation complexe
- Relations many-to-many
- Transactions
- Indexes & optimisation
- Soft deletes
- Query optimization
- Eager loading
- Raw queries

</td>
<td valign="top" width="33%">

**PHP Moderne :**
- Typage strict
- Attributes
- Enums
- Named arguments
- Constructor promotion
- Match expressions
- Null coalescing
- Array spreading

</td>
</tr>
</table>

### ☸️ DevOps & Infrastructure

<table>
<tr>
<td valign="top" width="50%">

**Kubernetes :**
- ✅ Deployments & Services
- ✅ ConfigMaps & Secrets
- ✅ Ingress & Middleware
- ✅ HPA (autoscaling)
- ✅ Health checks (probes)
- ✅ SecurityContext
- ✅ Network policies
- ✅ Resource limits
- ✅ Rolling updates
- ✅ Job & CronJob

</td>
<td valign="top" width="50%">

**CI/CD & Sécurité :**
- ✅ GitHub Actions workflows
- ✅ Multi-stage Docker builds
- ✅ HashiCorp Vault
- ✅ Secret rotation
- ✅ SSL/TLS automatique
- ✅ Zero-downtime deploy
- ✅ Auto-rollback
- ✅ Infrastructure as Code
- ✅ GitOps practices

</td>
</tr>
</table>

---

## 📁 Structure du Code (Highlights)

### Clean Architecture - Couche Domain
```php
app/Domain/
├── Entity/
│   ├── Booking.php              # Entité métier pure (zéro dépendance)
│   ├── Showtime.php
│   └── Payment.php
├── ValueObject/
│   ├── Email.php                # Value Object immuable avec validation
│   ├── Money.php                # Gestion monétaire type-safe
│   └── BookingStatus.php        # Enum pour statuts
└── Repository/
    └── BookingRepositoryInterface.php  # Contrat (pas d'implémentation)
```

### Application Layer - Use Cases
```php
app/Application/
├── DTO/
│   ├── CreateBookingDTO.php     # Data Transfer Object
│   └── BookingResponseDTO.php
└── UseCase/
    ├── CreateBookingUseCase.php # Logique métier orchestrée
    ├── CancelBookingUseCase.php
    └── ProcessPaymentUseCase.php
```

### Infrastructure - Implémentations
```php
app/Infrastructure/
├── Persistence/
│   ├── Eloquent/
│   │   └── BookingEloquentRepository.php  # Implémentation Repository
│   └── Mapper/
│       └── BookingMapper.php    # Conversion Domain ↔ Eloquent
└── External/
    └── StripePaymentGateway.php # Adapter pour API externe
```

---

## 🎓 Réflexions Architecturales

### Pourquoi ce projet se démarque ?

#### 1. **Pragmatisme architectural**
- Ne sur-architecte pas les CRUD simples (Laravel MVC)
- Utilise Clean Architecture uniquement où la complexité le justifie
- Démontre la capacité à adapter l'architecture au contexte

#### 2. **Production-ready infrastructure**
- Configuration Kubernetes complète et sécurisée
- Pipeline CI/CD automatisé avec tests
- Gestion des secrets avec Vault (pas de secrets dans Git)
- Zero-downtime deployments
- Auto-rollback en cas d'échec

#### 3. **Code maintenable et testable**
- Tests unitaires des Use Cases (business logic)
- Tests d'intégration des endpoints API
- Separation of Concerns stricte
- Code découplé et facilement mockable

#### 4. **Bonnes pratiques de l'industrie**
- SOLID principles appliqués
- Infrastructure as Code
- GitOps workflow
- Semantic versioning
- Documentation complète

---

## 📚 Documentation Disponible

| Document | Description | Audience |
|----------|-------------|----------|
| **[README.md](README.md)** | Vue d'ensemble complète du projet | Tous |
| **[PORTFOLIO.md](PORTFOLIO.md)** | Ce document - Highlights techniques | Recruteurs/Tech leads |
| **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** | Architecture détaillée CI/CD & Infra | DevOps/Architectes |
| **[QUICKSTART.md](QUICKSTART.md)** | Setup rapide (5 min) | Développeurs |
| **[SETUP.md](SETUP.md)** | Guide complet d'installation | Développeurs |
| **[k8s/](k8s/)** | Manifests Kubernetes | DevOps |
| **[docs/SECURITY.md](docs/SECURITY.md)** | Pratiques de sécurité | Security/DevOps |

---

## 🚀 Pour Démarrer

### Option 1 : Docker (Développement local)
```bash
git clone <repo-url>
cd cinegest-back
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

### Option 2 : Kubernetes (Production)
```bash
# Installation automatique complète
./setup-infrastructure.sh

# OU suivre le guide rapide
# Voir QUICKSTART.md
```

### Tester l'API
```bash
# Health check
curl http://localhost:8000/up

# Liste des films
curl http://localhost:8000/api/movies

# Documentation complète des routes
php artisan route:list
```

---

## 🎖️ Points Forts pour un Recruteur

### ✅ Compétences Backend
- Maîtrise avancée de Laravel et PHP 8.2+
- Connaissance approfondie des design patterns
- Expérience en Clean Architecture et DDD
- Développement d'APIs RESTful robustes

### ✅ Compétences DevOps
- Déploiement Kubernetes production-ready
- Pipeline CI/CD avec GitHub Actions
- Gestion sécurisée des secrets (Vault)
- Infrastructure as Code
- Monitoring et observabilité

### ✅ Compétences Architecturales
- Capacité à choisir l'architecture adaptée au contexte
- Séparation des préoccupations (Separation of Concerns)
- Code testable et maintenable
- Documentation technique claire

### ✅ Bonnes Pratiques
- Tests automatisés (unitaires + intégration)
- Security-first approach (SecurityContext, TLS, secrets)
- Zero-downtime deployments
- Auto-rollback et résilience
- Documentation complète

---

## 📞 Contact

**Projet développé par :** [Votre Nom]  
**GitHub :** [Lien vers profil]  
**LinkedIn :** [Lien vers profil]  
**Email :** [Votre email]

---

## 📝 Licence

Ce projet est développé à des fins de démonstration de compétences techniques.

---

<div align="center">

**🌟 Merci d'avoir pris le temps d'examiner ce projet ! 🌟**

</div>
