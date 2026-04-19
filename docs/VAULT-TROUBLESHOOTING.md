# 🔧 Vault & External Secrets - Guide de Dépannage

Guide complet de dépannage pour les problèmes liés à Vault et External Secrets Operator.

---

## 📋 Table des matières

1. [Diagnostic rapide](#diagnostic-rapide)
2. [Problèmes courants](#problèmes-courants)
3. [Commandes de vérification](#commandes-de-vérification)
4. [Procédures de résolution](#procédures-de-résolution)
5. [Logs et débogage](#logs-et-débogage)

---

## 🚨 Diagnostic rapide

### Vérification de l'état global

```bash
# Vérifier que Vault est démarré et unseal
sudo kubectl -n vault get pods

# Vérifier le SecretStore
sudo kubectl -n cinegest get secretstore vault-backend

# Vérifier l'ExternalSecret
sudo kubectl -n cinegest get externalsecret cinegest-back-vault

# Vérifier que le secret K8s a été créé
sudo kubectl -n cinegest get secret cinegest-back-secret
```

### États attendus

| Ressource | État attendu |
|-----------|--------------|
| **Pod Vault** | `Running`, `1/1 Ready` |
| **SecretStore** | `STATUS: Ready` |
| **ExternalSecret** | `STATUS: SecretSynced` |
| **Secret K8s** | Doit exister avec toutes les clés |

---

## ⚠️ Problèmes courants

### 1. ExternalSecret: "SecretStore is not ready"

**Symptôme:**
```
Error: SecretStore "vault-backend" is not ready
```

**Causes possibles:**
- ✗ Vault est sealed
- ✗ Vault n'est pas démarré
- ✗ Service account manquant
- ✗ RBAC non configuré

**Solution:**

```bash
# 1. Vérifier que Vault est unseal
sudo kubectl -n vault exec deploy/vault -- vault status

# Si sealed, unseal avec la clé:
sudo kubectl -n vault exec deploy/vault -- vault operator unseal <UNSEAL_KEY>

# 2. Vérifier le SecretStore
sudo kubectl -n cinegest describe secretstore vault-backend

# 3. Appliquer les RBAC si manquants
sudo kubectl apply -f k8s/vault/rbac.yaml
sudo kubectl apply -f k8s/vault/serviceaccount.yaml
```

---

### 2. ExternalSecret: "Permission denied"

**Symptôme:**
```
Code: 403. Errors: * permission denied
```

**Causes possibles:**
- ✗ Policy Vault incorrecte ou manquante
- ✗ Role Kubernetes non configuré
- ✗ Service account incorrect

**Solution:**

```bash
# Vérifier la configuration Vault
# (nécessite le ROOT_TOKEN)

# Port-forward vers Vault
sudo kubectl -n vault port-forward svc/vault 8200:8200 &
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN="<ROOT_TOKEN>"

# Vérifier la policy
vault policy read cinegest-policy

# Vérifier le role
vault read auth/kubernetes/role/cinegest-app

# Si manquant, reconfigurer Vault
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

---

### 3. ExternalSecret: "could not get secret data from provider"

**Symptôme:**
```
Message: could not get secret data from provider
```

**Causes possibles:**
- ✗ Le secret n'existe pas dans Vault au bon chemin
- ✗ Mauvais format de clé dans externalsecret.yaml

**Solution:**

```bash
# Vérifier que le secret existe dans Vault
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN="<ROOT_TOKEN>"

vault kv get secret/cinegest/app

# Vérifier le chemin dans externalsecret.yaml
# Le chemin doit être: cinegest/app (sans "secret/" devant)

# Si le secret n'existe pas, le créer:
./update-vault-secrets.sh <ROOT_TOKEN>
```

---

### 4. Vault: "Vault is sealed"

**Symptôme:**
```
Error: Vault is sealed
```

**Causes:**
- Pod Vault redémarré
- Cluster redémarré
- Vault non initialisé

**Solution:**

```bash
# Vérifier l'état
sudo kubectl -n vault exec deploy/vault -- vault status

# Unseal Vault
sudo kubectl -n vault exec deploy/vault -- vault operator unseal <UNSEAL_KEY>

# ⚠️ Important: Sauvegardez toujours votre UNSEAL_KEY!
```

---

### 5. Service Account: Token creation failed

**Symptôme:**
```
error: failed to create token: may not specify a duration less than 10 minutes
```

**Cause:**
- Durée de token invalide (< 10 minutes)

**Solution:**
```bash
# Utiliser une durée minimale de 10 minutes
kubectl -n cinegest create token vault-auth --duration=10m
```

---

### 6. Deployment: Secret not found

**Symptôme:**
```
Error: couldn't find key cinegest-back-secret
```

**Cause:**
- ExternalSecret n'a pas synchronisé le secret

**Solution:**

```bash
# 1. Vérifier l'état de l'ExternalSecret
sudo kubectl -n cinegest describe externalsecret cinegest-back-vault

# 2. Forcer la synchronisation
sudo kubectl -n cinegest delete externalsecret cinegest-back-vault
sudo kubectl apply -f k8s/externalsecret.yaml

# 3. Attendre la synchronisation (max 60s)
watch sudo kubectl -n cinegest get externalsecret cinegest-back-vault
```

---

## 🔍 Commandes de vérification

### Vérifier Vault

```bash
# Statut du pod
sudo kubectl -n vault get pods

# Logs Vault
sudo kubectl -n vault logs -f deploy/vault

# Statut Vault (sealed/unsealed)
sudo kubectl -n vault exec deploy/vault -- vault status

# Se connecter au pod
sudo kubectl -n vault exec -it deploy/vault -- sh
```

### Vérifier External Secrets Operator

```bash
# Status de l'opérateur
sudo kubectl -n external-secrets get pods

# Logs de l'opérateur
sudo kubectl -n external-secrets logs -f deploy/external-secrets

# Version installée
sudo helm list -n external-secrets
```

### Vérifier les ressources de synchronisation

```bash
# SecretStore détaillé
sudo kubectl -n cinegest describe secretstore vault-backend

# ExternalSecret détaillé
sudo kubectl -n cinegest describe externalsecret cinegest-back-vault

# Contenu du secret K8s (base64 encodé)
sudo kubectl -n cinegest get secret cinegest-back-secret -o yaml

# Liste des clés dans le secret
sudo kubectl -n cinegest get secret cinegest-back-secret -o jsonpath='{.data}' | jq 'keys'
```

### Vérifier le service account et RBAC

```bash
# Service account
sudo kubectl -n cinegest get serviceaccount vault-auth

# ClusterRoleBinding
sudo kubectl get clusterrolebinding vault-tokenreview-binding

# Tester la création de token
sudo kubectl -n cinegest create token vault-auth --duration=10m
```

---

## 🛠️ Procédures de résolution

### Réinitialisation complète de la synchronisation

Si tout échoue, réinitialiser complètement:

```bash
# 1. Supprimer les ressources de synchronisation
sudo kubectl -n cinegest delete externalsecret cinegest-back-vault
sudo kubectl -n cinegest delete secretstore vault-backend
sudo kubectl -n cinegest delete secret cinegest-back-secret

# 2. Supprimer et recréer le service account
sudo kubectl -n cinegest delete serviceaccount vault-auth
sudo kubectl delete clusterrolebinding vault-tokenreview-binding

# 3. Réappliquer tout
sudo kubectl apply -f k8s/vault/rbac.yaml
sudo kubectl apply -f k8s/vault/serviceaccount.yaml
sudo kubectl apply -f k8s/secretstore.yaml
sudo kubectl apply -f k8s/externalsecret.yaml

# 4. Vérifier
sudo kubectl -n cinegest get externalsecret cinegest-back-vault
```

### Reconfigurer Vault après redémarrage

```bash
# 1. Unseal Vault
sudo kubectl -n vault exec deploy/vault -- vault operator unseal <UNSEAL_KEY>

# 2. Vérifier la configuration
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

### Mettre à jour les secrets

```bash
# Script interactif pour modifier les secrets
./update-vault-secrets.sh <ROOT_TOKEN>

# Forcer la resynchronisation
sudo kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync="$(date +%s)" --overwrite
```

---

## 📝 Logs et débogage

### Activer les logs détaillés

```bash
# Logs External Secrets Operator
sudo kubectl -n external-secrets logs -f deploy/external-secrets --tail=100

# Logs Vault
sudo kubectl -n vault logs -f deploy/vault --tail=100

# Watch des events
sudo kubectl -n cinegest get events --watch
```

### Informations de débogage complètes

```bash
#!/bin/bash
# Script de collecte de logs pour support

echo "=== VAULT POD ==="
sudo kubectl -n vault get pods
echo ""

echo "=== VAULT STATUS ==="
sudo kubectl -n vault exec deploy/vault -- vault status
echo ""

echo "=== SECRETSTORE ==="
sudo kubectl -n cinegest describe secretstore vault-backend
echo ""

echo "=== EXTERNALSECRET ==="
sudo kubectl -n cinegest describe externalsecret cinegest-back-vault
echo ""

echo "=== SERVICE ACCOUNT ==="
sudo kubectl -n cinegest get serviceaccount vault-auth -o yaml
echo ""

echo "=== EVENTS ==="
sudo kubectl -n cinegest get events --sort-by='.lastTimestamp'
```

---

## 🆘 Support et ressources

### Documentation officielle

- [Vault Documentation](https://www.vaultproject.io/docs)
- [External Secrets Operator](https://external-secrets.io)
- [Kubernetes Secrets](https://kubernetes.io/docs/concepts/configuration/secret/)

### Fichiers de configuration

- [k8s/vault/](../k8s/vault/) - Configuration Vault
- [k8s/secretstore.yaml](../k8s/secretstore.yaml) - SecretStore
- [k8s/externalsecret.yaml](../k8s/externalsecret.yaml) - ExternalSecret
- [VAULT-SETUP.md](VAULT-SETUP.md) - Guide d'installation

### Scripts utiles

- [`k8s/vault/install.sh`](../k8s/vault/install.sh) - Installation Vault + ESO
- [`k8s/vault/configure-vault.sh`](../k8s/vault/configure-vault.sh) - Configuration Vault
- [`update-vault-secrets.sh`](../update-vault-secrets.sh) - Mise à jour des secrets
- [`setup-infrastructure.sh`](../setup-infrastructure.sh) - Installation complète

---

## ✅ Checklist de vérification

Avant de demander de l'aide, vérifier:

- [ ] Vault est démarré et **unseal**
- [ ] Le secret existe dans Vault au chemin `secret/cinegest/app`
- [ ] La policy `cinegest-policy` existe
- [ ] Le role `cinegest-app` existe dans l'auth Kubernetes
- [ ] Le service account `vault-auth` existe dans le namespace `cinegest`
- [ ] Le ClusterRoleBinding `vault-tokenreview-binding` existe
- [ ] Le SecretStore `vault-backend` est **Ready**
- [ ] L'ExternalSecret `cinegest-back-vault` est **SecretSynced**
- [ ] Le Secret K8s `cinegest-back-secret` existe avec toutes les clés

---

**Dernière mise à jour:** Avril 2026
