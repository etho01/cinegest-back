# Configuration Vault + External Secrets Operator pour CineGest

Ce guide explique comment configurer Vault avec External Secrets Operator (ESO) pour gérer les secrets de votre application Laravel dans Kubernetes.

## Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌──────────────────┐
│   Vault      │────▶│  External       │────▶│  K8s Secret      │
│  (KV Store)  │     │  Secrets        │     │  (cinegest-back) │
└──────────────┘     └─────────────────┘     └──────────────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │  Deployment      │
                     │  (cinegest-back) │
                     └─────────────────┘
```

## Prérequis

- Cluster Kubernetes (K3s)
- Helm 3 installé
- kubectl configuré
- vault CLI installé localement

## Installation pas à pas

### 1. Installer External Secrets Operator

```bash
cd /home/nicolas/cinegest/cinegest-back

# Rendre les scripts exécutables
chmod +x k8s/vault/*.sh

# Installer ESO et déployer Vault
./k8s/vault/install.sh
```

### 2. Initialiser Vault

```bash
# Se connecter au pod Vault
kubectl -n vault exec -it deploy/vault -- sh

# Dans le pod Vault
export VAULT_ADDR=http://localhost:8200

# Initialiser (ATTENTION: sauvegarder les clés !)
vault operator init -key-shares=1 -key-threshold=1

# Vous obtiendrez :
# Unseal Key 1: xxxxx
# Initial Root Token: s.xxxxx
```

**⚠️ IMPORTANT : Sauvegarder ces valeurs dans un endroit sûr !**

```bash
# Unseal Vault avec la clé obtenue
vault operator unseal <UNSEAL_KEY>

# Quitter le pod
exit
```

### 3. Configurer Vault

```bash
# Depuis votre machine locale
# Remplacer <ROOT_TOKEN> par le token obtenu à l'étape 2
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

Ce script va :
- Activer le KV secret engine v2
- Créer la policy pour l'application
- Configurer l'authentification Kubernetes
- Créer le role pour le ServiceAccount
- Insérer les secrets de l'application

### 4. Mettre à jour les secrets dans Vault

```bash
# Port-forward pour accéder à Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Mettre à jour les secrets (remplacer les valeurs CHANGEME)
vault kv put secret/cinegest/app \
  APP_KEY="$(php artisan key:generate --show)" \
  DB_PASSWORD="votre_mot_de_passe_mysql" \
  MAIL_USERNAME="votre_mailjet_username" \
  MAIL_PASSWORD="votre_mailjet_password" \
  MAILJET_APIKEY="votre_mailjet_api_key" \
  MAILJET_APISECRET="votre_mailjet_api_secret" \
  STRIPE_KEY="votre_stripe_key" \
  STRIPE_SECRET="votre_stripe_secret" \
  STRIPE_WEBHOOK_SECRET="votre_stripe_webhook_secret"
```

### 5. Déployer External Secrets

```bash
# Créer le ServiceAccount pour l'authentification
kubectl apply -f k8s/vault/serviceaccount.yaml

# Créer le SecretStore (connexion à Vault)
kubectl apply -f k8s/secretstore.yaml

# Créer l'ExternalSecret (synchronisation)
kubectl apply -f k8s/externalsecret.yaml
```

### 6. Vérifier la synchronisation

```bash
# Vérifier que l'ExternalSecret est synchronisé
kubectl -n cinegest get externalsecret
# STATUS devrait être "SecretSynced"

# Vérifier que le Secret K8s a été créé
kubectl -n cinegest get secret cinegest-back-secret
# Devrait exister et contenir vos secrets

# Voir les clés du secret (pas les valeurs)
kubectl -n cinegest get secret cinegest-back-secret -o jsonpath='{.data}' | jq 'keys'
```

### 7. Déployer l'application

L'application est déjà configurée pour utiliser le secret `cinegest-back-secret` :

```bash
# Déployer normalement
kubectl apply -f k8s/deployment.yaml
kubectl apply -f k8s/service.yaml
kubectl apply -f k8s/ingress.yaml
```

## Gestion des secrets

### Lire un secret

```bash
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Port-forward si nécessaire
kubectl -n vault port-forward svc/vault 8200:8200 &

# Lire les secrets
vault kv get secret/cinegest/app
```

### Mettre à jour un secret

```bash
# Mettre à jour un secret spécifique
vault kv patch secret/cinegest/app \
  DB_PASSWORD="nouveau_mot_de_passe"

# L'ExternalSecret synchronise automatiquement (toutes les 15 min par défaut)
# Pour forcer la synchronisation immédiate :
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite
```

### Rotation des secrets

```bash
# 1. Mettre à jour dans Vault
vault kv patch secret/cinegest/app NEW_VALUE="nouvelle_valeur"

# 2. Forcer la sync
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite

# 3. Redémarrer les pods pour prendre en compte
kubectl -n cinegest rollout restart deployment/cinegest-back
```

## Unseal automatique (Production)

Pour éviter d'unseal manuellement après un redémarrage :

### Option 1: Vault Auto-unseal avec cloud KMS
Configurer avec GCP KMS, AWS KMS, ou Azure Key Vault

### Option 2: Script d'unseal
```bash
# Stocker l'unseal key dans un secret K8s (dev uniquement!)
kubectl -n vault create secret generic vault-unseal \
  --from-literal=key=<UNSEAL_KEY>

# Créer un initContainer dans le deployment Vault
# Voir la documentation Vault pour les détails
```

## Commandes utiles

```bash
# Voir les logs ESO
kubectl -n external-secrets logs -f deploy/external-secrets

# Status de l'ExternalSecret
kubectl -n cinegest describe externalsecret cinegest-back-vault

# Vérifier l'authentification Vault
vault read auth/kubernetes/role/cinegest-app

# Lister les policies
vault policy list
vault policy read cinegest-policy

# Health check Vault
vault status
```

## Troubleshooting

### ExternalSecret ne synchronise pas

```bash
# Vérifier les logs
kubectl -n external-secrets logs -f deploy/external-secrets

# Vérifier le SecretStore
kubectl -n cinegest describe secretstore vault-backend

# Vérifier l'ExternalSecret
kubectl -n cinegest describe externalsecret cinegest-back-vault
```

### Vault sealed après redémarrage

```bash
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator unseal <UNSEAL_KEY>
```

### Erreur d'authentification

```bash
# Re-configurer l'auth Kubernetes
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

## Sécurité

- ⚠️ Ne jamais commiter les clés Unseal ou Root Token dans Git
- ✅ Utiliser un gestionnaire de secrets pour stocker les clés de Vault
- ✅ Activer l'audit logging dans Vault
- ✅ Restreindre l'accès réseau à Vault
- ✅ Utiliser des policies restrictives
- ✅ Rotation régulière des secrets

## Migration depuis les Secrets Kubernetes

Si vous utilisez actuellement des secrets manuels K8s :

1. Les secrets sont maintenant dans Vault
2. L'ExternalSecret crée automatiquement `cinegest-back-secret`
3. Le deployment utilise toujours `cinegest-back-secret` (aucun changement)
4. Supprimez l'ancien secret manuel :
   ```bash
   kubectl -n cinegest delete secret cinegest-back-secret
   ```
5. L'ExternalSecret le recréera automatiquement depuis Vault
