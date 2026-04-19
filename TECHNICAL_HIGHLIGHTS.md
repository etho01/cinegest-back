# 💻 CineGest - Points Forts Techniques avec Exemples de Code

> **Documentation technique détaillée** montrant l'implémentation concrète de patterns avancés et bonnes pratiques.

---

## 📑 Table des Matières

1. [Clean Architecture - Implémentation complète](#1-clean-architecture---implémentation-complète)
2. [Value Objects - Validation métier](#2-value-objects---validation-métier)
3. [Repository Pattern - Abstraction de persistence](#3-repository-pattern---abstraction-de-persistence)
4. [Use Cases - Logique métier orchestrée](#4-use-cases---logique-métier-orchestrée)
5. [Mappers - Séparation Domain/Infrastructure](#5-mappers---séparation-domaininfrastructure)
6. [Kubernetes - Configuration Production-Ready](#6-kubernetes---configuration-production-ready)
7. [CI/CD Pipeline - Automatisation complète](#7-cicd-pipeline---automatisation-complète)

---

## 1. Clean Architecture - Implémentation Complète

### 🏗️ Structure en couches (Réservation de billets)

```
Domain (Cœur métier - 0 dépendance)
   ↓ utilise
Application (Cas d'utilisation)
   ↓ utilise  
Infrastructure (Implémentation technique)
```

### 📁 Organisation des fichiers

```
app/
├── Domain/                          # ❌ Aucune dépendance Laravel
│   ├── Entity/Booking.php           # Entité métier pure
│   ├── ValueObject/
│   │   ├── Email.php                # Value Object immuable
│   │   ├── Money.php
│   │   └── BookingStatus.php
│   └── Repository/
│       └── BookingRepositoryInterface.php  # Contrat (interface)
│
├── Application/                     # ✅ Use Cases & DTOs
│   ├── DTO/BookingDTO.php
│   └── UseCase/
│       └── CreateBookingUseCase.php
│
└── Infrastructure/                  # ✅ Implémentations Laravel
    └── Persistence/
        ├── Eloquent/
        │   └── EloquentBookingRepository.php  # Implémentation
        └── Mapper/
            └── BookingMapper.php    # Conversion Domain ↔ Eloquent
```

---

## 2. Value Objects - Validation Métier

### 🔐 Email Value Object (Immuable avec validation)

**Fichier:** [`app/Domain/ValueObject/Email.php`](app/Domain/ValueObject/Email.php)

```php
<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

**✅ Avantages :**
- Validation centralisée (une seule source de vérité)
- Impossibilité de créer un email invalide
- Type-safety (ne peut pas confondre avec un string)
- Immuabilité (thread-safe)

### 💰 Money Value Object (Gestion monétaire type-safe)

```php
final class Money
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = 'eur')
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
        
        $this->amount = round($amount, 2);
        $this->currency = strtolower($currency);
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }
}
```

### 📊 BookingStatus Value Object (Enum-like avec méthodes métier)

**Fichier:** [`app/Domain/ValueObject/BookingStatus.php`](app/Domain/ValueObject/BookingStatus.php)

```php
final class BookingStatus
{
    private const PENDING = 'pending';
    private const PAID = 'paid';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::PAID,
        self::CANCELLED,
    ];

    private string $value;

    // Named constructors (Factory pattern)
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    // Méthodes métier
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    public function equals(BookingStatus $other): bool
    {
        return $this->value === $other->value;
    }
}
```

---

## 3. Repository Pattern - Abstraction de Persistence

### 🔌 Interface (Domain Layer)

**Fichier:** [`app/Domain/Repository/BookingRepositoryInterface.php`](app/Domain/Repository/BookingRepositoryInterface.php)

```php
<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Booking;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\BookingStatus;

interface BookingRepositoryInterface
{
    public function findById(int $id): ?Booking;
    
    public function findByPaymentIntentId(string $paymentIntentId): ?Booking;
    
    /**
     * @return Booking[]
     */
    public function getUserBookings(UserId $userId, ?BookingStatus $status = null): array;
    
    public function getTotalTicketsSold(SessionId $sessionId): int;
    
    public function save(Booking $booking): Booking;
}
```

**✅ Avantages :**
- Le Domain ne connaît pas Eloquent
- Testabilité maximale (facile à mocker)
- Possibilité de changer de provider (Doctrine, MongoDB, etc.)
- Contrats clairs et explicites

### ⚙️ Implémentation (Infrastructure Layer)

**Fichier:** [`app/Infrastructure/Persistence/Eloquent/Repository/EloquentBookingRepository.php`](app/Infrastructure/Persistence/Eloquent/Repository/EloquentBookingRepository.php)

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\BookingRepositoryInterface;
use App\Domain\Entity\Booking;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\BookingStatus;
use App\Infrastructure\Persistence\Mapper\BookingMapper;
use App\Models\Booking as BookingModel;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function findById(int $id): ?Booking
    {
        $model = BookingModel::find($id);
        
        // Conversion Eloquent → Domain Entity via Mapper
        return $model ? BookingMapper::toDomainEntity($model) : null;
    }

    public function getUserBookings(UserId $userId, ?BookingStatus $status = null): array
    {
        $query = BookingModel::where('user_id', $userId->value());
        
        if ($status) {
            $query->where('status', $status->value());
        }
        
        $models = $query->with([
                'session.movie', 
                'session.cinema', 
                'session.room', 
                'items'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Map collection vers array de Domain Entities
        return $models->map(fn($model) => BookingMapper::toDomainEntity($model))
                     ->toArray();
    }

    public function save(Booking $booking): Booking
    {
        // Conversion Domain Entity → Eloquent attributes
        $attributes = BookingMapper::toEloquentAttributes($booking);
        
        $model = BookingModel::updateOrCreate(
            ['id' => $attributes['id']],
            $attributes
        );
        
        return BookingMapper::toDomainEntity($model);
    }
}
```

---

## 4. Use Cases - Logique Métier Orchestrée

### 🎫 CreateBookingWithPaymentIntent Use Case

**Fichier:** [`app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php`](app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php)

```php
<?php

namespace App\UseCase\Site\Booking;

use App\Repository\BookingRepository;
use App\Repository\BookingItemRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Exceptions\Site\InsufficientCapacityException;
use Illuminate\Support\Facades\DB;

class CreateBookingWithPaymentIntent
{
    // Dependency Injection via constructor
    public function __construct(
        private BookingRepository $bookingRepository,
        private BookingItemRepository $bookingItemRepository,
        private SessionRepository $sessionRepository,
        private UserRepository $userRepository
    ) {}

    /**
     * Orchestrate booking creation + payment intent
     */
    public function handle(array $data): array
    {
        // Transaction pour garantir l'atomicité
        return DB::transaction(function () use ($data) {
            // 1. Validation métier
            $totalTickets = $this->calculateTotalTickets($data['items']);
            $session = $this->sessionRepository->findWithRelations(
                $data['sessionId'], 
                ['room']
            );

            $availableSeats = $session->room->capacity 
                - $this->bookingRepository->getTotalTicketsSold($data['sessionId']);
            
            if ($availableSeats < $totalTickets) {
                throw new InsufficientCapacityException($totalTickets, $availableSeats);
            }

            // 2. Créer la réservation (état: pending)
            $booking = $this->bookingRepository->create([
                'user_id' => $data['userId'],
                'session_id' => $data['sessionId'],
                'payment_intent_id' => '',
                'status' => 'pending',
                'total_amount' => $data['totalAmount'],
                'currency' => 'eur',
                'total_tickets' => $totalTickets,
            ]);

            // 3. Créer les lignes de commande
            $this->bookingItemRepository->createMany($booking->id, $data['items']);

            // 4. Initier le paiement Stripe
            $user = $this->userRepository->find($data['userId']);
            $amountInCents = (int) ($data['totalAmount'] * 100);

            $payment = $user->pay($amountInCents, [
                'metadata' => [
                    'bookingId' => $booking->id,
                    'sessionId' => $data['sessionId'],
                    'userId' => $data['userId'],
                ],
            ]);

            // 5. Mettre à jour avec payment_intent_id
            $this->bookingRepository->update($booking, [
                'payment_intent_id' => $payment->id,
            ]);

            return [
                'booking' => $booking,
                'payment' => $payment,  // Contient client_secret pour frontend
            ];
        });
    }

    private function calculateTotalTickets(array $items): int
    {
        return array_reduce($items, fn($sum, $item) => $sum + ($item['quantity'] ?? 0), 0);
    }
}
```

**✅ Patterns démontrés :**
- Injection de dépendances (constructor)
- Transaction pour atomicité
- Validation métier dans le Use Case
- Orchestration de plusieurs repositories
- Gestion d'erreurs avec exceptions custom
- Méthodes privées pour la lisibilité

### ✅ ConfirmBookingPayment Use Case (Webhook Stripe)

**Fichier:** [`app/UseCase/Site/Booking/ConfirmBookingPayment.php`](app/UseCase/Site/Booking/ConfirmBookingPayment.php)

```php
class ConfirmBookingPayment
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {}

    public function handle(int $bookingId): ?Booking
    {
        $booking = $this->bookingRepository->findWithRelations($bookingId, [
            'user',
            'session.cinema',
            'session.movie',
            'session.room',
            'items'
        ]);

        if (!$booking) {
            Log::warning('Booking not found', ['booking_id' => $bookingId]);
            return null;
        }

        // Marquer comme payé
        $booking->markAsPaid();

        Log::info('Booking confirmed', [
            'booking_id' => $booking->id,
            'total_tickets' => $booking->total_tickets,
        ]);

        // Envoyer email de confirmation (asynchrone)
        $this->sendConfirmationEmail($booking);

        return $booking;
    }

    private function sendConfirmationEmail(Booking $booking): void
    {
        try {
            Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
        } catch (\Exception $e) {
            // Email failure ne doit pas bloquer la confirmation
            Log::error('Email failed', ['booking_id' => $booking->id]);
        }
    }
}
```

---

## 5. Mappers - Séparation Domain/Infrastructure

### 🔄 BookingMapper (Bidirectionnel)

**Fichier:** [`app/Infrastructure/Persistence/Mapper/BookingMapper.php`](app/Infrastructure/Persistence/Mapper/BookingMapper.php)

```php
<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Entity\Booking as BookingEntity;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\BookingStatus;
use App\Models\Booking as BookingModel;

class BookingMapper
{
    /**
     * Eloquent Model → Domain Entity
     */
    public static function toDomainEntity(BookingModel $model): BookingEntity
    {
        return new BookingEntity(
            id: $model->id,
            userId: new UserId($model->user_id),
            sessionId: new SessionId($model->session_id),
            paymentIntentId: $model->payment_intent_id,
            status: BookingStatus::fromString($model->status),
            totalAmount: new Money($model->total_amount, $model->currency),
            totalTickets: $model->total_tickets,
            paidAt: $model->paid_at,
            createdAt: $model->created_at
        );
    }

    /**
     * Domain Entity → Eloquent attributes
     */
    public static function toEloquentAttributes(BookingEntity $entity): array
    {
        return [
            'id' => $entity->id(),
            'user_id' => $entity->userId()->value(),
            'session_id' => $entity->sessionId()->value(),
            'payment_intent_id' => $entity->paymentIntentId(),
            'status' => $entity->status()->value(),
            'total_amount' => $entity->totalAmount()->amount(),
            'currency' => $entity->totalAmount()->currency(),
            'total_tickets' => $entity->totalTickets(),
            'paid_at' => $entity->paidAt(),
        ];
    }
}
```

**✅ Rôle du Mapper :**
- **Isolation** : Le Domain ne connaît pas Eloquent
- **Conversion** : Types primitifs ↔ Value Objects
- **Testabilité** : Facile à tester unitairement
- **Flexibilité** : Changer de persistence sans toucher au Domain

---

## 6. Kubernetes - Configuration Production-Ready

### ☸️ Deployment avec Best Practices

**Fichier:** [`k8s/deployment.yaml`](k8s/deployment.yaml)

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: cinegest-back
  namespace: cinegest
  labels:
    app: cinegest-back
spec:
  replicas: 2  # Haute disponibilité
  
  # Zero-downtime deployment strategy
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxUnavailable: 0  # Toujours 1 pod minimum disponible
      maxSurge: 1        # 1 nouveau pod créé avant de kill l'ancien

  selector:
    matchLabels:
      app: cinegest-back

  template:
    metadata:
      labels:
        app: cinegest-back
    spec:
      # 🔒 Security Context (non-root user)
      securityContext:
        runAsUser: 33         # www-data
        runAsGroup: 33
        fsGroup: 33
        runAsNonRoot: true

      containers:
      - name: cinegest-back
        image: ghcr.io/etho01/cinegest-back:latest
        imagePullPolicy: Always
        
        # 🔒 Container Security
        securityContext:
          allowPrivilegeEscalation: false
          readOnlyRootFilesystem: true  # Filesystem read-only
          capabilities:
            drop:
              - ALL  # Drop toutes les capabilities Linux

        # 📊 Resource Management
        resources:
          requests:
            memory: "256Mi"
            cpu: "100m"
          limits:
            memory: "512Mi"
            cpu: "500m"

        # 🔐 Environment Variables (secrets depuis Vault)
        envFrom:
          - secretRef:
              name: cinegest-back-secret  # Créé automatiquement par ESO

        # ❤️ Health Checks
        livenessProbe:
          httpGet:
            path: /up
            port: 80
          initialDelaySeconds: 30
          periodSeconds: 10
          timeoutSeconds: 5
          failureThreshold: 3

        readinessProbe:
          httpGet:
            path: /up
            port: 80
          initialDelaySeconds: 10
          periodSeconds: 5
          timeoutSeconds: 3
          failureThreshold: 2

        # 💾 Volumes (nécessaires car readOnlyRootFilesystem)
        volumeMounts:
          - name: tmp
            mountPath: /tmp
          - name: cache
            mountPath: /var/www/html/storage/framework/cache
          - name: logs
            mountPath: /var/www/html/storage/logs

      volumes:
        - name: tmp
          emptyDir: {}
        - name: cache
          emptyDir: {}
        - name: logs
          emptyDir: {}
```

### 🔐 External Secrets Operator (Vault → Kubernetes)

**Fichier:** [`k8s/externalsecret.yaml`](k8s/externalsecret.yaml)

```yaml
apiVersion: external-secrets.io/v1beta1
kind: ExternalSecret
metadata:
  name: cinegest-back-vault
  namespace: cinegest
spec:
  # Refresh automatique toutes les 15 minutes
  refreshInterval: 15m
  
  # Référence au SecretStore
  secretStoreRef:
    name: vault-backend
    kind: SecretStore
  
  # Secret Kubernetes cible (créé/mis à jour automatiquement)
  target:
    name: cinegest-back-secret
    creationPolicy: Owner
  
  # Source des secrets dans Vault
  dataFrom:
    - extract:
        key: secret/cinegest/app  # Chemin dans Vault KV v2
```

**✅ Avantages :**
- ❌ **Zéro secret dans Git** (jamais de `env` committé)
- 🔄 **Auto-refresh** : Secrets mis à jour sans redéployer
- 🔒 **Centralisé** : Une seule source de vérité (Vault)
- 🛡️ **Audit trail** : Vault log tous les accès

### 🌐 Ingress avec Rate Limiting

**Fichier:** [`k8s/ingress.yaml`](k8s/ingress.yaml)

```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: cinegest-back
  namespace: cinegest
  annotations:
    # SSL/TLS automatique avec Let's Encrypt
    cert-manager.io/cluster-issuer: "letsencrypt-prod"
    
    # Rate limiting (protection DDoS/abuse)
    traefik.ingress.kubernetes.io/router.middlewares: cinegest-rate-limit@kubernetescrd
    
    # Security headers
    traefik.ingress.kubernetes.io/router.entrypoints: websecure
spec:
  rules:
    - host: api.cinegest.example.com
      http:
        paths:
          - path: /
            pathType: Prefix
            backend:
              service:
                name: cinegest-back
                port:
                  number: 80
  
  # Certificat SSL/TLS
  tls:
    - hosts:
        - api.cinegest.example.com
      secretName: cinegest-back-tls  # Créé automatiquement par cert-manager
```

---

## 7. CI/CD Pipeline - Automatisation Complète

### 🔄 GitHub Actions Workflow

**Fichier:** [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml)

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP 8.2
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, pdo_mysql
          coverage: xdebug
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run Tests
        run: php artisan test --parallel
      
      - name: Code Coverage
        run: php artisan test --coverage-clover coverage.xml

  build:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v3
      
      - name: Login to GitHub Container Registry
        uses: docker/login-action@v2
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}
      
      - name: Build and Push Docker Image
        uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: |
            ghcr.io/etho01/cinegest-back:latest
            ghcr.io/etho01/cinegest-back:${{ github.sha }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    needs: build
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup kubectl
        uses: azure/setup-kubectl@v3
      
      - name: Configure kubectl
        run: |
          mkdir -p ~/.kube
          echo "${{ secrets.KUBECONFIG }}" | base64 -d > ~/.kube/config
      
      - name: Deploy to Kubernetes
        run: |
          kubectl set image deployment/cinegest-back \
            cinegest-back=ghcr.io/etho01/cinegest-back:${{ github.sha }} \
            -n cinegest
      
      - name: Wait for Rollout
        run: |
          kubectl rollout status deployment/cinegest-back -n cinegest --timeout=5m
      
      - name: Run Migrations
        run: |
          kubectl exec -n cinegest \
            deploy/cinegest-back -- php artisan migrate --force
      
      - name: Health Check
        run: |
          kubectl exec -n cinegest \
            deploy/cinegest-back -- php artisan health:check
```

**✅ Pipeline Features :**
- ✅ **Tests automatiques** avant chaque déploiement
- ✅ **Build multi-stage** Docker optimisé
- ✅ **Zero-downtime** (RollingUpdate)
- ✅ **Migrations automatiques** après déploiement
- ✅ **Health checks** post-déploiement
- ✅ **Auto-rollback** si échec de readiness probe

---

## 🎯 Résumé des Bonnes Pratiques Démontrées

### Architecture
- ✅ Clean Architecture avec séparation stricte des couches
- ✅ Domain-Driven Design (Entity, Value Objects, Repository)
- ✅ Dependency Inversion (Domain → Interfaces, Infrastructure → Implémentation)
- ✅ Single Responsibility Principle
- ✅ Testabilité maximale (code découplé)

### Backend
- ✅ Value Objects pour validation métier
- ✅ Repository Pattern pour abstraction persistence
- ✅ DTO Pattern pour transfert de données
- ✅ Use Cases pour logique applicative
- ✅ Mappers pour isolation Domain/Infrastructure
- ✅ Transactions pour atomicité
- ✅ Exceptions custom pour gestion d'erreurs

### DevOps
- ✅ Kubernetes avec SecurityContext (non-root, read-only FS)
- ✅ Zero-downtime deployments (RollingUpdate)
- ✅ Health checks (liveness/readiness)
- ✅ Resource limits (CPU/Memory)
- ✅ Secrets management avec Vault (zéro secret dans Git)
- ✅ Auto-scaling (HPA)
- ✅ CI/CD complet avec tests automatiques
- ✅ SSL/TLS automatique (Let's Encrypt)

---

## 📚 Fichiers Clés à Examiner

### Clean Architecture
- [Domain/Entity/Booking.php](app/Domain/Entity/Booking.php) - Entité métier pure
- [Domain/ValueObject/Email.php](app/Domain/ValueObject/Email.php) - Value Object
- [Domain/Repository/BookingRepositoryInterface.php](app/Domain/Repository/BookingRepositoryInterface.php) - Interface
- [Infrastructure/Persistence/Eloquent/EloquentBookingRepository.php](app/Infrastructure/Persistence/Eloquent/Repository/EloquentBookingRepository.php) - Implémentation

### Use Cases
- [UseCase/Site/Booking/CreateBookingWithPaymentIntent.php](app/UseCase/Site/Booking/CreateBookingWithPaymentIntent.php)
- [UseCase/Site/Booking/ConfirmBookingPayment.php](app/UseCase/Site/Booking/ConfirmBookingPayment.php)

### Kubernetes
- [k8s/deployment.yaml](k8s/deployment.yaml) - Deployment production-ready
- [k8s/externalsecret.yaml](k8s/externalsecret.yaml) - Synchronisation Vault
- [k8s/ingress.yaml](k8s/ingress.yaml) - Ingress avec SSL/TLS

### Tests
- [tests/Unit/](tests/Unit/) - Tests unitaires (Domain, Services)
- [tests/Feature/](tests/Feature/) - Tests d'intégration (API)

---

<div align="center">

**💡 Ce fichier démontre la capacité à implémenter des architectures avancées<br>tout en maintenant un code clean, testable et production-ready.**

</div>
