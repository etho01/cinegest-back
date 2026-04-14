# Configuration Pipeline CI/CD avec Vault

## 📋 Vue d'ensemble

La pipeline GitHub Actions déploie automatiquement votre application avec intégration Vault :

1. **Tests** → Exécute PHPUnit
2. **Build** → Build et push de l'image Docker
3. **Deploy** → 
   - Installe External Secrets Operator (si nécessaire)
   - Déploie les manifestes Vault
   - Synchronise les secrets depuis Vault
   - Déploie l'application
   - Exécute les migrations
   - Rollback automatique en cas d'erreur

## 🔐 Secrets GitHub requis

Configurez ces secrets dans GitHub : **Settings → Secrets and variables → Actions**

### Obligatoires

| Secret | Description | Exemple |
|--------|-------------|---------|
| `KUBECONFIG_B64` | Kubeconfig encodé en base64 | `cat ~/.kube/config \| base64 -w0` |

### Optionnels (pour automatisation Vault)

| Secret | Description |
|--------|-------------|
| `VAULT_ADDR` | URL de Vault (si différent du défaut) |
| `VAULT_TOKEN` | Token Vault pour updates automatiques |

## 🚀 Première installation

### Étape 1: Configuration initiale de Vault

**À faire manuellement une seule fois** (pas dans la pipeline) :

```bash
# 1. Se connecter au cluster
export KUBECONFIG=/path/to/your/kubeconfig

# 2. Installer Vault et External Secrets Operator
./k8s/vault/install.sh

# 3. Initialiser Vault
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator init -key-shares=1 -key-threshold=1

# ⚠️ SAUVEGARDER les clés affichées dans un endroit sûr !

# 4. Unseal Vault
vault operator unseal <UNSEAL_KEY>
exit

# 5. Configurer Vault
./k8s/vault/configure-vault.sh <ROOT_TOKEN>

# 6. Mettre à jour les secrets avec les vraies valeurs
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Générer APP_KEY
APP_KEY=$(php artisan key:generate --show)

# Insérer les vrais secrets
vault kv patch secret/cinegest/app \
  APP_KEY="$APP_KEY" \
  DB_PASSWORD="votre_password_mysql" \
  DB_HOST="votre_host_mysql" \
  MAIL_USERNAME="votre_mailjet_user" \
  MAIL_PASSWORD="votre_mailjet_pass" \
  MAILJET_APIKEY="votre_api_key" \
  MAILJET_APISECRET="votre_api_secret" \
  STRIPE_KEY="pk_live_..." \
  STRIPE_SECRET="sk_live_..." \
  STRIPE_WEBHOOK_SECRET="whsec_..."
```

### Étape 2: Configurer GitHub Secrets

```bash
# Créer le secret KUBECONFIG_B64
cat ~/.kube/config | base64 -w0
# Copier le résultat et le mettre dans GitHub Secrets
```

### Étape 3: Premier déploiement

```bash
# Pusher sur la branche main pour déclencher la pipeline
git add .
git commit -m "feat: integrate Vault in CI/CD"
git push origin main
```

La pipeline va :
- ✅ Exécuter les tests
- ✅ Builder l'image Docker
- ✅ Installer ESO si nécessaire
- ✅ Déployer les manifestes Vault
- ✅ Vérifier la synchronisation des secrets
- ✅ Déployer l'application
- ✅ Exécuter les migrations avec rollback automatique

## 🔄 Workflow après configuration

Une fois Vault configuré, chaque push sur `main` :

```
┌─────────────┐
│  git push   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Job: Test                          │
│  - Checkout code                    │
│  - Install dependencies             │
│  - Run PHPUnit tests                │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Job: Build and Push                │
│  - Build Docker image               │
│  - Tag with SHA + latest            │
│  - Push to ghcr.io                  │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Job: Deploy                        │
│  - Setup kubectl                    │
│  - Install/Check ESO                │
│  - Deploy Vault manifests           │
│  - Wait secrets sync (Vault → K8s)  │
│  - Deploy app manifests             │
│  - Update image                     │
│  - Run migrations                   │
│  - Wait rollout                     │
│  - Rollback if failure              │
└─────────────────────────────────────┘
```

## 🔧 Gestion des secrets après déploiement

### Mettre à jour un secret

```bash
# 1. Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# 2. Mettre à jour le secret
vault kv patch secret/cinegest/app \
  DB_PASSWORD="nouveau_password"

# 3. Forcer la synchronisation (optionnel, sinon auto dans 15 min)
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite

# 4. Redémarrer l'app pour charger les nouveaux secrets
kubectl -n cinegest rollout restart deployment/cinegest-back
```

### Ou via la pipeline

Pour automatiser la mise à jour de secrets via la pipeline, ajoutez un workflow séparé :

```yaml
# .github/workflows/update-secrets.yml
name: Update Vault Secrets

on:
  workflow_dispatch:
    inputs:
      secret_key:
        description: 'Secret key to update'
        required: true
      secret_value:
        description: 'New secret value'
        required: true

jobs:
  update:
    runs-on: ubuntu-latest
    steps:
      - name: Update secret in Vault
        run: |
          kubectl port-forward -n vault svc/vault 8200:8200 &
          sleep 2
          
          export VAULT_ADDR=http://localhost:8200
          export VAULT_TOKEN=${{ secrets.VAULT_TOKEN }}
          
          vault kv patch secret/cinegest/app \
            ${{ github.event.inputs.secret_key }}="${{ github.event.inputs.secret_value }}"
```

## 📊 Monitoring

### Vérifier le statut de la synchronisation

```bash
# Status de l'ExternalSecret
kubectl -n cinegest get externalsecret cinegest-back-vault

# Détails
kubectl -n cinegest describe externalsecret cinegest-back-vault

# Vérifier le secret K8s
kubectl -n cinegest get secret cinegest-back-secret
```

### Logs

```bash
# Logs External Secrets Operator
kubectl -n external-secrets logs -f deploy/external-secrets

# Logs de l'application
kubectl -n cinegest logs -f deployment/cinegest-back

# Logs de la dernière migration
kubectl -n cinegest logs job/cinegest-back-migrate
```

## 🆘 Troubleshooting

### Pipeline échoue : "Timeout waiting for Vault secrets"

**Cause** : Vault n'est pas configuré ou sealed

**Solution** :
```bash
# Vérifier Vault
kubectl -n vault exec deploy/vault -- vault status

# Si sealed
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator unseal <UNSEAL_KEY>
```

### Pipeline échoue : "ExternalSecret not ready"

**Cause** : Problème d'authentification Vault

**Solution** :
```bash
# Re-configurer l'authentification
./k8s/vault/configure-vault.sh <ROOT_TOKEN>

# Re-déclencher la pipeline
git commit --allow-empty -m "trigger deploy"
git push
```

### Fallback sans Vault

Si Vault n'est pas configuré, vous pouvez utiliser un secret manuel :

```bash
# Créer le secret manuellement
kubectl -n cinegest create secret generic cinegest-back-secret \
  --from-env-file=.env.production

# La pipeline détectera le secret existant et continuera
```

## 🔒 Sécurité

### Bonnes pratiques

- ✅ Ne jamais commiter de secrets dans Git
- ✅ Utiliser Vault pour tous les secrets sensibles
- ✅ Rotation régulière des secrets (DB, API keys, etc.)
- ✅ Audit logging activé dans Vault
- ✅ Limiter les accès aux tokens Vault
- ✅ Utiliser Auto-unseal en production

### Secrets à ne JAMAIS mettre dans GitHub Secrets

- ❌ Secrets applicatifs (DB password, API keys, etc.)
  → Ces secrets vont dans Vault

### Secrets OK dans GitHub Secrets

- ✅ KUBECONFIG_B64 (accès au cluster)
- ✅ VAULT_TOKEN (uniquement si automatisation)

## 📚 Documentation

- [Guide rapide Vault](VAULT-QUICKSTART.md)
- [Documentation complète Vault](vault/README.md)
- [Configuration Kubernetes](deployment.yaml)

## 🎯 Checklist de déploiement

- [ ] Vault installé et initialisé
- [ ] Secrets configurés dans Vault
- [ ] External Secrets Operator installé
- [ ] KUBECONFIG_B64 défini dans GitHub Secrets
- [ ] Pipeline exécutée avec succès
- [ ] Application accessible via l'Ingress
- [ ] Health checks OK
- [ ] Logs vérifiés
