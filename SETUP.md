# 🚀 Guide de Configuration - Commandes Étape par Étape

## Option 1: Script Automatique (Recommandé)

```bash
# Exécuter le script de configuration complète
./setup-infrastructure.sh
```

Le script va tout configurer automatiquement. **Suivez simplement les instructions à l'écran.**

---

## Option 2: Configuration Manuelle

Si vous préférez faire les étapes manuellement :

### 📋 **Prérequis**

```bash
# Vérifier que tout est installé
kubectl version --client
helm version
vault version

# Vérifier connexion au cluster
kubectl cluster-info
kubectl get nodes
```

---

### **ÉTAPE 1: Installation Vault + External Secrets Operator**

```bash
# Installer ESO et Vault
./k8s/vault/install.sh

# Vérifier l'installation
kubectl -n external-secrets get pods
kubectl -n vault get pods

# Attendre que Vault soit prêt
kubectl -n vault wait --for=condition=ready pod -l app=vault --timeout=120s
```

---

### **ÉTAPE 2: Initialiser Vault**

```bash
# Se connecter au pod Vault
kubectl -n vault exec -it deploy/vault -- sh

# Dans le pod Vault
export VAULT_ADDR=http://127.0.0.1:8200

# Initialiser Vault (SAUVEGARDER LA SORTIE !)
vault operator init -key-shares=1 -key-threshold=1

# ⚠️ COPIER ET SAUVEGARDER:
# - Unseal Key 1: xxxxx
# - Initial Root Token: s.xxxxx

# Unseal Vault avec la clé obtenue
vault operator unseal <UNSEAL_KEY>

# Vérifier le statut (Sealed doit être "false")
vault status

# Quitter le pod
exit
```

**💾 Sauvegarder les clés dans un gestionnaire de mots de passe !**

---

### **ÉTAPE 3: Configurer Vault**

```bash
# Configurer Vault (remplacer <ROOT_TOKEN> par votre token)
./k8s/vault/configure-vault.sh <ROOT_TOKEN>

# Vérifier la configuration
kubectl -n vault exec deploy/vault -- vault status
```

---

### **ÉTAPE 4: Configurer les secrets de l'application**

```bash
# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Générer la clé Laravel
APP_KEY=$(php artisan key:generate --show)
echo "APP_KEY généré: $APP_KEY"

# Insérer tous les secrets dans Vault
vault kv put secret/cinegest/app \
  APP_NAME="CineGest" \
  APP_ENV="production" \
  APP_KEY="$APP_KEY" \
  APP_DEBUG="false" \
  APP_URL="https://api.cinegest.nicolasbarbey.fr" \
  APP_FRONTEND_URL="https://cinegest.nicolasbarbey.fr" \
  DB_CONNECTION="mysql" \
  DB_HOST="mysql" \
  DB_PORT="3306" \
  DB_DATABASE="cinegest" \
  DB_USERNAME="cinegest" \
  DB_PASSWORD="VOTRE_PASSWORD_MYSQL" \
  SESSION_DRIVER="database" \
  CACHE_STORE="database" \
  QUEUE_CONNECTION="database" \
  SANCTUM_STATEFUL_DOMAINS="cinegest.nicolasbarbey.fr" \
  MAIL_MAILER="smtp" \
  MAIL_HOST="in-v3.mailjet.com" \
  MAIL_PORT="587" \
  MAIL_USERNAME="VOTRE_MAILJET_USER" \
  MAIL_PASSWORD="VOTRE_MAILJET_PASS" \
  MAIL_ENCRYPTION="tls" \
  MAIL_FROM_ADDRESS="no-reply@cinegest.nicolasbarbey.fr" \
  MAIL_FROM_NAME="CineGest" \
  MAILJET_APIKEY="VOTRE_MAILJET_API_KEY" \
  MAILJET_APISECRET="VOTRE_MAILJET_API_SECRET" \
  STRIPE_KEY="pk_live_VOTRE_KEY" \
  STRIPE_SECRET="sk_live_VOTRE_SECRET" \
  STRIPE_WEBHOOK_SECRET="whsec_VOTRE_SECRET"

# Vérifier les secrets
vault kv get secret/cinegest/app

# Arrêter le port-forward
killall kubectl
```

**⚠️ Remplacer toutes les valeurs VOTRE_xxx par vos vraies valeurs !**

---

### **ÉTAPE 5: Configurer GitHub Actions**

```bash
# Générer le secret pour GitHub Actions
cat ~/.kube/config | base64 -w0

# Copier la sortie
```

**Configuration dans GitHub:**

1. Aller sur: `https://github.com/VOTRE_USERNAME/cinegest-back/settings/secrets/actions`
2. Cliquer **New repository secret**
3. Name: `KUBECONFIG_B64`
4. Value: `<coller la valeur générée ci-dessus>`
5. Cliquer **Add secret**

✅ Secret GitHub configuré

---

### **ÉTAPE 6: Déployer les manifestes Vault**

```bash
# Créer les namespaces
kubectl apply -f k8s/namespace.yaml

# Déployer les manifestes Vault
kubectl apply -f k8s/vault/serviceaccount.yaml
kubectl apply -f k8s/secretstore.yaml
kubectl apply -f k8s/externalsecret.yaml

# Vérifier la synchronisation des secrets (peut prendre 1-2 min)
kubectl -n cinegest get externalsecret cinegest-back-vault
# STATUS devrait être "SecretSynced"

# Vérifier que le secret K8s a été créé
kubectl -n cinegest get secret cinegest-back-secret
# Devrait exister

# Voir les détails si problème
kubectl -n cinegest describe externalsecret cinegest-back-vault
```

✅ Secrets synchronisés de Vault vers Kubernetes

---

### **ÉTAPE 7: Premier déploiement (via pipeline)**

```bash
# Commit et push pour déclencher la pipeline
git add .
git commit -m "chore: infrastructure configuration complete"
git push origin main

# Ou trigger manuel sans commit
git commit --allow-empty -m "trigger: initial deployment"
git push origin main
```

**La pipeline GitHub Actions va automatiquement:**
1. ✅ Exécuter les tests
2. ✅ Builder l'image Docker
3. ✅ Installer ESO (si nécessaire)
4. ✅ Déployer les manifests K8s
5. ✅ Synchroniser les secrets Vault
6. ✅ Déployer l'application
7. ✅ Exécuter les migrations
8. ✅ Vérifier le rollout

---

### **ÉTAPE 8: Vérification du déploiement**

```bash
# Surveiller la pipeline sur GitHub
# https://github.com/VOTRE_USERNAME/cinegest-back/actions

# Vérifier les pods
kubectl -n cinegest get pods
# Devrait afficher 2 pods "Running"

# Voir les logs
kubectl -n cinegest logs -f deployment/cinegest-back

# Vérifier le déploiement
kubectl -n cinegest get deployment cinegest-back
# READY devrait être "2/2"

# Vérifier l'ingress
kubectl -n cinegest get ingress
# devrait afficher l'URL

# Tester l'API
curl -k https://api.cinegest.nicolasbarbey.fr/health
# Devrait retourner: {"status":"ok"}
```

---

## 🎉 Configuration terminée !

**Workflow normal après configuration:**

```bash
# 1. Développer normalement
git add .
git commit -m "feat: nouvelle fonctionnalité"

# 2. Push → déclenche automatiquement la pipeline
git push origin main

# 3. Vérifier le déploiement
kubectl -n cinegest get pods
```

---

## 📊 Commandes utiles quotidiennes

### Voir les logs
```bash
kubectl -n cinegest logs -f deployment/cinegest-back
kubectl -n cinegest logs job/cinegest-back-migrate
```

### Mettre à jour un secret
```bash
# 1. Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# 2. Mettre à jour
vault kv patch secret/cinegest/app \
  DB_PASSWORD="nouveau_password"

# 3. Forcer la sync (optionnel, sinon auto dans 15 min)
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite

# 4. Redémarrer l'app
kubectl -n cinegest rollout restart deployment/cinegest-back
```

### Scaler l'application
```bash
kubectl -n cinegest scale deployment cinegest-back --replicas=5
```

### Rollback
```bash
kubectl -n cinegest rollout undo deployment/cinegest-back
```

### Debug dans un pod
```bash
kubectl -n cinegest exec -it deployment/cinegest-back -- bash
php artisan tinker
```

---

## 🆘 Troubleshooting

### Vault sealed après redémarrage
```bash
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://127.0.0.1:8200
vault operator unseal <UNSEAL_KEY>
exit
```

### Secrets non synchronisés
```bash
# Voir les logs ESO
kubectl -n external-secrets logs -f deploy/external-secrets

# Vérifier ExternalSecret
kubectl -n cinegest describe externalsecret cinegest-back-vault

# Forcer la sync
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite
```

### Pipeline échoue
```bash
# Vérifier les tests localement
php artisan test

# Vérifier le secret GitHub
# Settings → Secrets → KUBECONFIG_B64 doit exister

# Voir les logs de la pipeline sur GitHub Actions
```

---

## 📚 Documentation

- [Guide rapide](docs/QUICKSTART-PRODUCTION.md) - Déploiement complet
- [Architecture](docs/ARCHITECTURE.md) - Diagrammes détaillés
- [Workflows CI/CD](.github/README.md) - Guide des workflows
- [Vault complet](k8s/vault/README.md) - Documentation Vault

---

## ✅ Checklist de vérification

Après configuration, vérifier:

- [ ] Vault running et unseal
- [ ] External Secrets Operator running
- [ ] Secret KUBECONFIG_B64 dans GitHub
- [ ] Secrets Vault configurés avec vraies valeurs
- [ ] ExternalSecret status "SecretSynced"
- [ ] Secret K8s `cinegest-back-secret` existe
- [ ] Pipeline GitHub Actions passe
- [ ] 2 pods running
- [ ] API accessible et health check OK

**Si tous les points sont ✅, vous êtes prêt pour déployer en continu !**
