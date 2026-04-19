# 🔐 Guide de Sécurité et Bonnes Pratiques

Guide complet des bonnes pratiques de sécurité pour le déploiement et la maintenance de CineGest Backend.

---

## 📋 Table des matières

1. [Principes de sécurité](#principes-de-sécurité)
2. [Gestion des secrets](#gestion-des-secrets)
3. [Configuration Kubernetes](#configuration-kubernetes)
4. [Réseau et accès](#réseau-et-accès)
5. [Monitoring et alertes](#monitoring-et-alertes)
6. [Procédures d'urgence](#procédures-durgence)

---

## 🛡️ Principes de sécurité

### Defense in Depth (Défense en profondeur)

L'application utilise plusieurs couches de sécurité :

```
┌─────────────────────────────────────┐
│  1. Traefik Ingress (TLS, Rate Limit) │
├─────────────────────────────────────┤
│  2. Middleware (Headers, CORS)       │
├─────────────────────────────────────┤
│  3. Service Kubernetes (ClusterIP)   │
├─────────────────────────────────────┤
│  4. Pod Security (non-root, readonly) │
├─────────────────────────────────────┤
│  5. Laravel Sanctum (Auth)           │
├─────────────────────────────────────┤
│  6. External Secrets (Vault)         │
└─────────────────────────────────────┘
```

### Principe du moindre privilège

- ✅ Pods exécutés en **non-root** (UID 33)
- ✅ Capabilities supprimées (`drop: ALL`)
- ✅ Filesystem **read-only** (sauf volumes nécessaires)
- ✅ Service accounts avec **permissions minimales**

---

## 🔑 Gestion des secrets

### Classification des secrets

| Type | Exemples | Rotation | Stockage |
|------|----------|----------|----------|
| **Critiques** | DB_PASSWORD, STRIPE_SECRET, ROOT_TOKEN | 90 jours | Vault + Gestionnaire MdP |
| **Sensibles** | APP_KEY, MAILJET_APISECRET | 180 jours | Vault |
| **Standards** | API Keys externes | 1 an | Vault |
| **Publics** | STRIPE_KEY (pk_), APP_URL | Jamais | Config/Vault |

### Secrets Vault

#### ⚠️ UNSEAL_KEY

**Criticité:** 🔴 CRITIQUE

Le plus important ! Permet de desceller Vault après redémarrage.

**Bonnes pratiques:**
```bash
# ✅ DO: Sauvegarder dans un gestionnaire de mots de passe
# Exemples: 1Password, Bitwarden, LastPass
# Partager uniquement avec les administrateurs système

# ❌ DON'T:
# - Ne jamais stocker en clair sur le serveur
# - Ne jamais envoyer par email/Slack
# - Ne jamais commiter dans Git
# - Ne jamais afficher dans les logs
```

**Récupération en cas de perte:**
> ⚠️ **IMPOSSIBLE** - Si perdu, Vault reste sealed définitivement.
> Nécessite réinstallation complète avec perte des données.

#### 🔐 ROOT_TOKEN

**Criticité:** 🔴 CRITIQUE

Accès administrateur complet à Vault.

**Bonnes pratiques:**
```bash
# ✅ DO:
# - Utiliser uniquement pour configuration initiale
# - Révoquer après création de tokens utilisateurs
# - Rotation tous les 90 jours minimum

# ❌ DON'T:
# - Ne jamais utiliser en production courante
# - Ne jamais hardcoder dans scripts
```

**Rotation du ROOT_TOKEN:**
```bash
# 1. Générer nouveau root token
vault operator generate-root

# 2. Mettre à jour dans le gestionnaire de MdP

# 3. Révoquer l'ancien
vault token revoke <OLD_ROOT_TOKEN>
```

#### 🗝️ Secrets applicatifs

**APP_KEY (Laravel):**
```bash
# Génération sécurisée
php artisan key:generate --show

# Format attendu
base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# ⚠️ Ne jamais partager ou exposer
# La rotation nécessite re-signature des sessions/cookies
```

**DB_PASSWORD:**
```bash
# ✅ Exigences:
# - Minimum 16 caractères
# - Lettres majuscules, minuscules, chiffres, symboles
# - Unique par environnement

# Génération sécurisée
openssl rand -base64 32

# Rotation
# 1. Créer nouveau user MySQL
# 2. Tester connexion
# 3. Mettre à jour Vault
# 4. Restart pods
# 5. Supprimer ancien user
```

**STRIPE_SECRET:**
```bash
# Format: sk_live_XXXX (production) ou sk_test_XXXX (test)

# ⚠️ JAMAIS exposer côté client
# ⚠️ Rotation depuis dashboard Stripe
# ⚠️ Utiliser sk_test_ en dev/staging
```

---

## 🔐 Configuration Kubernetes

### Security Context (Pod)

**Configuration actuelle ([k8s/deployment.yaml](../k8s/deployment.yaml)):**
```yaml
securityContext:
  runAsNonRoot: true
  runAsUser: 33          # www-data
  fsGroup: 33
  allowPrivilegeEscalation: false
  capabilities:
    drop:
      - ALL
```

**Pourquoi ces paramètres:**

| Paramètre | Raison | Impact sécurité |
|-----------|--------|-----------------|
| `runAsNonRoot: true` | Empêche root | 🔴 Critique |
| `runAsUser: 33` | UID www-data | 🟡 Important |
| `allowPrivilegeEscalation: false` | Bloque escalade | 🔴 Critique |
| `drop: ALL` | Supprime capabilities | 🔴 Critique |

### Network Policies

**Recommandé (à implémenter):**
```yaml
apiVersion: networking.k8s.io/v1
kind: NetworkPolicy
metadata:
  name: cinegest-back-netpol
  namespace: cinegest
spec:
  podSelector:
    matchLabels:
      app: cinegest-back
  policyTypes:
    - Ingress
    - Egress
  ingress:
    - from:
        - namespaceSelector:
            matchLabels:
              name: traefik
      ports:
        - protocol: TCP
          port: 80
  egress:
    - to:
        - namespaceSelector: {}
      ports:
        - protocol: TCP
          port: 3306  # MySQL
        - protocol: TCP
          port: 443   # HTTPS externe (APIs)
        - protocol: TCP
          port: 587   # SMTP
```

### Resource Limits

**Configuration actuelle:**
```yaml
resources:
  requests:
    cpu: "100m"      # Minimum garanti
    memory: "128Mi"
  limits:
    cpu: "500m"      # Maximum autorisé
    memory: "512Mi"
```

**Bonnes pratiques:**
- ✅ Toujours définir requests ET limits
- ✅ Monitorer l'utilisation réelle
- ✅ Ajuster selon la charge
- ⚠️ OOMKilled = limits trop basses

---

## 🌐 Réseau et accès

### TLS/HTTPS

**Configuration ([k8s/ingress.yaml](../k8s/ingress.yaml)):**
```yaml
tls:
  - hosts:
      - api.cinegest.nicolasbarbey.fr
    secretName: cinegest-back-tls
```

**Vérifications:**
```bash
# Tester TLS
curl -I https://api.cinegest.nicolasbarbey.fr

# Vérifier certificat
openssl s_client -connect api.cinegest.nicolasbarbey.fr:443 -servername api.cinegest.nicolasbarbey.fr

# Grade SSL (via SSL Labs)
# https://www.ssllabs.com/ssltest/
```

**Auto-renouvellement (Cert-Manager):**
```bash
# Vérifier les certificats
kubectl -n cinegest get certificate

# Forcer renouvellement
kubectl -n cinegest delete secret cinegest-back-tls
```

### Rate Limiting

**Configuration ([k8s/middleware.yaml](../k8s/middleware.yaml)):**
```yaml
rateLimit:
  average: 100      # 100 req/s par IP
  period: 1s
  burst: 200        # Tolerance burst
```

**Ajustements selon besoin:**
```yaml
# API publique (restrictif)
average: 50
period: 1s

# API authentifiée (permissif)
average: 200
period: 1s
```

### CORS

**Configuration Laravel ([config/cors.php](../config/cors.php)):**
```php
'allowed_origins' => [
    env('APP_FRONTEND_URL', 'https://cinegest.nicolasbarbey.fr'),
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 3600,
'supports_credentials' => true,
```

**Sécurité CORS:**
- ✅ Whitelister uniquement les domaines nécessaires
- ❌ Jamais `allowed_origins' => ['*']` en production
- ✅ `supports_credentials: true` si cookies/auth

---

## 📊 Monitoring et alertes

### Health Checks

**Liveness Probe:**
```yaml
livenessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 30
  periodSeconds: 20
```

**Readiness Probe:**
```yaml
readinessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 10
```

**Endpoint health ([routes/web.php](../routes/web.php)):**
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

### Logs

**Accès aux logs:**
```bash
# Logs application
kubectl -n cinegest logs -f deploy/cinegest-back --tail=100

# Logs erreurs uniquement
kubectl -n cinegest logs deploy/cinegest-back | grep ERROR

# Logs depuis X minutes
kubectl -n cinegest logs --since=10m deploy/cinegest-back
```

**⚠️ Ne JAMAIS logger:**
- Mots de passe
- Tokens d'authentification
- Clés API complètes
- Données personnelles sensibles (RGPD)

**✅ Logger:**
- Actions utilisateurs (audit trail)
- Erreurs avec context
- Tentatives d'accès non autorisées
- Métriques de performance

### Métriques recommandées

```bash
# CPU/Memory
kubectl top pods -n cinegest

# Requêtes HTTP (via Traefik metrics)
# - Nombre total
# - Temps de réponse (P50, P95, P99)
# - Codes erreur (4xx, 5xx)

# Base de données
# - Connexions actives
# - Slow queries
# - Errors
```

---

## 🚨 Procédures d'urgence

### Incident de sécurité

**1. Détection:**
```bash
# Vérifier logs suspects
kubectl -n cinegest logs deploy/cinegest-back | grep -i "unauthorized\|forbidden\|attack"

# Vérifier connexions DB inhabituelles
# (via monitoring MySQL)
```

**2. Isolation immédiate:**
```bash
# Mettre l'app en maintenance
kubectl -n cinegest scale deployment/cinegest-back --replicas=0

# Ou bloquer Ingress
kubectl -n cinegest delete ingress cinegest-back-ingress
```

**3. Investigation:**
```bash
# Dump logs
kubectl -n cinegest logs deploy/cinegest-back --all-containers > incident-$(date +%Y%m%d).log

# Check events
kubectl -n cinegest get events --sort-by='.lastTimestamp'

# Vérifier secrets
kubectl -n cinegest get secret cinegest-back-secret -o yaml
```

**4. Remediation:**
```bash
# Rotation secrets compromis
./update-vault-secrets.sh <ROOT_TOKEN>

# Force resync
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync="$(date +%s)" --overwrite

# Redéployer
kubectl -n cinegest rollout restart deployment/cinegest-back
```

### Fuite de secret

**Si APP_KEY exposée:**
```bash
# 1. Générer nouvelle clé
NEW_KEY=$(php artisan key:generate --show)

# 2. Mettre à jour dans Vault
./update-vault-secrets.sh <ROOT_TOKEN>
# > APP_KEY: $NEW_KEY

# 3. Rolling restart
kubectl -n cinegest rollout restart deployment/cinegest-back

# ⚠️ Conséquence: Toutes les sessions invalidées
```

**Si DB_PASSWORD exposé:**
```bash
# 1. Se connecter à MySQL
mysql -h <DB_HOST> -u root -p

# 2. Créer nouveau user
CREATE USER 'cinegest_new'@'%' IDENTIFIED BY 'NEW_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON cinegest.* TO 'cinegest_new'@'%';

# 3. Mettre à jour Vault
./update-vault-secrets.sh <ROOT_TOKEN>
# > DB_USERNAME: cinegest_new
# > DB_PASSWORD: NEW_STRONG_PASSWORD

# 4. Tester connexion
kubectl -n cinegest exec deploy/cinegest-back -- php artisan migrate:status

# 5. Si OK, supprimer ancien user
DROP USER 'cinegest_old'@'%';
```

**Si STRIPE_SECRET exposé:**
```bash
# 1. Se connecter au Dashboard Stripe
# 2. Révoquer la clé compromise
# 3. Générer nouvelle clé
# 4. Mettre à jour Vault
./update-vault-secrets.sh <ROOT_TOKEN>

# 5. Redémarrer app
kubectl -n cinegest rollout restart deployment/cinegest-back

# 6. Vérifier webhooks Stripe
# Dashboard > Webhooks > Test signature
```

### Vault sealed après incident

```bash
# 1. Vérifier le status
kubectl -n vault exec deploy/vault -- vault status

# 2. Unseal (nécessite UNSEAL_KEY)
kubectl -n vault exec deploy/vault -- vault operator unseal <UNSEAL_KEY>

# 3. Vérifier secrets
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>
vault kv get secret/cinegest/app

# 4. Forcer resync K8s
kubectl -n cinegest delete pod -l app=cinegest-back
```

---

## ✅ Checklist de sécurité

### Installation initiale

- [ ] UNSEAL_KEY sauvegardé dans gestionnaire MdP
- [ ] ROOT_TOKEN sauvegardé dans gestionnaire MdP
- [ ] Fichiers temporaires supprimés (`vault-keys.json`, etc.)
- [ ] Secrets forts générés (DB, Stripe, etc.)
- [ ] TLS actif et certificat valide
- [ ] Rate limiting configuré
- [ ] CORS restrictif (pas de `*`)
- [ ] Pods en non-root
- [ ] Resource limits définis

### Maintenance régulière

- [ ] Rotation secrets sensibles (90 jours)
- [ ] Mise à jour dépendances (composer, npm)
- [ ] Review logs erreurs
- [ ] Vérification certificats TLS
- [ ] Backup Vault (si données critiques)
- [ ] Test disaster recovery

### Avant chaque release

- [ ] `composer audit` (vulnérabilités)
- [ ] Tests de sécurité passés
- [ ] Secrets de prod non exposés
- [ ] Variables d'environnement validées
- [ ] Changelog de sécurité documenté

---

## 📚 Ressources

### Outils de sécurité

```bash
# Audit dépendances PHP
composer audit

# Scan image Docker
trivy image ghcr.io/etho01/cinegest-back:latest

# Test penetration (staging uniquement!)
# OWASP ZAP, Burp Suite

# Scan secrets dans Git
git-secrets --scan
```

### Documentation

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Kubernetes Security Best Practices](https://kubernetes.io/docs/concepts/security/)
- [Laravel Security](https://laravel.com/docs/security)
- [Vault Security Model](https://www.vaultproject.io/docs/internals/security)

### Guides internes

- [VAULT-TROUBLESHOOTING.md](VAULT-TROUBLESHOOTING.md) - Dépannage Vault
- [SCRIPTS-GUIDE.md](SCRIPTS-GUIDE.md) - Guide des scripts
- [VAULT-SETUP.md](VAULT-SETUP.md) - Installation Vault

---

**Dernière mise à jour:** Avril 2026  
**Responsible:** DevOps Team  
**Classification:** 🔴 CONFIDENTIEL
