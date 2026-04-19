# 📚 Documentation CineGest Backend

Bienvenue dans la documentation complète du projet CineGest Backend.

## 🚀 Guides de démarrage

### Développement local
- [Installation locale](../README.md#-installation) - Configuration Docker Compose pour développement

### Production Kubernetes
- **[Guide rapide de déploiement](QUICKSTART-PRODUCTION.md) ⭐** - Déployer en production en 30 minutes
- [Architecture complète](ARCHITECTURE.md) - Diagrammes et flux détaillés

## ☸️ Kubernetes & Infrastructure

### Configuration Vault
- [Guide rapide Vault](../k8s/VAULT-QUICKSTART.md) - Installation et configuration Vault
- [Documentation complète Vault](VAULT-SETUP.md) - Guide détaillé avec troubleshooting
- **[Dépannage Vault](VAULT-TROUBLESHOOTING.md) 🔧** - Résolution des problèmes courants
- **[Guide des Scripts](SCRIPTS-GUIDE.md) 📜** - Documentation complète des scripts d'installation

### Sécurité
- **[Guide de Sécurité](SECURITY.md) 🔐** - Bonnes pratiques et procédures d'urgence
- Gestion des secrets - Vault, rotation, classification
- Configuration Kubernetes - Security context, network policies
- Monitoring et alertes - Health checks, logs, métriques

### CI/CD
- [Workflows GitHub Actions](../.github/README.md) - Guide des workflows disponibles
- Pipeline automatique - Déploiement automatique sur push main
- Déploiement manuel - Via GitHub Actions UI
- Opérations Vault - Gestion Vault sans redéploiement

### Manifests Kubernetes
Tous les fichiers dans [`k8s/`](../k8s/):
- `deployment.yaml` - Configuration du déploiement (2 replicas, security, health checks)
- `service.yaml` - Service ClusterIP
- `ingress.yaml` - Configuration Traefik avec TLS
- `middleware.yaml` - Rate limiting et security headers
- `migrate-job.yaml` - Job de migration DB

Scripts racine:
- `setup-infrastructure.sh` - Installation complète (tout-en-un)
- `update-vault-secrets.sh` - Mise à jour interactive des secrets

📖 **Documentation complète:** [Guide des Scripts](SCRIPTS-GUIDE.md)
- `secretstore.yaml` - Connexion à Vault
- `externalsecret.yaml` - Synchronisation secrets Vault → K8s

### Scripts d'installation
Dans [`k8s/vault/`](../k8s/vault/):
- `install.sh` - Installation Vault + ESO
- `configure-vault.sh` - Configuration automatique de Vault

## 🏗️ Architecture

### Architecture applicative
Voir [README principal](../README.md#-architecture) pour:
- Architecture Laravel classique
- Clean Architecture (DDD)
- Comparaison des approches
- Structure du projet

### Architecture infrastructure
Voir [ARCHITECTURE.md](ARCHITECTURE.md) pour:
- Vue d'ensemble infrastructure complète
- Flux de déploiement CI/CD
- Gestion des secrets (Vault)
- Monitoring et health checks
- Stratégie RollingUpdate
- Sécurité multi-layers
- Scalabilité

## 🔐 Sécurité

### Gestion des secrets
- **Vault** - Secret management centralisé
- **External Secrets Operator** - Synchronisation automatique
- **Kubernetes Secrets** - Secrets injectés dans les pods
- Pas de secrets dans Git ou en clair

### Sécurité Kubernetes
- SecurityContext (runAsNonRoot, capabilities drop)
- Network policies
- TLS/SSL automatique (Let's Encrypt)
- Rate limiting (100 req/min)
- Security headers HTTP

## 📊 Workflows

### Développement → Production

```
Local Dev → Git Push → Pipeline CI/CD → Production K8s
    │          │            │               │
    │          │            ├─ Tests        ├─ 2 replicas
    │          │            ├─ Build        ├─ Zero downtime
    │          │            ├─ Deploy       ├─ Auto rollback
    │          │            └─ Migrations   └─ Health checks
    └─ Docker Compose
```

### Gestion des secrets

```
Admin update Vault → ESO sync → K8s Secret → Pod restart
```

## 🛠️ Opérations

### Commandes essentielles

```bash
# Déploiement automatique
git push origin main

# Déploiement manuel via GitHub Actions
# Actions → Deploy to Production → Run workflow

# Monitoring
kubectl -n cinegest get pods  # Status pods
kubectl -n cinegest logs -f deployment/cinegest-back  # Logs

# Secrets
vault kv get secret/cinegest/app  # Voir secrets
vault kv patch secret/cinegest/app KEY="value"  # MAJ secret
kubectl -n cinegest rollout restart deployment/cinegest-back  # Reload

# Scaling
kubectl -n cinegest scale deployment cinegest-back --replicas=5

# Rollback
kubectl -n cinegest rollout undo deployment/cinegest-back
```

## 🆘 Troubleshooting

### Problèmes courants

| Problème | Solution |
|----------|----------|
| Vault sealed | `kubectl -n vault exec -it deploy/vault -- vault operator unseal <KEY>` |
| Secrets non synchro | Vérifier ESO logs, forcer sync avec annotation |
| Pod CrashLoop | Vérifier logs et events du pod |
| Pipeline échoue | Vérifier tests localement, vérifier KUBECONFIG_B64 |

Voir [QUICKSTART-PRODUCTION.md](QUICKSTART-PRODUCTION.md#-troubleshooting) pour plus de détails.

## 📖 Index des documents

### Documentation projet
- [`README.md`](../README.md) - Vue d'ensemble du projet
- [`ARCHITECTURE.md`](ARCHITECTURE.md) - Architecture complète avec diagrammes
- [`QUICKSTART-PRODUCTION.md`](QUICKSTART-PRODUCTION.md) - Guide de déploiement rapide

### CI/CD
- [`.github/README.md`](../.github/README.md) - Workflows GitHub Actions
- [`.github/PIPELINE-VAULT.md`](../.github/PIPELINE-VAULT.md) - Configuration pipeline
- [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml) - Pipeline principale
- [`.github/workflows/deploy-manual.yml`](../.github/workflows/deploy-manual.yml) - Deploy manuel
- [`.github/workflows/vault-ops.yml`](../.github/workflows/vault-ops.yml) - Opérations Vault

### Kubernetes
- [`k8s/VAULT-QUICKSTART.md`](../k8s/VAULT-QUICKSTART.md) - Guide rapide Vault
- [`k8s/vault/README.md`](../k8s/vault/README.md) - Documentation complète Vault
- Tous les manifests dans [`k8s/`](../k8s/)

## 🎯 Par cas d'usage

### Je veux...

**...déployer pour la première fois**
→ [QUICKSTART-PRODUCTION.md](QUICKSTART-PRODUCTION.md)

**...comprendre l'architecture**
→ [ARCHITECTURE.md](ARCHITECTURE.md)

**...configurer Vault**
→ [k8s/VAULT-QUICKSTART.md](../k8s/VAULT-QUICKSTART.md)

**...comprendre la pipeline CI/CD**
→ [.github/README.md](../.github/README.md)

**...mettre à jour un secret**
→ [QUICKSTART-PRODUCTION.md - Gestion quotidienne](QUICKSTART-PRODUCTION.md#-gestion-quotidienne)

**...débugger un problème**
→ [QUICKSTART-PRODUCTION.md - Troubleshooting](QUICKSTART-PRODUCTION.md#-troubleshooting)

**...développer localement**
→ [README.md - Installation](../README.md#-installation)

**...comprendre le code (Clean Architecture)**
→ [README.md - Architecture](../README.md#-architecture)

## 🔄 Mise à jour de la documentation

Cette documentation est maintenue à jour avec chaque modification de l'infrastructure.

**Dernière mise à jour**: 14 avril 2026

**Version**: 1.0.0

---

**Questions ou problèmes avec la documentation ?**

Ouvrir une issue sur GitHub ou consulter les logs:
```bash
kubectl -n cinegest logs -f deployment/cinegest-back
```
