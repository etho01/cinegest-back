# 📜 Guide des Scripts d'Installation et Maintenance

Documentation complète de tous les scripts d'installation, configuration et maintenance du projet CineGest Backend.

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Scripts d'installation](#scripts-dinstallation)
3. [Scripts de configuration](#scripts-de-configuration)
4. [Scripts de maintenance](#scripts-de-maintenance)
5. [Workflows typiques](#workflows-typiques)

---

## 🌐 Vue d'ensemble

### Localisation des scripts

```
cinegest-back/
├── setup-infrastructure.sh        # Installation complète (tout-en-un)
├── update-vault-secrets.sh        # Mise à jour des secrets
├── k8s/vault/
│   ├── install.sh                 # Installation Vault + ESO
│   └── configure-vault.sh         # Configuration Vault
└── artisan                        # CLI Laravel
```

### Matrice de dépendances

| Script | Prérequis | Produit | Usage |
|--------|-----------|---------|-------|
| `k8s/vault/install.sh` | K3s, kubectl, helm | Vault déployé | Installation initiale |
| `k8s/vault/configure-vault.sh` | Vault initialisé, ROOT_TOKEN | Vault configuré | Après installation |
| `update-vault-secrets.sh` | Vault configuré, ROOT_TOKEN | Secrets mis à jour | Maintenance |
| `setup-infrastructure.sh` | Tout | Infrastructure complète | Première installation |

---

## 🚀 Scripts d'installation

### 1. `setup-infrastructure.sh` - Installation complète

**Description:** Script principal orchestrant l'installation complète de l'infrastructure.

**Utilisation:**
```bash
./setup-infrastructure.sh
```

**Ce qu'il fait:**

1. ✅ Vérifie les prérequis (kubectl, helm, vault CLI)
2. ✅ Installe Vault + External Secrets Operator
3. ✅ Initialise Vault (génère UNSEAL_KEY et ROOT_TOKEN)
4. ✅ Configure Vault (policies, auth, roles)
5. ✅ Collecte et sauvegarde les secrets applicatifs
6. ✅ Génère le secret KUBECONFIG_B64 pour GitHub Actions
7. ✅ Déploie les manifests Kubernetes
8. ✅ Attend la synchronisation des secrets

**Prérequis:**
- Cluster K3s opérationnel
- `kubectl` configuré avec accès au cluster
- `helm` installé
- `vault` CLI installé
- `jq` installé

**Outputs:**
- `vault-keys.json` - Clés Vault (⚠️ à supprimer après sauvegarde)
- `github-kubeconfig-secret.txt` - Secret GitHub (⚠️ à supprimer après configuration)

**Exemple:**
```bash
# Installation complète en une commande
./setup-infrastructure.sh

# Suivre les instructions à l'écran
# Sauvegarder les clés affichées dans un gestionnaire de mots de passe
# Configurer GitHub avec le secret KUBECONFIG_B64
```

**Durée estimée:** 10-15 minutes

---

### 2. `k8s/vault/install.sh` - Installation Vault + ESO

**Description:** Installe Vault et External Secrets Operator dans le cluster.

**Utilisation:**
```bash
./k8s/vault/install.sh
```

**Ce qu'il fait:**

1. ✅ Ajoute le repo Helm External Secrets
2. ✅ Installe External Secrets Operator
3. ✅ Crée le namespace `vault`
4. ✅ Déploie Vault (PVC, Deployment, Service)
5. ✅ Attend que Vault soit prêt
6. ✅ Affiche les instructions pour initialisation manuelle

**Variables d'environnement:**
```bash
export KUBECONFIG=/etc/rancher/k3s/k3s.yaml
KUBECTL="sudo -E kubectl"
HELM="sudo -E helm"
```

**Ressources créées:**
- Namespace: `external-secrets`, `vault`
- Helm Release: `external-secrets`
- Deployment: `vault`
- Service: `vault` (ClusterIP:8200)
- PVC: `vault-storage` (1Gi)

**Sortie attendue:**
```
✅ External Secrets Operator installé
✅ Vault déployé
⏳ Pod Vault démarré

🔧 Initialisation de Vault...
Exécuter manuellement :
  kubectl -n vault exec -it deploy/vault -- sh
```

---

### 3. `k8s/vault/configure-vault.sh` - Configuration Vault

**Description:** Configure Vault après initialisation (secrets engine, policies, auth).

**Utilisation:**
```bash
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

**Exemple:**
```bash
./k8s/vault/configure-vault.sh hvs.XXXXXXXXXXXXXXXXXXXXXX
```

**Ce qu'il fait:**

1. ✅ Port-forward vers Vault (8200)
2. ✅ Active KV v2 secrets engine
3. ✅ Crée la policy `cinegest-policy`
4. ✅ Active l'auth Kubernetes
5. ✅ Configure la connexion Kubernetes
6. ✅ Crée le role `cinegest-app`
7. ✅ Insère les secrets initiaux (avec valeurs par défaut)

**Configuration créée:**

```hcl
# Policy cinegest-policy
path "secret/data/cinegest/*" {
  capabilities = ["read"]
}
path "secret/metadata/cinegest/*" {
  capabilities = ["read", "list"]
}

# Role cinegest-app
bound_service_account_names: vault-auth
bound_service_account_namespaces: cinegest
policies: cinegest-policy
ttl: 24h
```

**Secrets créés:**
- Path: `secret/cinegest/app`
- Clés: APP_*, DB_*, MAIL_*, STRIPE_*, etc.

**Vérification:**
```bash
# Port-forward
kubectl -n vault port-forward svc/vault 8200:8200 &

# Vérifier
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>
vault kv get secret/cinegest/app
```

---

## 🔧 Scripts de configuration

### 4. `update-vault-secrets.sh` - Mise à jour des secrets

**Description:** Interface interactive pour mettre à jour les secrets dans Vault.

**Utilisation:**
```bash
./update-vault-secrets.sh <ROOT_TOKEN>
```

**Exemple:**
```bash
./update-vault-secrets.sh hvs.XXXXXXXXXXXXXXXXXXXXXX
```

**Ce qu'il fait:**

1. ✅ Port-forward vers Vault
2. ✅ Récupère les secrets actuels
3. ✅ Interface interactive pour chaque secret
4. ✅ Validation avant sauvegarde
5. ✅ Mise à jour dans Vault
6. ✅ Vérification post-update

**Fonctionnalités:**
- Affiche les valeurs actuelles
- Permet de garder les valeurs existantes (ENTRÉE)
- Masque les secrets sensibles
- Demande confirmation avant sauvegarde

**Exemple d'interaction:**
```
🔑 DB_PASSWORD - Mot de passe MySQL
   Actuelle: mypassword123... (masquée)
   Nouvelle (ENTRÉE pour garder): [saisie utilisateur ou ENTRÉE]
```

**Après mise à jour:**
```bash
# Forcer la resynchronisation dans K8s
sudo kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync="$(date +%s)" --overwrite

# Redémarrer les pods pour appliquer
sudo kubectl -n cinegest rollout restart deployment/cinegest-back
```

---

## 🛠️ Scripts de maintenance

### Commandes Laravel (artisan)

**Description:** CLI Laravel pour migrations, seeders, cache, etc.

**Exemples courants:**

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Générer clé Laravel
php artisan key:generate --show

# Tests
php artisan test
```

**En production (dans le pod K8s):**
```bash
# Se connecter au pod
kubectl -n cinegest exec -it deploy/cinegest-back -- sh

# Puis exécuter artisan
php artisan migrate --force
php artisan optimize
```

---

## 📊 Workflows typiques

### Installation initiale (première fois)

```bash
# 1. Installation complète
./setup-infrastructure.sh

# 2. Sauvegarder les clés (affichées à l'écran)
# - UNSEAL_KEY → Gestionnaire de mots de passe
# - ROOT_TOKEN → Gestionnaire de mots de passe
# - KUBECONFIG_B64 → GitHub Secrets

# 3. Supprimer les fichiers temporaires
rm vault-keys.json github-kubeconfig-secret.txt

# 4. Vérifier le déploiement
sudo kubectl -n cinegest get pods
sudo kubectl -n cinegest get secret cinegest-back-secret

# 5. Push code → CI/CD déploie automatiquement
git add .
git commit -m "chore: infrastructure setup"
git push origin main
```

---

### Installation manuelle (étape par étape)

```bash
# 1. Installer Vault + ESO
./k8s/vault/install.sh

# 2. Initialiser Vault manuellement
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator init -key-shares=1 -key-threshold=1
# Sauvegarder UNSEAL_KEY et ROOT_TOKEN

# 3. Unseal Vault
vault operator unseal <UNSEAL_KEY>
exit

# 4. Configurer Vault
./k8s/vault/configure-vault.sh <ROOT_TOKEN>

# 5. Mettre à jour les secrets
./update-vault-secrets.sh <ROOT_TOKEN>

# 6. Déployer les manifests K8s
sudo kubectl apply -f k8s/namespace.yaml
sudo kubectl apply -f k8s/vault/rbac.yaml
sudo kubectl apply -f k8s/vault/serviceaccount.yaml
sudo kubectl apply -f k8s/secretstore.yaml
sudo kubectl apply -f k8s/externalsecret.yaml

# 7. Vérifier la synchronisation
sudo kubectl -n cinegest get externalsecret cinegest-back-vault
```

---

### Mise à jour des secrets en production

```bash
# 1. Mettre à jour dans Vault
./update-vault-secrets.sh <ROOT_TOKEN>

# 2. Vérifier dans Vault
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>
vault kv get secret/cinegest/app

# 3. Forcer la resynchronisation K8s
sudo kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync="$(date +%s)" --overwrite

# 4. Vérifier le secret K8s
sudo kubectl -n cinegest get secret cinegest-back-secret -o yaml

# 5. Redémarrer l'application
sudo kubectl -n cinegest rollout restart deployment/cinegest-back

# 6. Suivre le rolling update
sudo kubectl -n cinegest rollout status deployment/cinegest-back
```

---

### Récupération après redémarrage serveur

```bash
# 1. Vérifier que Vault est démarré
sudo kubectl -n vault get pods

# 2. Unseal Vault (toujours nécessaire après redémarrage)
sudo kubectl -n vault exec deploy/vault -- vault operator unseal <UNSEAL_KEY>

# 3. Vérifier la synchronisation
sudo kubectl -n cinegest get externalsecret cinegest-back-vault

# 4. Si besoin, forcer refresh
sudo kubectl -n cinegest delete pod -l app=cinegest-back
```

---

### Debug et troubleshooting

```bash
# Vérifier Vault
sudo kubectl -n vault logs -f deploy/vault
sudo kubectl -n vault exec deploy/vault -- vault status

# Vérifier External Secrets
sudo kubectl -n external-secrets logs -f deploy/external-secrets
sudo kubectl -n cinegest describe secretstore vault-backend
sudo kubectl -n cinegest describe externalsecret cinegest-back-vault

# Vérifier l'application
sudo kubectl -n cinegest get pods
sudo kubectl -n cinegest logs -f deploy/cinegest-back
sudo kubectl -n cinegest describe deployment cinegest-back

# Events
sudo kubectl -n cinegest get events --sort-by='.lastTimestamp'
```

---

## 🔒 Sécurité et bonnes pratiques

### Gestion des secrets

✅ **DO:**
- Sauvegarder UNSEAL_KEY et ROOT_TOKEN dans un gestionnaire de mots de passe sécurisé
- Supprimer immédiatement les fichiers temporaires (`vault-keys.json`, etc.)
- Utiliser des mots de passe forts pour DB, Stripe, etc.
- Limiter l'accès au cluster K8s

❌ **DON'T:**
- Ne jamais commiter les secrets dans Git
- Ne jamais partager le ROOT_TOKEN par email/chat
- Ne jamais laisser les fichiers temporaires sur le serveur
- Ne jamais loguer les secrets en clair

### Rotation des secrets

```bash
# 1. Mettre à jour dans Vault
./update-vault-secrets.sh <ROOT_TOKEN>

# 2. Forcer resync
sudo kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync="$(date +%s)" --overwrite

# 3. Rolling restart
sudo kubectl -n cinegest rollout restart deployment/cinegest-back
```

---

## 📚 Ressources

### Scripts

- [setup-infrastructure.sh](../setup-infrastructure.sh) - Installation complète
- [update-vault-secrets.sh](../update-vault-secrets.sh) - Mise à jour secrets
- [k8s/vault/install.sh](../k8s/vault/install.sh) - Installation Vault
- [k8s/vault/configure-vault.sh](../k8s/vault/configure-vault.sh) - Configuration Vault

### Documentation

- [VAULT-SETUP.md](VAULT-SETUP.md) - Guide Vault complet
- [VAULT-TROUBLESHOOTING.md](VAULT-TROUBLESHOOTING.md) - Dépannage
- [QUICKSTART-PRODUCTION.md](QUICKSTART-PRODUCTION.md) - Démarrage rapide
- [ARCHITECTURE.md](ARCHITECTURE.md) - Architecture du projet

---

**Dernière mise à jour:** Avril 2026
