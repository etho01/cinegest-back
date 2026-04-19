# Configuration Vault + External Secrets Operator

Ce document explique comment installer et configurer HashiCorp Vault avec External Secrets Operator pour gérer les secrets de l'application CineGest.

## Prérequis

- Cluster K3s fonctionnel
- Helm 3 installé
- kubectl configuré avec accès au cluster
- Namespace `cinegest` créé

## Architecture

```
┌─────────────────┐
│  Application    │
│    Pods         │
└────────┬────────┘
         │ utilise
         ↓
┌─────────────────┐
│ cinegest-back-  │
│    secret       │ ← Secret Kubernetes créé automatiquement
└────────┬────────┘
         │ synchronisé par
         ↓
┌─────────────────┐
│ ExternalSecret  │ ← Définit quels secrets récupérer
└────────┬────────┘
         │ utilise
         ↓
┌─────────────────┐
│  SecretStore    │ ← Configure la connexion à Vault
└────────┬────────┘
         │ authentifie via
         ↓
┌─────────────────┐
│ ServiceAccount  │ ← vault-auth avec permissions RBAC
│   vault-auth    │
└────────┬────────┘
         │ autorisé par
         ↓
┌─────────────────┐
│ ClusterRole     │ ← system:auth-delegator
│    Binding      │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│  Vault Pod      │ ← Stocke les secrets chiffrés
│   (KV v2)       │
└─────────────────┘
```

## Installation

### 1. Installer Vault et ESO

```bash
cd k8s/vault
./install.sh
```

Ce script :
- Installe External Secrets Operator via Helm
- Déploie Vault en mode dev dans le namespace `vault`
- Attend que tous les pods soient prêts

### 2. Initialiser Vault (première fois uniquement)

Si Vault n'est pas initialisé :

```bash
sudo kubectl -n vault exec -it deploy/vault -- vault operator init -key-shares=1 -key-threshold=1
```

**⚠️ IMPORTANT** : Sauvegardez précieusement :
- **Unseal Key** : pour déverrouiller Vault après un redémarrage
- **Root Token** : pour configurer Vault

### 3. Déverrouiller Vault (après chaque redémarrage)

```bash
sudo kubectl -n vault exec -it deploy/vault -- vault operator unseal <UNSEAL_KEY>
```

### 4. Appliquer les permissions RBAC

**CRUCIAL** : Cette étape doit être faite AVANT de configurer Vault

```bash
sudo kubectl apply -f k8s/vault/rbac.yaml
```

Ce fichier crée un `ClusterRoleBinding` qui donne au ServiceAccount `vault-auth` le rôle `system:auth-delegator`, permettant à Vault de valider les tokens Kubernetes.

### 5. Configurer Vault

```bash
./configure-vault.sh <ROOT_TOKEN>
```

Ce script :
- Active le secret engine KV v2 (`secret/`)
- Crée la policy `cinegest-policy` (lecture dans `secret/data/cinegest/*`)
- Active l'authentification Kubernetes
- Configure Vault pour utiliser l'endpoint interne Kubernetes (`https://kubernetes.default.svc:443`)
- Crée le rôle `cinegest-app` lié au ServiceAccount `vault-auth`
- Insère les secrets initiaux (avec valeurs `CHANGEME`)

### 6. Déployer les External Secrets

```bash
sudo kubectl apply -f k8s/vault/serviceaccount.yaml
sudo kubectl apply -f k8s/secretstore.yaml
sudo kubectl apply -f k8s/externalsecret.yaml
```

### 7. Vérifier que tout fonctionne

```bash
# Vérifier le SecretStore
sudo kubectl -n cinegest get secretstore vault-backend
# Doit afficher: STATUS: Valid, READY: True

# Vérifier l'ExternalSecret
sudo kubectl -n cinegest get externalsecret cinegest-back-vault
# Doit afficher: STATUS: SecretSynced, READY: True

# Vérifier que le secret Kubernetes est créé
sudo kubectl -n cinegest get secret cinegest-back-secret
# Doit afficher le secret avec 29 clés (DATA: 29)
```

## Problèmes courants

### SecretStore en erreur 403 "permission denied"

**Cause** : Le RBAC `ClusterRoleBinding` n'est pas appliqué ou Vault utilise l'endpoint externe

**Solution** :
```bash
# Appliquer le RBAC
sudo kubectl apply -f k8s/vault/rbac.yaml

# Reconfigurer Vault avec l'endpoint interne
./configure-vault.sh <ROOT_TOKEN>
```

### Vault "sealed" après un redémarrage du pod

**Cause** : Vault utilise le stockage fichier qui ne persiste pas l'état "unsealed"

**Solution** :
```bash
sudo kubectl -n vault exec -it deploy/vault -- vault operator unseal <UNSEAL_KEY>
# Puis reconfigurer
./configure-vault.sh <ROOT_TOKEN>
```

### ExternalSecret en erreur "SecretStore not ready"

**Cause** : Le SecretStore n'arrive pas à s'authentifier auprès de Vault

**Solution** : Vérifier les logs ESO
```bash
sudo kubectl -n external-secrets logs -l app.kubernetes.io/name=external-secrets --tail=50
```

## Mettre à jour les secrets

### Méthode 1 : Via le script

```bash
./update-vault-secrets.sh
```

### Méthode 2 : Manuellement

```bash
sudo kubectl -n vault exec -it deploy/vault -- sh -c '
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

vault kv put secret/cinegest/app \
  APP_KEY="base64:VotreClefGeneree..." \
  DB_PASSWORD="VotreMotDePasse" \
  STRIPE_SECRET="sk_live_..." \
  ...
'
```

Les secrets seront automatiquement synchronisés dans le secret Kubernetes `cinegest-back-secret` dans les 15 minutes (ou immédiatement si vous supprimez/recréez l'ExternalSecret).

## Désinstallation complète

Voir [VAULT-CLEANUP.md](VAULT-CLEANUP.md)

## Références

- [HashiCorp Vault](https://www.vaultproject.io/)
- [External Secrets Operator](https://external-secrets.io/)
- [Vault Kubernetes Auth](https://developer.hashicorp.com/vault/docs/auth/kubernetes)
