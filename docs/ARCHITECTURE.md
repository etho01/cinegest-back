# Architecture CI/CD et Infrastructure

## 🏗️ Vue d'ensemble de l'infrastructure

```
┌─────────────────────────────────────────────────────────────────────┐
│                         GitHub Repository                            │
│  ┌────────────┐  ┌────────────┐  ┌────────────────────────────┐   │
│  │ Source Code│  │   Tests    │  │  Kubernetes Manifests      │   │
│  └────────────┘  └────────────┘  └────────────────────────────┘   │
└──────────────────────────┬──────────────────────────────────────────┘
                           │
                           │ Push sur main
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      GitHub Actions CI/CD                            │
│                                                                       │
│  ┌──────────┐    ┌──────────┐    ┌──────────────────────────┐      │
│  │  Tests   │───▶│  Build   │───▶│       Deploy             │      │
│  │ PHPUnit  │    │  Docker  │    │  - Install ESO           │      │
│  └──────────┘    │  Image   │    │  - Deploy Vault manifests│      │
│                  └──────────┘    │  - Sync secrets          │      │
│                       │          │  - Apply K8s manifests   │      │
│                       │          │  - Run migrations        │      │
│                       ▼          └──────────────────────────┘      │
│                GHCR Registry                    │                   │
│          ghcr.io/etho01/cinegest-back          │                   │
│                                                 │                   │
└─────────────────────────────────────────────────┼───────────────────┘
                                                  │
                                                  │ kubectl apply
                                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Kubernetes Cluster (K3s)                          │
│                                                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                    Namespace: vault                           │  │
│  │                                                               │  │
│  │  ┌──────────────────┐         ┌──────────────────┐          │  │
│  │  │  Vault Pod       │◀───────▶│  PVC (5Gi)       │          │  │
│  │  │  (KV v2 Engine)  │         │  Data Storage    │          │  │
│  │  └────────┬─────────┘         └──────────────────┘          │  │
│  │           │                                                   │  │
│  │           │ service/vault:8200                               │  │
│  └───────────┼───────────────────────────────────────────────────┘  │
│              │                                                       │
│              │ Auth via ServiceAccount                              │
│              │ Role: cinegest-app                                   │
│              ▼                                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │            Namespace: external-secrets                        │  │
│  │                                                               │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  External Secrets Operator (ESO)                       │  │  │
│  │  │  - Sync toutes les 15 min                              │  │  │
│  │  │  - Watch Vault pour changements                        │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  └─────────────────────┼─────────────────────────────────────────┘  │
│                        │                                             │
│                        │ Crée/MAJ Secret K8s                        │
│                        ▼                                             │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                    Namespace: cinegest                        │  │
│  │                                                               │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  SecretStore (vault-backend)                           │  │  │
│  │  │  - Connexion à Vault                                   │  │  │
│  │  │  - Auth Kubernetes                                     │  │  │
│  │  └────────────────────────────────────────────────────────┘  │  │
│  │                                                               │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  ExternalSecret (cinegest-back-vault)                  │  │  │
│  │  │  - Source: secret/cinegest/app                         │  │  │
│  │  │  - Target: cinegest-back-secret                        │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  │                     │                                          │  │
│  │                     ▼                                          │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Secret: cinegest-back-secret                          │  │  │
│  │  │  - APP_KEY, DB_PASSWORD, etc.                          │  │  │
│  │  │  - Créé/MAJ automatiquement par ESO                    │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  │                     │                                          │  │
│  │                     │ envFrom                                  │  │
│  │                     ▼                                          │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Deployment: cinegest-back                             │  │  │
│  │  │  - Replicas: 2                                         │  │  │
│  │  │  - Strategy: RollingUpdate                             │  │  │
│  │  │  - SecurityContext: runAsUser 33 (www-data)            │  │  │
│  │  │                                                         │  │  │
│  │  │  ┌─────────────┐  ┌─────────────┐                     │  │  │
│  │  │  │   Pod 1     │  │   Pod 2     │                     │  │  │
│  │  │  │  Laravel    │  │  Laravel    │                     │  │  │
│  │  │  │  Apache     │  │  Apache     │                     │  │  │
│  │  │  └──────┬──────┘  └──────┬──────┘                     │  │  │
│  │  └─────────┼────────────────┼────────────────────────────┘  │  │
│  │            │                │                                │  │
│  │            └────────┬───────┘                                │  │
│  │                     │                                          │  │
│  │                     ▼                                          │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Service: cinegest-back                                │  │  │
│  │  │  - Type: ClusterIP                                     │  │  │
│  │  │  - Port: 80                                            │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  │                     │                                          │  │
│  │                     ▼                                          │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Middleware: ratelimit + security-headers              │  │  │
│  │  │  - 100 req/min avg, burst 50                           │  │  │
│  │  │  - X-Frame-Options, CSP, etc.                          │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  │                     │                                          │  │
│  │                     ▼                                          │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Ingress: cinegest-back                                │  │  │
│  │  │  - Host: api.cinegest.nicolasbarbey.fr                 │  │  │
│  │  │  - TLS: Let's Encrypt (cert-manager)                   │  │  │
│  │  │  - IngressClass: traefik                               │  │  │
│  │  └──────────────────┬─────────────────────────────────────┘  │  │
│  └─────────────────────┼─────────────────────────────────────────┘  │
│                        │                                             │
└────────────────────────┼─────────────────────────────────────────────┘
                         │
                         │ HTTPS
                         ▼
                  ┌──────────────┐
                  │   Internet   │
                  │   Users      │
                  └──────────────┘
```

## 🔄 Flux de déploiement complet

### 1. Développement
```bash
# Développeur push code
git add .
git commit -m "feat: nouvelle fonctionnalité"
git push origin main
```

### 2. CI/CD Pipeline (GitHub Actions)

```
┌──────────────┐
│  Job: Test   │  ← PHPUnit tests (composer install + php artisan test)
└──────┬───────┘
       │ ✅ Tests passed
       ▼
┌──────────────────┐
│ Job: Build+Push  │  ← Docker build multi-stage
└──────┬───────────┘  ← Push to GHCR (tags: SHA + latest)
       │
       │ Image: ghcr.io/etho01/cinegest-back:abc123def
       ▼
┌──────────────────────────────┐
│      Job: Deploy             │
├──────────────────────────────┤
│ 1. Setup kubectl             │
│ 2. Setup Helm                │
│ 3. Check/Install ESO         │ ← Si pas déjà installé
│ 4. Deploy Vault manifests    │ ← ServiceAccount, SecretStore, ExternalSecret
│ 5. Wait secrets sync         │ ← Jusqu'à 2 min
│ 6. Apply K8s manifests       │ ← Deployment, Service, Ingress, Middleware
│ 7. Update image              │ ← kubectl set image
│ 8. Run migrations            │ ← Job temporaire avec même image
│ 9. Wait rollout              │ ← Jusqu'à 5 min
└──────┬───────────────────────┘
       │
       ├───✅ Success
       │   └──▶ Deployment complete
       │
       └───❌ Failure
           └──▶ Automatic rollback to previous image
```

## 🔐 Flux de gestion des secrets

```
┌──────────────────────────┐
│  1. Admin met à jour     │
│     secret dans Vault    │
└────────┬─────────────────┘
         │
         │ vault kv patch secret/cinegest/app DB_PASSWORD="new"
         ▼
┌────────────────────────────────────┐
│  Vault                             │
│  secret/cinegest/app               │
│  {                                 │
│    APP_KEY: "...",                 │
│    DB_PASSWORD: "new",  ← Updated  │
│    ...                             │
│  }                                 │
└────────┬───────────────────────────┘
         │
         │ Toutes les 15 min (ou force-sync)
         ▼
┌────────────────────────────────────┐
│  External Secrets Operator         │
│  - Poll Vault                      │
│  - Detect change                   │
│  - Update K8s Secret               │
└────────┬───────────────────────────┘
         │
         ▼
┌────────────────────────────────────┐
│  K8s Secret                        │
│  cinegest-back-secret              │
│  {                               │
│    DB_PASSWORD: "new"  ← Updated   │
│  }                                 │
└────────┬───────────────────────────┘
         │
         │ Note: Pods ne rechargent PAS automatiquement
         ▼
┌────────────────────────────────────┐
│  2. Admin redémarre pods           │
│     pour charger nouveaux secrets  │
└────────┬───────────────────────────┘
         │
         │ kubectl -n cinegest rollout restart deployment/cinegest-back
         ▼
┌────────────────────────────────────┐
│  Pods redémarrés avec nouveaux     │
│  secrets chargés                   │
└────────────────────────────────────┘
```

## 📊 Monitoring et Health Checks

```
┌─────────────────────────────────────┐
│  Kubelet (sur chaque node)          │
│                                     │
│  Toutes les 10s:                    │
│  ┌────────────────────────────────┐ │
│  │  Readiness Probe               │ │
│  │  GET /health                   │ │
│  │  - initialDelay: 10s           │ │
│  │  - timeout: 5s                 │ │
│  ├────────────────────────────────┤ │
│  │  ✅ → Pod added to Service    │ │
│  │  ❌ → Pod removed from Service│ │
│  └────────────────────────────────┘ │
│                                     │
│  Toutes les 20s:                    │
│  ┌────────────────────────────────┐ │
│  │  Liveness Probe                │ │
│  │  GET /health                   │ │
│  │  - initialDelay: 30s           │ │
│  │  - timeout: 5s                 │ │
│  ├────────────────────────────────┤ │
│  │  ✅ → Pod is healthy           │ │
│  │  ❌ → Pod is restarted         │ │
│  └────────────────────────────────┘ │
└─────────────────────────────────────┘
```

## 🔄 Stratégie RollingUpdate (Zero Downtime)

```
État initial: 2 pods running
┌──────┐  ┌──────┐
│ v1.0 │  │ v1.0 │
└──────┘  └──────┘

Déploiement v1.1:
maxUnavailable: 0  ← Minimum 2 pods always running
maxSurge: 1        ← Maximum 3 pods total

Step 1: Créer nouveau pod
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.0 │  │ v1.1 │ ← Création
└──────┘  └──────┘  └──────┘

Step 2: Attendre readiness
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.0 │  │ v1.1 │ ← Readiness probe OK
└──────┘  └──────┘  └──────┘ ✅

Step 3: Terminer ancien pod
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.0 │  │ v1.1 │
└──┬───┘  └──────┘  └──────┘
   └─ Terminated

Step 4: Créer deuxième pod v1.1
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.1 │  │ v1.1 │ ← Création
└──────┘  └──────┘  └──────┘

Step 5: Attendre readiness
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.1 │  │ v1.1 │ ← Readiness probe OK
└──────┘  └──────┘  └──────┘ ✅

Step 6: Terminer dernier pod v1.0
┌──────┐  ┌──────┐  ┌──────┐
│ v1.0 │  │ v1.1 │  │ v1.1 │
└──┬───┘  └──────┘  └──────┘
   └─ Terminated

État final: 2 pods v1.1 running
           ┌──────┐  ┌──────┐
           │ v1.1 │  │ v1.1 │
           └──────┘  └──────┘

✅ Aucun downtime pendant la transition
```

## 🛡️ Sécurité - Layers

```
┌─────────────────────────────────────────────────────┐
│ Internet                                            │
└──────────────────────┬──────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 1: Traefik Rate Limiting                       │
│ - 100 req/min moyenne                                │
│ - Burst de 50 requêtes                               │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 2: Security Headers                            │
│ - X-Frame-Options: DENY                              │
│ - X-Content-Type-Options: nosniff                    │
│ - Content Security Policy                            │
│ - Referrer-Policy                                    │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 3: TLS/SSL (cert-manager)                      │
│ - Let's Encrypt certificates                         │
│ - Auto-renewal                                       │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 4: Kubernetes Network Policies (optionnel)     │
│ - Isolation namespace                                │
│ - Whitelist IPs                                      │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 5: Pod SecurityContext                         │
│ - runAsNonRoot: true                                 │
│ - runAsUser: 33 (www-data)                           │
│ - allowPrivilegeEscalation: false                    │
│ - capabilities: drop ALL                             │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│ Layer 6: Laravel Application Security                │
│ - Sanctum authentication                             │
│ - CORS configuration                                 │
│ - Input validation                                   │
│ - CSRF protection                                    │
└──────────────────────────────────────────────────────┘
```

## 📈 Scalabilité (Future)

```
┌────────────────────────────────────────────────────┐
│  HorizontalPodAutoscaler (HPA)                     │
│                                                    │
│  ┌──────────────────────────────────────────────┐ │
│  │  Metrics:                                    │ │
│  │  - CPU > 70% → Scale up                      │ │
│  │  - CPU < 30% → Scale down                    │ │
│  │  - Memory > 80% → Scale up                   │ │
│  │                                              │ │
│  │  Limits:                                     │ │
│  │  - minReplicas: 2                            │ │
│  │  - maxReplicas: 10                           │ │
│  └──────────────────────────────────────────────┘ │
│                                                    │
│  ┌──────────────────────────────────────────────┐ │
│  │  Current: 2 pods                             │ │
│  │  CPU: 45%                                    │ │
│  │  Memory: 60%                                 │ │
│  └──────────────────────────────────────────────┘ │
│                                                    │
│              High traffic detected                │
│                      ↓                            │
│  ┌──────────────────────────────────────────────┐ │
│  │  Scaled to: 5 pods                           │ │
│  │  CPU: 55%                                    │ │
│  │  Memory: 50%                                 │ │
│  └──────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────┘
```

---

Cette architecture assure:
- ✅ Haute disponibilité (multiple replicas)
- ✅ Zero downtime deployments
- ✅ Sécurité en profondeur (multi-layers)
- ✅ Gestion sécurisée des secrets
- ✅ Monitoring et health checks
- ✅ Rollback automatique
- ✅ Scalabilité horizontale
- ✅ Infrastructure as Code
