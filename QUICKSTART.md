# ⚡ Setup Rapide - Commandes Essentielles

## 🚀 Configuration automatique (recommandé)

```bash
./setup-infrastructure.sh
```

Puis suivre les instructions à l'écran.

---

## 📝 Configuration manuelle (version courte)

### 1. Installer Vault + ESO
```bash
./k8s/vault/install.sh
```

### 2. Initialiser Vault
```bash
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://127.0.0.1:8200
vault operator init -key-shares=1 -key-threshold=1
# ⚠️ SAUVEGARDER: Unseal Key + Root Token
vault operator unseal <UNSEAL_KEY>
exit
```

### 3. Configurer Vault
```bash
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

### 4. Configurer les secrets
```bash
kubectl -n vault port-forward svc/vault 8200:8200 &
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

APP_KEY=$(php artisan key:generate --show)

vault kv put secret/cinegest/app \
  APP_KEY="$APP_KEY" \
  DB_PASSWORD="VOTRE_PASSWORD" \
  MAIL_USERNAME="VOTRE_MAILJET_USER" \
  MAIL_PASSWORD="VOTRE_MAILJET_PASS" \
  MAILJET_APIKEY="VOTRE_KEY" \
  MAILJET_APISECRET="VOTRE_SECRET" \
  STRIPE_KEY="pk_live_xxx" \
  STRIPE_SECRET="sk_live_xxx" \
  STRIPE_WEBHOOK_SECRET="whsec_xxx"
  # ... (voir SETUP.md pour la liste complète)

killall kubectl
```

### 5. Configurer GitHub
```bash
cat ~/.kube/config | base64 -w0
# Copier la sortie
```

→ GitHub: **Settings → Secrets → New secret**
- Name: `KUBECONFIG_B64`
- Value: `<coller la valeur>`

### 6. Déployer manifestes Vault
```bash
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/vault/serviceaccount.yaml
kubectl apply -f k8s/secretstore.yaml
kubectl apply -f k8s/externalsecret.yaml

# Attendre sync (1-2 min)
kubectl -n cinegest get externalsecret
```

### 7. Premier déploiement
```bash
git push origin main
# → Pipeline automatique sur GitHub Actions
```

### 8. Vérifier
```bash
kubectl -n cinegest get pods
kubectl -n cinegest logs -f deployment/cinegest-back
```

---

## ✅ Après configuration

**Workflow normal:**
```bash
git commit -m "feat: nouvelle fonctionnalité"
git push origin main
# → déploiement automatique
```

---

## 🔧 Commandes quotidiennes

**Logs:**
```bash
kubectl -n cinegest logs -f deployment/cinegest-back
```

**Mettre à jour un secret:**
```bash
kubectl -n vault port-forward svc/vault 8200:8200 &
export VAULT_ADDR=http://localhost:8200 VAULT_TOKEN=<ROOT_TOKEN>
vault kv patch secret/cinegest/app KEY="nouvelle_valeur"
kubectl -n cinegest rollout restart deployment/cinegest-back
```

**Unseal Vault (après redémarrage):**
```bash
kubectl -n vault exec -it deploy/vault -- sh
vault operator unseal <UNSEAL_KEY>
```

---

📚 **Documentation complète:** [SETUP.md](SETUP.md)
