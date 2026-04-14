# Configuration Vault + Kubernetes - Guide Rapide

## 📁 Fichiers créés

### Configuration Vault
- `k8s/vault/namespace.yaml` - Namespaces vault & external-secrets
- `k8s/vault/pvc.yaml` - Stockage persistant pour Vault
- `k8s/vault/deployment.yaml` - Déploiement Vault
- `k8s/vault/service.yaml` - Service Vault
- `k8s/vault/serviceaccount.yaml` - ServiceAccount pour l'auth

### External Secrets
- `k8s/secretstore.yaml` - Connexion à Vault
- `k8s/externalsecret.yaml` - Synchronisation des secrets

### Scripts
- `k8s/vault/install.sh` - Installation ESO + Vault
- `k8s/vault/configure-vault.sh` - Configuration Vault

## 🚀 Installation Rapide

### Étape 1: Installer Vault et ESO
```bash
cd /home/nicolas/cinegest/cinegest-back
./k8s/vault/install.sh
```

### Étape 2: Initialiser Vault
```bash
# Se connecter au pod
kubectl -n vault exec -it deploy/vault -- sh

# Dans le pod
export VAULT_ADDR=http://localhost:8200
vault operator init -key-shares=1 -key-threshold=1

# Sauvegarder Unseal Key et Root Token !
# Puis unseal :
vault operator unseal <UNSEAL_KEY>
exit
```

### Étape 3: Configurer Vault
```bash
# Remplacer <ROOT_TOKEN> par votre token
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

### Étape 4: Mettre à jour les secrets
```bash
# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Générer APP_KEY
APP_KEY=$(php artisan key:generate --show)

# Mettre à jour les secrets réels
vault kv patch secret/cinegest/app \
  APP_KEY="$APP_KEY" \
  DB_PASSWORD="votre_password_mysql" \
  MAIL_USERNAME="votre_mailjet_user" \
  MAIL_PASSWORD="votre_mailjet_pass" \
  MAILJET_APIKEY="votre_api_key" \
  MAILJET_APISECRET="votre_api_secret" \
  STRIPE_KEY="pk_..." \
  STRIPE_SECRET="sk_..." \
  STRIPE_WEBHOOK_SECRET="whsec_..."
```

### Étape 5: Déployer l'application
```bash
# La pipeline automatique s'occupe du déploiement
git push origin main

# Ou déploiement manuel via GitHub Actions:
# Actions → Deploy to Production → Run workflow
```

## 🔄 Workflow de déploiement

```
┌─────────────────────────────────────────────────────┐
│  1. Secrets stockés dans Vault                     │
│     vault kv put secret/cinegest/app ...            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│  2. External Secrets Operator synchronise           │
│     Toutes les 15 minutes (ou sur demande)          │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│  3. Secret Kubernetes créé/mis à jour               │
│     cinegest-back-secret (namespace: cinegest)      │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│  4. Pods utilisent le secret via envFrom            │
│     Redémarrage nécessaire pour nouvelles valeurs   │
└─────────────────────────────────────────────────────┘
```

## 📝 Commandes utiles

### Gérer les secrets dans Vault
```bash
# Lire les secrets
vault kv get secret/cinegest/app

# Mettre à jour un secret
vault kv patch secret/cinegest/app DB_PASSWORD="nouveau_mdp"

# Voir l'historique des versions
vault kv metadata get secret/cinegest/app
```

### Forcer la synchronisation
```bash
# Forcer ESO à synchroniser immédiatement
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite

# Redémarrer l'app pour utiliser les nouveaux secrets
kubectl -n cinegest rollout restart deployment/cinegest-back
```

### Vérifier le status
```bash
# Status de Vault
kubectl -n vault exec deploy/vault -- vault status

# Status External Secret
kubectl -n cinegest get externalsecret cinegest-back-vault
kubectl -n cinegest describe externalsecret cinegest-back-vault

# Vérifier le secret K8s
kubectl -n cinegest get secret cinegest-back-secret
```

### Logs et debugging
```bash
# Logs External Secrets Operator
kubectl -n external-secrets logs -f deploy/external-secrets

# Logs de l'application
kubectl -n cinegest logs -f deployment/cinegest-back

# Logs Vault
kubectl -n vault logs -f deploy/vault
```

## 🔒 Sécurité

### Où stocker les clés Vault ?

**❌ Ne JAMAIS faire :**
- Commiter les clés dans Git
- Les laisser en clair sur votre machine

**✅ Bonnes pratiques :**
- Utiliser un gestionnaire de mots de passe (1Password, LastPass, etc.)
- Stocker dans un cloud sécurisé (AWS Secrets Manager, etc.)
- Pour la production : utiliser Vault Auto-unseal avec KMS

### Unseal automatique après redémarrage

Option 1 (dev uniquement) - Stocker l'unseal key dans K8s:
```bash
kubectl -n vault create secret generic vault-unseal \
  --from-literal=key=<UNSEAL_KEY>
```

Option 2 (production) - Utiliser Auto-unseal:
- AWS KMS
- GCP Cloud KMS
- Azure Key Vault

## 🆘 Troubleshooting

### Vault est sealed
```bash
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator unseal <UNSEAL_KEY>
```

### ExternalSecret ne synchronise pas
```bash
# Vérifier les logs ESO
kubectl -n external-secrets logs -f deploy/external-secrets

# Vérifier la connexion au SecretStore
kubectl -n cinegest describe secretstore vault-backend

# Forcer la sync
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite
```

### Erreur d'authentification Vault
```bash
# Re-configurer l'auth
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

### Le pod ne démarre pas
```bash
# Vérifier que le secret existe
kubectl -n cinegest get secret cinegest-back-secret

# Vérifier les events
kubectl -n cinegest describe pod <pod-name>

# Vérifier les logs
kubectl -n cinegest logs <pod-name>
```

## 📚 Documentation complète

Voir `k8s/vault/README.md` pour la documentation détaillée.

## 🎯 Prochaines étapes

1. ✅ Vault installé et configuré
2. ✅ External Secrets Operator installé
3. ✅ Secrets synchronisés
4. ✅ Application déployée
5. ⏭️ Configurer les backups Vault
6. ⏭️ Mettre en place la rotation automatique des secrets
7. ⏭️ Configurer l'audit logging Vault
8. ⏭️ Implémenter Auto-unseal pour la production
