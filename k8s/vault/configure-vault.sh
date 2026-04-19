#!/bin/bash
set -euo pipefail

if [ -z "${1:-}" ]; then
  echo "Usage: $0 <ROOT_TOKEN>"
  exit 1
fi

ROOT_TOKEN="$1"

echo "🔧 Configuration de Vault..."

# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &
PF_PID=$!
sleep 3

export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Activer KV v2
echo "Activation du secret engine KV v2..."
vault secrets enable -path=secret kv-v2 || echo "KV déjà activé"

# Créer la policy
echo "Création de la policy cinegest-policy..."
cat > /tmp/cinegest-policy.hcl <<'EOF'
path "secret/data/cinegest/*" {
  capabilities = ["read"]
}
path "secret/metadata/cinegest/*" {
  capabilities = ["read", "list"]
}
EOF

vault policy write cinegest-policy /tmp/cinegest-policy.hcl

# Configurer auth Kubernetes
echo "Configuration de l'auth Kubernetes..."
vault auth enable kubernetes || echo "Auth Kubernetes déjà activé"

# Récupérer les infos du cluster
SA_NAME="vault-auth"
SA_NAMESPACE="cinegest"

# Créer le service account si nécessaire
kubectl -n cinegest create sa vault-auth --dry-run=client -o yaml | kubectl apply -f -

# Attendre que le token soit créé
sleep 2

# Récupérer le token JWT
SA_JWT_TOKEN=$(kubectl -n cinegest create token vault-auth --duration=8760h)
K8S_HOST=$(kubectl config view --raw --minify --flatten -o jsonpath='{.clusters[0].cluster.server}')
K8S_CA_CERT=$(kubectl config view --raw --minify --flatten -o jsonpath='{.clusters[0].cluster.certificate-authority-data}' | base64 -d)

echo "$K8S_CA_CERT" > /tmp/ca.crt

vault write auth/kubernetes/config \
  token_reviewer_jwt="$SA_JWT_TOKEN" \
  kubernetes_host="$K8S_HOST" \
  kubernetes_ca_cert=@/tmp/ca.crt

# Créer le role
echo "Création du role cinegest-app..."
vault write auth/kubernetes/role/cinegest-app \
  bound_service_account_names=vault-auth \
  bound_service_account_namespaces=cinegest \
  policies=cinegest-policy \
  ttl=24h

# Insérer les secrets
echo "Insertion des secrets pour cinegest..."
vault kv put secret/cinegest/app \
  APP_NAME="CineGest" \
  APP_ENV="production" \
  APP_KEY="base64:CHANGEME_GENERATE_WITH_php_artisan_key:generate" \
  APP_DEBUG="false" \
  APP_URL="https://api.cinegest.nicolasbarbey.fr" \
  APP_FRONTEND_URL="https://cinegest.nicolasbarbey.fr" \
  DB_CONNECTION="mysql" \
  DB_HOST="mysql" \
  DB_PORT="3306" \
  DB_DATABASE="cinegest" \
  DB_USERNAME="cinegest" \
  DB_PASSWORD="CHANGEME" \
  SESSION_DRIVER="database" \
  CACHE_STORE="database" \
  QUEUE_CONNECTION="database" \
  SANCTUM_STATEFUL_DOMAINS="cinegest.nicolasbarbey.fr" \
  MAIL_MAILER="smtp" \
  MAIL_HOST="in-v3.mailjet.com" \
  MAIL_PORT="587" \
  MAIL_USERNAME="CHANGEME" \
  MAIL_PASSWORD="CHANGEME" \
  MAIL_ENCRYPTION="tls" \
  MAIL_FROM_ADDRESS="no-reply@cinegest.nicolasbarbey.fr" \
  MAIL_FROM_NAME="CineGest" \
  MAILJET_APIKEY="CHANGEME" \
  MAILJET_APISECRET="CHANGEME" \
  STRIPE_KEY="CHANGEME" \
  STRIPE_SECRET="CHANGEME" \
  STRIPE_WEBHOOK_SECRET="CHANGEME"

echo ""
echo "✅ Configuration de Vault terminée !"
echo ""
echo "Vérifier les secrets :"
echo "  vault kv get secret/cinegest/app"
echo ""
echo "Déployer External Secrets :"
echo "  kubectl apply -f k8s/vault/serviceaccount.yaml"
echo "  kubectl apply -f k8s/secretstore.yaml"
echo "  kubectl apply -f k8s/externalsecret.yaml"
echo ""

# Arrêter le port-forward
kill $PF_PID
