# Configuration Vault pour plusieurs projets

Ce guide explique comment utiliser le même Vault pour plusieurs projets.

## Structure des secrets Vault

```
secret/
├── cinegest/
│   └── app          # Secrets cinegest-back
├── projet2/
│   └── app          # Secrets projet2
└── projet3/
    └── app          # Secrets projet3
```

## Étapes pour ajouter un nouveau projet

### 1. Créer les fichiers Kubernetes

Dans le nouveau projet, créez `k8s/secretstore.yaml`:

```yaml
apiVersion: external-secrets.io/v1
kind: SecretStore
metadata:
  name: vault-backend
  namespace: mon-projet  # ← Votre namespace
spec:
  provider:
    vault:
      server: "http://vault.vault.svc.cluster.local:8200"
      path: "secret"
      version: "v2"
      auth:
        kubernetes:
          mountPath: "kubernetes"
          role: "external-secrets"
          serviceAccountRef:
            name: external-secrets-sa
```

Créez `k8s/externalsecret.yaml`:

```yaml
apiVersion: external-secrets.io/v1
kind: ExternalSecret
metadata:
  name: mon-projet-vault
  namespace: mon-projet
spec:
  refreshInterval: 15m
  secretStoreRef:
    name: vault-backend
    kind: SecretStore
  target:
    name: mon-projet-secret
    creationPolicy: Owner
  dataFrom:
    - extract:
        key: mon-projet/app  # ← Path dans Vault
```

Créez `k8s/serviceaccount.yaml`:

```yaml
apiVersion: v1
kind: ServiceAccount
metadata:
  name: external-secrets-sa
  namespace: mon-projet
```

### 2. Mettre à jour la policy Vault

Connectez-vous à Vault et ajoutez l'accès au nouveau path:

```bash
# Port-forward vers Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

# Configurer l'environnement
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="hvs.xxx..."

# Mettre à jour la policy
vault policy write external-secrets-policy - <<EOF
path "secret/data/cinegest/*" {
  capabilities = ["read"]
}
path "secret/data/mon-projet/*" {
  capabilities = ["read"]
}
path "secret/metadata/*" {
  capabilities = ["list"]
}
EOF
```

### 3. Ajouter les secrets dans Vault

```bash
vault kv put secret/mon-projet/app \
  APP_NAME="Mon Projet" \
  APP_ENV="production" \
  APP_KEY="base64:votre-clé-générée" \
  APP_DEBUG="false" \
  APP_URL="https://mon-projet.example.com" \
  DB_CONNECTION="mysql" \
  DB_HOST="mysql-service" \
  DB_PORT="3306" \
  DB_DATABASE="mon_projet" \
  DB_USERNAME="user" \
  DB_PASSWORD="password"
  # Ajoutez tous vos secrets...
```

### 4. Déployer dans Kubernetes

```bash
# Créer le namespace
kubectl create namespace mon-projet

# Appliquer les ressources
kubectl apply -f k8s/serviceaccount.yaml
kubectl apply -f k8s/secretstore.yaml
kubectl apply -f k8s/externalsecret.yaml

# Vérifier la synchronisation
kubectl -n mon-projet get externalsecret
kubectl -n mon-projet get secret mon-projet-secret
```

### 5. Utiliser le secret dans votre deployment

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mon-projet
  namespace: mon-projet
spec:
  template:
    spec:
      containers:
        - name: app
          image: mon-image:latest
          envFrom:
            - secretRef:
                name: mon-projet-secret  # ← Le secret créé par ExternalSecret
```

## Script de gestion des secrets

Vous pouvez adapter le script `update-vault-secrets.sh` pour chaque projet:

```bash
#!/bin/bash
# update-vault-secrets-mon-projet.sh

# ... (copier la logique du script existant)

# Modifier la section vault kv put:
vault kv put secret/mon-projet/app \
  APP_NAME="$APP_NAME" \
  # ... vos variables
```

## Bonnes pratiques

1. **Isolation des secrets**: Chaque projet a son propre path dans Vault (`secret/projet/app`)
2. **Permissions minimales**: La policy ne donne accès qu'aux paths nécessaires
3. **Rotation des secrets**: Mettez à jour régulièrement les secrets sensibles
4. **Backup**: Sauvegardez régulièrement votre Vault
5. **Monitoring**: Surveillez les ExternalSecrets avec `kubectl get externalsecret -A`

## Dépannage

### ExternalSecret ne synchronise pas

```bash
# Vérifier le status
kubectl -n mon-projet describe externalsecret mon-projet-vault

# Vérifier les logs de l'operator
kubectl -n external-secrets-system logs -l app.kubernetes.io/name=external-secrets
```

### Vault inaccessible

```bash
# Vérifier que Vault est running
kubectl -n vault get pods

# Tester la connexion depuis un pod
kubectl -n mon-projet run -it --rm debug --image=alpine --restart=Never -- sh
apk add curl
curl http://vault.vault.svc.cluster.local:8200/v1/sys/health
```

### Permissions insuffisantes

```bash
# Vérifier la policy actuelle
vault policy read external-secrets-policy

# Vérifier les logs Vault
kubectl -n vault logs -l app.kubernetes.io/name=vault
```

## Commandes utiles

```bash
# Lister tous les secrets d'un projet
vault kv list secret/mon-projet/

# Voir un secret
vault kv get secret/mon-projet/app

# Mettre à jour un seul champ
vault kv patch secret/mon-projet/app DB_PASSWORD="nouveau-password"

# Voir tous les ExternalSecrets
kubectl get externalsecret -A

# Forcer une resynchronisation
kubectl -n mon-projet annotate externalsecret mon-projet-vault \
  force-sync=$(date +%s)
```
