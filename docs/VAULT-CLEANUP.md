# Nettoyage et réinstallation complète de Vault

Ce document contient les commandes pour supprimer complètement Vault, External Secrets Operator et toutes les ressources associées, puis réinstaller proprement.

## 🗑️ Suppression complète

### 1. Supprimer les External Secrets

```bash
# Supprimer l'ExternalSecret (arrête la synchronisation)
sudo kubectl -n cinegest delete externalsecret cinegest-back-vault

# Supprimer le SecretStore
sudo kubectl -n cinegest delete secretstore vault-backend

# Supprimer le secret Kubernetes créé
sudo kubectl -n cinegest delete secret cinegest-back-secret

# Supprimer le ServiceAccount
sudo kubectl -n cinegest delete sa vault-auth

# Supprimer le secret token du ServiceAccount
sudo kubectl -n cinegest delete secret vault-auth-token
```

### 2. Supprimer les permissions RBAC

```bash
# Supprimer le ClusterRoleBinding
sudo kubectl delete clusterrolebinding vault-tokenreview-binding
```

### 3. Supprimer Vault

```bash
# Supprimer le déploiement Vault
sudo kubectl -n vault delete deployment vault

# Supprimer le service Vault
sudo kubectl -n vault delete service vault

# Supprimer le namespace Vault (optionnel, supprime tout)
sudo kubectl delete namespace vault
```

### 4. Supprimer External Secrets Operator

```bash
# Désinstaller via Helm
sudo helm uninstall external-secrets -n external-secrets

# Supprimer les CRDs (Custom Resource Definitions)
sudo kubectl delete crd secretstores.external-secrets.io
sudo kubectl delete crd externalsecrets.external-secrets.io
sudo kubectl delete crd clustersecretstores.external-secrets.io
sudo kubectl delete crd clusterexternalsecrets.external-secrets.io

# Supprimer le namespace
sudo kubectl delete namespace external-secrets
```

### 5. Nettoyer les namespaces (optionnel)

```bash
# Recréer le namespace cinegest s'il a été supprimé
sudo kubectl create namespace cinegest
```

## 🔄 Script de nettoyage complet (une seule commande)

```bash
#!/bin/bash
set -x

echo "🗑️  Suppression des External Secrets..."
sudo kubectl -n cinegest delete externalsecret cinegest-back-vault --ignore-not-found
sudo kubectl -n cinegest delete secretstore vault-backend --ignore-not-found
sudo kubectl -n cinegest delete secret cinegest-back-secret --ignore-not-found
sudo kubectl -n cinegest delete sa vault-auth --ignore-not-found
sudo kubectl -n cinegest delete secret vault-auth-token --ignore-not-found

echo "🗑️  Suppression des RBAC..."
sudo kubectl delete clusterrolebinding vault-tokenreview-binding --ignore-not-found

echo "🗑️  Suppression de Vault..."
sudo kubectl delete namespace vault --ignore-not-found

echo "🗑️  Suppression de External Secrets Operator..."
sudo helm uninstall external-secrets -n external-secrets --ignore-not-found 2>/dev/null || true
sudo kubectl delete crd secretstores.external-secrets.io --ignore-not-found
sudo kubectl delete crd externalsecrets.external-secrets.io --ignore-not-found
sudo kubectl delete crd clustersecretstores.external-secrets.io --ignore-not-found
sudo kubectl delete crd clusterexternalsecrets.external-secrets.io --ignore-not-found
sudo kubectl delete namespace external-secrets --ignore-not-found

echo "✅ Nettoyage terminé !"
```

Copiez ce script dans un fichier (ex: `cleanup-vault.sh`), rendez-le exécutable et lancez-le :

```bash
chmod +x cleanup-vault.sh
./cleanup-vault.sh
```

## ✨ Réinstallation complète

Après le nettoyage, attendez 30 secondes que toutes les ressources soient supprimées, puis :

### Ordre de réinstallation

```bash
# 1. Installer Vault et ESO
cd k8s/vault
./install.sh

# 2. Initialiser Vault (noter les clés !)
sudo kubectl -n vault exec -it deploy/vault -- vault operator init -key-shares=1 -key-threshold=1

# 3. Déverrouiller Vault
sudo kubectl -n vault exec -it deploy/vault -- vault operator unseal <UNSEAL_KEY>

# 4. Appliquer les RBAC (IMPORTANT!)
sudo kubectl apply -f k8s/vault/rbac.yaml

# 5. Configurer Vault
./configure-vault.sh <ROOT_TOKEN>

# 6. Déployer les External Secrets
sudo kubectl apply -f k8s/vault/serviceaccount.yaml
sudo kubectl apply -f k8s/secretstore.yaml
sudo kubectl apply -f k8s/externalsecret.yaml

# 7. Vérifier
sudo kubectl -n cinegest get secretstore vault-backend
sudo kubectl -n cinegest get externalsecret cinegest-back-vault
sudo kubectl -n cinegest get secret cinegest-back-secret
```

### Résultat attendu

```bash
$ sudo kubectl -n cinegest get secretstore vault-backend
NAME            AGE   STATUS   CAPABILITIES   READY
vault-backend   30s   Valid    ReadWrite      True

$ sudo kubectl -n cinegest get externalsecret cinegest-back-vault
NAME                  STORETYPE     STORE           REFRESH INTERVAL   STATUS         READY   LAST SYNC
cinegest-back-vault   SecretStore   vault-backend   15m                SecretSynced   True    10s

$ sudo kubectl -n cinegest get secret cinegest-back-secret
NAME                   TYPE     DATA   AGE
cinegest-back-secret   Opaque   29     10s
```

## ⚠️ Points d'attention

1. **Sauvegardez vos clés Vault** avant de supprimer (Unseal Key + Root Token)
2. **Les secrets dans Vault seront perdus** lors de la suppression (Vault en mode dev)
3. **Les CRDs peuvent être lents à supprimer** (attendre la suppression complète)
4. **Le RBAC doit être appliqué AVANT** de configurer Vault
5. **Ne pas oublier** de déverrouiller Vault après chaque redémarrage

## 🔐 Pour un environnement de production

En production, utilisez :
- Vault avec stockage persistant (PVC)
- Auto-unseal (Cloud KMS)
- Haute disponibilité (multiple replicas)
- Backup automatique des secrets

Voir la [documentation officielle Vault](https://developer.hashicorp.com/vault/tutorials/kubernetes/kubernetes-raft-deployment-guide) pour la configuration production.
