# 🚀 Guide de démarrage rapide - Déploiement Production

Ce guide vous permet de déployer CineGest en production sur Kubernetes avec Vault en moins de 30 minutes.

## ✅ Prérequis

- [ ] Cluster Kubernetes (K3s) accessible
- [ ] `kubectl` configuré et fonctionnel
- [ ] `helm` installé (version 3+)
- [ ] `vault` CLI installé localement
- [ ] Accès GitHub au repository
- [ ] Domaine configuré (DNS pointant vers le cluster)

## 📋 Checklist de déploiement

### Phase 1: Configuration initiale (une seule fois)

#### 1️⃣ Configuration GitHub

```bash
# Créer le secret KUBECONFIG_B64
cat ~/.kube/config | base64 -w0

# Aller sur GitHub:
# Settings → Secrets and variables → Actions → New repository secret
# Name: KUBECONFIG_B64
# Value: <coller le résultat ci-dessus>
```

- [ ] Secret `KUBECONFIG_B64` créé dans GitHub

#### 2️⃣ Installation Vault

```bash
cd /home/nicolas/cinegest/cinegest-back

# Installer Vault et External Secrets Operator
./k8s/vault/install.sh
```

**Attendez que l'installation se termine** (~3-5 minutes)

- [ ] Vault déployé
- [ ] External Secrets Operator installé

#### 3️⃣ Initialisation Vault

```bash
# Se connecter au pod Vault
kubectl -n vault exec -it deploy/vault -- sh

# Dans le pod
export VAULT_ADDR=http://localhost:8200

# Initialiser Vault
vault operator init -key-shares=1 -key-threshold=1
```

**⚠️ IMPORTANT: Sauvegarder ces valeurs dans un endroit sûr !**

```
Unseal Key 1: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Initial Root Token: s.xxxxxxxxxxxxxxxxxxxxxxxx
```

```bash
# Unseal Vault avec la clé
vault operator unseal <UNSEAL_KEY>

# Vérifier le statut
vault status
# Sealed doit être "false"

# Quitter le pod
exit
```

- [ ] Vault initialisé
- [ ] Unseal Key sauvegardée en lieu sûr
- [ ] Root Token sauvegardé en lieu sûr
- [ ] Vault unseal

#### 4️⃣ Configuration Vault

```bash
# Configurer Vault avec le Root Token
./k8s/vault/configure-vault.sh <ROOT_TOKEN>
```

Ce script va :
- ✅ Activer le secret engine KV v2
- ✅ Créer la policy pour l'application
- ✅ Configurer l'authentification Kubernetes
- ✅ Créer le role pour le ServiceAccount
- ✅ Insérer les secrets de l'application

- [ ] Vault configuré

#### 5️⃣ Mise à jour des secrets

```bash
# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# Générer APP_KEY Laravel
APP_KEY=$(php artisan key:generate --show)

# Mettre à jour les secrets avec les vraies valeurs
vault kv patch secret/cinegest/app \
  APP_KEY="$APP_KEY" \
  DB_PASSWORD="VOTRE_PASSWORD_MYSQL" \
  DB_HOST="VOTRE_HOST_MYSQL" \
  MAIL_USERNAME="VOTRE_MAILJET_USER" \
  MAIL_PASSWORD="VOTRE_MAILJET_PASS" \
  MAILJET_APIKEY="VOTRE_API_KEY" \
  MAILJET_APISECRET="VOTRE_API_SECRET" \
  STRIPE_KEY="pk_live_..." \
  STRIPE_SECRET="sk_live_..." \
  STRIPE_WEBHOOK_SECRET="whsec_..."

# Vérifier les secrets
vault kv get secret/cinegest/app

# Arrêter le port-forward
killall kubectl
```

- [ ] APP_KEY généré
- [ ] Tous les secrets mis à jour dans Vault
- [ ] Secrets vérifiés

### Phase 2: Premier déploiement

#### 6️⃣ Déploiement via pipeline

```bash
# Vérifier que tout est commit
git status

# Pusher sur main pour déclencher la pipeline
git push origin main

# Ou si déjà à jour, forcer un déploiement
git commit --allow-empty -m "chore: trigger deployment"
git push origin main
```

**Surveiller la pipeline sur GitHub:**
- Actions → Deploy cinegest-back to K3s → Voir les logs

- [ ] Pipeline déclenchée
- [ ] Job Tests: ✅
- [ ] Job Build and Push: ✅
- [ ] Job Deploy: ✅

#### 7️⃣ Vérification du déploiement

```bash
# Vérifier les pods
kubectl -n cinegest get pods
# Devrait afficher 2 pods "Running"

# Vérifier le déploiement
kubectl -n cinegest get deployment cinegest-back
# READY devrait être "2/2"

# Vérifier l'ingress
kubectl -n cinegest get ingress
# ADDRESS devrait être rempli

# Vérifier les secrets synchronisés
kubectl -n cinegest get externalsecret cinegest-back-vault
# STATUS devrait être "SecretSynced"

# Voir les logs
kubectl -n cinegest logs -f deployment/cinegest-back
```

- [ ] 2 pods running
- [ ] Deployment ready
- [ ] Ingress configuré
- [ ] Secrets synchronisés
- [ ] Logs sans erreur

#### 8️⃣ Test de l'application

```bash
# Tester l'API
curl https://api.cinegest.nicolasbarbey.fr/health

# Devrait retourner: {"status": "ok"}
```

- [ ] API accessible
- [ ] Health check OK
- [ ] SSL/TLS fonctionnel

---

## 🎉 Déploiement terminé !

Votre application est maintenant en production avec :

- ✅ CI/CD automatisé via GitHub Actions
- ✅ Secrets gérés par Vault
- ✅ Zero-downtime deployments
- ✅ Rollback automatique en cas d'erreur
- ✅ SSL/TLS automatique (Let's Encrypt)
- ✅ Rate limiting et security headers
- ✅ Health checks et monitoring
- ✅ 2 replicas pour haute disponibilité

---

## 🔄 Workflows de déploiement

### Déploiement automatique (recommandé)

```bash
# Développer normalement
git add .
git commit -m "feat: nouvelle fonctionnalité"

# Push → déclenche automatiquement la pipeline
git push origin main

# Pipeline fait:
# 1. Tests automatiques
# 2. Build Docker
# 3. Deploy K8s
# 4. Migrations
# 5. Health checks
```

### Déploiement manuel (si besoin)

Via GitHub:
1. **Actions** → **Deploy to Production**
2. **Run workflow**
3. Optionnel: Skip tests (pas recommandé)
4. **Run workflow**

---

## 🔧 Gestion quotidienne

### Mettre à jour un secret

```bash
# 1. Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=<ROOT_TOKEN>

# 2. Mettre à jour
vault kv patch secret/cinegest/app \
  DB_PASSWORD="nouveau_password"

# 3. Forcer sync (optionnel, sinon auto dans 15 min)
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite

# 4. Redémarrer l'app
kubectl -n cinegest rollout restart deployment/cinegest-back
```

### Voir les logs

```bash
# Logs en temps réel
kubectl -n cinegest logs -f deployment/cinegest-back

# Logs d'un pod spécifique
kubectl -n cinegest logs <pod-name>

# Logs de migration
kubectl -n cinegest logs job/cinegest-back-migrate
```

### Scaler l'application

```bash
# Augmenter à 5 replicas
kubectl -n cinegest scale deployment cinegest-back --replicas=5

# Vérifier
kubectl -n cinegest get pods
```

### Rollback manuel

```bash
# Rollback au déploiement précédent
kubectl -n cinegest rollout undo deployment/cinegest-back

# Rollback à une révision spécifique
kubectl -n cinegest rollout history deployment/cinegest-back
kubectl -n cinegest rollout undo deployment/cinegest-back --to-revision=3
```

### Accéder à un pod (debug)

```bash
# Shell dans le pod
kubectl -n cinegest exec -it deployment/cinegest-back -- bash

# Une fois dans le pod
php artisan tinker
php artisan route:list
php artisan config:cache
```

---

## 🆘 Troubleshooting

### Vault sealed après redémarrage

```bash
kubectl -n vault exec -it deploy/vault -- sh
export VAULT_ADDR=http://localhost:8200
vault operator unseal <UNSEAL_KEY>
exit
```

### Secrets non synchronisés

```bash
# Vérifier ESO
kubectl -n external-secrets get pods

# Voir les logs ESO
kubectl -n external-secrets logs -f deploy/external-secrets

# Vérifier ExternalSecret
kubectl -n cinegest describe externalsecret cinegest-back-vault

# Forcer la sync
kubectl -n cinegest annotate externalsecret cinegest-back-vault \
  force-sync=$(date +%s) --overwrite
```

### Pod en CrashLoopBackOff

```bash
# Voir les logs
kubectl -n cinegest logs <pod-name>

# Voir les events
kubectl -n cinegest describe pod <pod-name>

# Vérifier le secret
kubectl -n cinegest get secret cinegest-back-secret
```

### Pipeline échoue sur les tests

```bash
# Lancer les tests localement
php artisan test

# Vérifier les dépendances
composer install
```

---

## 📚 Documentation complète

| Document | Description |
|----------|-------------|
| [Workflows CI/CD](.github/README.md) | Guide des workflows GitHub Actions |
| [Pipeline + Vault](.github/PIPELINE-VAULT.md) | Configuration pipeline avec Vault |
| [Vault Quickstart](k8s/VAULT-QUICKSTART.md) | Guide rapide Vault |
| [Vault Complet](k8s/vault/README.md) | Documentation détaillée Vault |
| [Architecture](docs/ARCHITECTURE.md) | Diagrammes d'architecture complète |
| [Manifests K8s](k8s/) | Configuration Kubernetes |

---

## 📞 Support

En cas de problème:

1. Consulter les logs: `kubectl -n cinegest logs -f deployment/cinegest-back`
2. Vérifier Vault: `kubectl -n vault exec deploy/vault -- vault status`
3. Vérifier ESO: `kubectl -n external-secrets get pods`
4. Voir la documentation détaillée ci-dessus

---

## 🎯 Prochaines étapes recommandées

- [ ] Configurer les backups Vault
- [ ] Mettre en place l'auto-scaling (HPA)
- [ ] Configurer Vault Auto-unseal
- [ ] Activer l'audit logging Vault
- [ ] Configurer les alertes monitoring
- [ ] Mettre en place la rotation automatique des secrets
- [ ] Configurer un environnement de staging

---

**Félicitations ! Votre application CineGest est maintenant en production ! 🎉**
