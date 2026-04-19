# 📝 Changelog Infrastructure

Historique des modifications de l'infrastructure et des scripts CineGest Backend.

---

## [1.1.0] - 2026-04-19

### 🎯 Améliorations majeures

#### Documentation professionnelle
- ✅ Ajout de [VAULT-TROUBLESHOOTING.md](docs/VAULT-TROUBLESHOOTING.md) - Guide complet de dépannage
- ✅ Ajout de [SCRIPTS-GUIDE.md](docs/SCRIPTS-GUIDE.md) - Documentation des scripts d'installation
- ✅ Ajout de [SECURITY.md](docs/SECURITY.md) - Guide de sécurité et bonnes pratiques
- ✅ Mise à jour de [docs/README.md](docs/README.md) avec références aux nouveaux guides

#### Scripts d'installation

**`setup-infrastructure.sh`**
- 🔧 Correction: Ajout de l'application du RBAC Vault (ligne 302)
  - Avant: RBAC non appliqué → SecretStore non fonctionnel
  - Après: `kubectl apply -f k8s/vault/rbac.yaml` ajouté

**`update-vault-secrets.sh`**
- 🔧 Correction: Problème de permissions fichiers temporaires
  - Avant: Utilisation de `/tmp/current_secrets.json` (permission denied avec sudo)
  - Après: Utilisation de `mktemp` pour fichier temporaire sécurisé
  - Ajout: Cleanup automatique du fichier temporaire via trap

### 🐛 Correctifs

- ✅ Résolution du problème "SecretStore is not ready"
  - Cause: RBAC non appliqué dans le workflow d'installation
  - Solution: Ajout explicite de `k8s/vault/rbac.yaml` dans `setup-infrastructure.sh`

- ✅ Résolution du problème "Permission denied" dans update-vault-secrets.sh
  - Cause: Sudo ne pouvait pas écrire dans `/tmp/current_secrets.json`
  - Solution: Utilisation de mktemp pour générer un fichier avec bonnes permissions

### 📚 Documentation ajoutée

#### VAULT-TROUBLESHOOTING.md
- Diagnostic rapide (vérifications essentielles)
- 6 problèmes courants avec solutions détaillées
- Commandes de vérification complètes
- Procédures de résolution pas à pas
- Guide de collecte de logs pour support
- Checklist de vérification pré-support

#### SCRIPTS-GUIDE.md
- Vue d'ensemble des scripts (matrice de dépendances)
- Documentation détaillée de 4 scripts principaux
- 5 workflows typiques (installation, mise à jour, debug, etc.)
- Exemples d'utilisation concrets
- Bonnes pratiques de sécurité
- Durées estimées d'exécution

#### SECURITY.md
- Principes de sécurité (Defense in Depth)
- Gestion complète des secrets (classification, rotation)
- Configuration Kubernetes sécurisée
- Réseau et accès (TLS, Rate Limiting, CORS)
- Monitoring et alertes
- Procédures d'urgence (incidents, fuites)
- Checklist de sécurité complète

### 🔄 Fichiers modifiés

```
Modified:
  setup-infrastructure.sh (L302: ajout RBAC)
  update-vault-secrets.sh (L40-75: mktemp + cleanup)
  docs/README.md (références nouveaux guides)

Added:
  docs/VAULT-TROUBLESHOOTING.md
  docs/SCRIPTS-GUIDE.md
  docs/SECURITY.md
  CHANGELOG-INFRA.md (ce fichier)
```

---

## [1.0.0] - 2026-04-18

### 🎉 Version initiale

#### Infrastructure Kubernetes
- ✅ Configuration K3s complète
- ✅ Vault + External Secrets Operator
- ✅ Traefik Ingress avec TLS automatique
- ✅ Rate limiting et security headers
- ✅ HPA (Horizontal Pod Autoscaling)

#### Scripts d'installation
- ✅ `k8s/vault/install.sh` - Installation Vault + ESO
- ✅ `k8s/vault/configure-vault.sh` - Configuration Vault automatique
- ✅ `setup-infrastructure.sh` - Installation complète orchestrée
- ✅ `update-vault-secrets.sh` - Interface interactive secrets

#### CI/CD GitHub Actions
- ✅ Pipeline automatique sur push main
- ✅ Tests + Build + Deploy
- ✅ Migration automatique de la DB
- ✅ Rollback automatique si échec

#### Sécurité
- ✅ Pods en non-root (UID 33)
- ✅ Capabilities supprimées (drop: ALL)
- ✅ Resource limits configurés
- ✅ Secrets managés via Vault
- ✅ TLS/HTTPS automatique

#### Documentation
- ✅ README.md principal
- ✅ docs/QUICKSTART-PRODUCTION.md
- ✅ docs/ARCHITECTURE.md
- ✅ docs/VAULT-SETUP.md
- ✅ k8s/VAULT-QUICKSTART.md

---

## 📊 Statistiques

### Infrastructure
- **Pods:** 2 replicas (cinegest-back)
- **Namespaces:** 3 (cinegest, vault, external-secrets)
- **Services:** 2 (app, vault)
- **Ingress:** 1 (Traefik + TLS)
- **Secrets:** 1 (sync depuis Vault)

### Scripts
- **Installation:** 3 scripts (install, configure, setup)
- **Maintenance:** 1 script (update-secrets)
- **Documentation:** 7 fichiers MD

### Sécurité
- **Secrets gérés:** 27 variables
- **Vault policies:** 1 (cinegest-policy)
- **Kubernetes RBAC:** 1 (vault-tokenreview-binding)
- **TLS:** Automatique (Let's Encrypt)

---

## 🔮 Roadmap

### v1.2.0 (À venir)
- [ ] Network Policies Kubernetes
- [ ] PodSecurityPolicy/PodSecurityStandards
- [ ] Monitoring (Prometheus + Grafana)
- [ ] Backup automatique Vault
- [ ] Health check avancé (DB, Redis, etc.)

### v1.3.0 (Futur)
- [ ] Multi-region deployment
- [ ] Blue/Green deployments
- [ ] Canary deployments
- [ ] Service Mesh (Istio/Linkerd)
- [ ] Distributed tracing

---

## 📝 Notes de version

### Comment lire ce changelog

Format basé sur [Keep a Changelog](https://keepachangelog.com/):

- **Added** - Nouvelles fonctionnalités
- **Changed** - Modifications de fonctionnalités existantes
- **Deprecated** - Fonctionnalités bientôt supprimées
- **Removed** - Fonctionnalités supprimées
- **Fixed** - Corrections de bugs
- **Security** - Correctifs de sécurité

### Versioning

Format: `MAJOR.MINOR.PATCH`

- **MAJOR** - Changements incompatibles (breaking changes)
- **MINOR** - Nouvelles fonctionnalités (backward compatible)
- **PATCH** - Corrections de bugs

---

## 🤝 Contributions

Pour proposer des améliorations:

1. Créer une branche: `git checkout -b feature/amelioration-xxx`
2. Documenter les changements dans ce CHANGELOG
3. Tester complètement sur staging
4. Créer une Pull Request

---

**Maintenu par:** DevOps Team  
**Dernière mise à jour:** 19 avril 2026
