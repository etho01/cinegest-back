#!/bin/bash
# Script de configuration complète - CineGest Backend
# Ce script configure Vault et l'infrastructure K8s pour que la pipeline CI/CD fonctionne

set -e  # Arrêt en cas d'erreur

echo "🚀 Configuration CineGest Backend - Infrastructure Kubernetes + Vault"
echo "======================================================================"
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Variables - À MODIFIER selon votre environnement
CLUSTER_NAME="cinegest-k3s"
NAMESPACE="cinegest"

echo -e "${YELLOW}📋 Vérification des prérequis...${NC}"
echo ""

# Vérifier kubectl
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl non trouvé. Installez-le d'abord.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ kubectl installé${NC}"

# Vérifier helm
if ! command -v helm &> /dev/null; then
    echo -e "${RED}❌ helm non trouvé. Installez-le d'abord.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ helm installé${NC}"

# Vérifier vault CLI
if ! command -v vault &> /dev/null; then
    echo -e "${RED}❌ vault CLI non trouvé. Installez-le d'abord.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ vault CLI installé${NC}"

# Vérifier connexion au cluster
if ! kubectl cluster-info &> /dev/null; then
    echo -e "${RED}❌ Impossible de se connecter au cluster Kubernetes${NC}"
    echo "Vérifiez votre kubeconfig: kubectl config view"
    exit 1
fi
echo -e "${GREEN}✅ Connexion au cluster OK${NC}"

echo ""
echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 1: Installation Vault + ESO${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

# Installation Vault et External Secrets Operator
./k8s/vault/install.sh

echo ""
echo -e "${GREEN}✅ Vault et ESO installés${NC}"
echo ""

echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 2: Initialisation de Vault${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

echo -e "${YELLOW}⚠️  IMPORTANT: Les clés suivantes seront affichées UNE SEULE FOIS !${NC}"
echo -e "${YELLOW}    Sauvegardez-les dans un endroit sûr (gestionnaire de mots de passe).${NC}"
echo ""
read -p "Appuyez sur ENTRÉE pour continuer..."

# Attendre que Vault soit prêt
echo "⏳ Attente que Vault soit prêt..."
kubectl -n vault wait --for=condition=ready pod -l app=vault --timeout=120s

# Initialiser Vault
echo ""
echo "🔐 Initialisation de Vault..."
VAULT_INIT=$(kubectl -n vault exec deploy/vault -- vault operator init -format=json -key-shares=1 -key-threshold=1)

UNSEAL_KEY=$(echo "$VAULT_INIT" | jq -r '.unseal_keys_b64[0]')
ROOT_TOKEN=$(echo "$VAULT_INIT" | jq -r '.root_token')

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}📝 SAUVEGARDEZ CES VALEURS IMMÉDIATEMENT !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}Unseal Key:${NC}  $UNSEAL_KEY"
echo -e "${YELLOW}Root Token:${NC}  $ROOT_TOKEN"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Sauvegarder dans un fichier temporaire (à supprimer après copie)
echo "$VAULT_INIT" > vault-keys.json
chmod 600 vault-keys.json
echo -e "${YELLOW}💾 Clés sauvegardées temporairement dans: vault-keys.json${NC}"
echo -e "${RED}⚠️  Supprimez ce fichier après avoir copié les clés dans un endroit sûr !${NC}"
echo ""

read -p "Avez-vous sauvegardé les clés ? (o/N): " confirm
if [[ ! "$confirm" =~ ^([oO][uU][iI]|[oO])$ ]]; then
    echo -e "${RED}❌ Sauvegardez les clés avant de continuer !${NC}"
    exit 1
fi

# Unseal Vault
echo ""
echo "🔓 Unseal de Vault..."
kubectl -n vault exec deploy/vault -- vault operator unseal "$UNSEAL_KEY" > /dev/null

# Vérifier le statut
kubectl -n vault exec deploy/vault -- vault status

echo ""
echo -e "${GREEN}✅ Vault initialisé et unseal${NC}"
echo ""

echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 3: Configuration de Vault${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

# Configurer Vault
./k8s/vault/configure-vault.sh "$ROOT_TOKEN"

echo ""
echo -e "${GREEN}✅ Vault configuré${NC}"
echo ""

echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 4: Configuration des secrets${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

echo "🔐 Configuration des secrets de l'application..."
echo ""

# Port-forward Vault en arrière-plan
kubectl -n vault port-forward svc/vault 8200:8200 &
PF_PID=$!
sleep 3

export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Générer APP_KEY Laravel
echo "🔑 Génération de la clé Laravel..."
if [ -f "artisan" ]; then
    APP_KEY=$(php artisan key:generate --show)
    echo -e "${GREEN}✅ APP_KEY générée: $APP_KEY${NC}"
else
    echo -e "${YELLOW}⚠️  Fichier artisan non trouvé, utilisez une clé par défaut${NC}"
    APP_KEY="base64:CHANGEME_GENERATE_WITH_php_artisan_key_generate"
fi

echo ""
echo -e "${YELLOW}📝 Configuration des secrets...${NC}"
echo ""
echo "Entrez les valeurs des secrets (ou appuyez sur ENTRÉE pour garder la valeur par défaut)"
echo ""

# Fonction pour demander un secret
ask_secret() {
    local key=$1
    local default=$2
    local prompt=$3
    
    if [ -n "$prompt" ]; then
        read -p "$prompt [$default]: " value
    else
        read -p "$key [$default]: " value
    fi
    
    echo "${value:-$default}"
}

# Base de données
echo -e "${YELLOW}=== Base de données ===${NC}"
DB_HOST=$(ask_secret "DB_HOST" "mysql" "Hôte MySQL")
DB_PORT=$(ask_secret "DB_PORT" "3306" "Port MySQL")
DB_DATABASE=$(ask_secret "DB_DATABASE" "cinegest" "Nom de la base")
DB_USERNAME=$(ask_secret "DB_USERNAME" "cinegest" "Utilisateur DB")
DB_PASSWORD=$(ask_secret "DB_PASSWORD" "CHANGEME" "Mot de passe DB")

echo ""
echo -e "${YELLOW}=== Email (Mailjet) ===${NC}"
MAIL_USERNAME=$(ask_secret "MAIL_USERNAME" "CHANGEME" "Mailjet API Key")
MAIL_PASSWORD=$(ask_secret "MAIL_PASSWORD" "CHANGEME" "Mailjet Secret Key")
MAILJET_APIKEY=$(ask_secret "MAILJET_APIKEY" "CHANGEME" "Mailjet API Key")
MAILJET_APISECRET=$(ask_secret "MAILJET_APISECRET" "CHANGEME" "Mailjet API Secret")

echo ""
echo -e "${YELLOW}=== Stripe ===${NC}"
STRIPE_KEY=$(ask_secret "STRIPE_KEY" "pk_test_CHANGEME" "Stripe Publishable Key")
STRIPE_SECRET=$(ask_secret "STRIPE_SECRET" "sk_test_CHANGEME" "Stripe Secret Key")
STRIPE_WEBHOOK_SECRET=$(ask_secret "STRIPE_WEBHOOK_SECRET" "whsec_CHANGEME" "Stripe Webhook Secret")

echo ""
echo "💾 Sauvegarde des secrets dans Vault..."

vault kv put secret/cinegest/app \
  APP_NAME="CineGest" \
  APP_ENV="production" \
  APP_KEY="$APP_KEY" \
  APP_DEBUG="false" \
  APP_URL="https://api.cinegest.nicolasbarbey.fr" \
  APP_FRONTEND_URL="https://cinegest.nicolasbarbey.fr" \
  DB_CONNECTION="mysql" \
  DB_HOST="$DB_HOST" \
  DB_PORT="$DB_PORT" \
  DB_DATABASE="$DB_DATABASE" \
  DB_USERNAME="$DB_USERNAME" \
  DB_PASSWORD="$DB_PASSWORD" \
  SESSION_DRIVER="database" \
  CACHE_STORE="database" \
  QUEUE_CONNECTION="database" \
  SANCTUM_STATEFUL_DOMAINS="cinegest.nicolasbarbey.fr" \
  MAIL_MAILER="smtp" \
  MAIL_HOST="in-v3.mailjet.com" \
  MAIL_PORT="587" \
  MAIL_USERNAME="$MAIL_USERNAME" \
  MAIL_PASSWORD="$MAIL_PASSWORD" \
  MAIL_ENCRYPTION="tls" \
  MAIL_FROM_ADDRESS="no-reply@cinegest.nicolasbarbey.fr" \
  MAIL_FROM_NAME="CineGest" \
  MAILJET_APIKEY="$MAILJET_APIKEY" \
  MAILJET_APISECRET="$MAILJET_APISECRET" \
  STRIPE_KEY="$STRIPE_KEY" \
  STRIPE_SECRET="$STRIPE_SECRET" \
  STRIPE_WEBHOOK_SECRET="$STRIPE_WEBHOOK_SECRET"

# Vérifier les secrets
echo ""
echo "🔍 Vérification des secrets..."
vault kv get secret/cinegest/app

# Arrêter le port-forward
kill $PF_PID 2>/dev/null || true

echo ""
echo -e "${GREEN}✅ Secrets configurés dans Vault${NC}"
echo ""

echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 5: Configuration GitHub${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

echo "🔑 Génération du secret KUBECONFIG pour GitHub Actions..."
KUBECONFIG_B64=$(cat ~/.kube/config | base64 -w0)

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}📝 SECRET GITHUB À CONFIGURER${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}Nom du secret:${NC} KUBECONFIG_B64"
echo ""
echo -e "${YELLOW}Valeur du secret (à copier):${NC}"
echo "$KUBECONFIG_B64"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "📋 Pour configurer dans GitHub:"
echo "   1. Aller sur: https://github.com/VOTRE_USERNAME/cinegest-back/settings/secrets/actions"
echo "   2. Cliquer sur 'New repository secret'"
echo "   3. Name: KUBECONFIG_B64"
echo "   4. Value: <coller la valeur ci-dessus>"
echo "   5. Cliquer 'Add secret'"
echo ""

# Sauvegarder dans un fichier
echo "$KUBECONFIG_B64" > github-kubeconfig-secret.txt
chmod 600 github-kubeconfig-secret.txt
echo -e "${YELLOW}💾 Secret sauvegardé dans: github-kubeconfig-secret.txt${NC}"
echo -e "${RED}⚠️  Supprimez ce fichier après configuration GitHub !${NC}"
echo ""

read -p "Avez-vous configuré le secret KUBECONFIG_B64 dans GitHub ? (o/N): " confirm
if [[ ! "$confirm" =~ ^([oO][uU][iI]|[oO])$ ]]; then
    echo -e "${YELLOW}⚠️  Configurez le secret GitHub avant de faire un commit${NC}"
fi

echo ""
echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}ÉTAPE 6: Déploiement initial${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""

echo "🚀 Déploiement des manifestes Kubernetes..."

# Créer les namespaces
kubectl apply -f k8s/namespace.yaml

# Déployer les manifestes Vault
kubectl apply -f k8s/vault/serviceaccount.yaml
kubectl apply -f k8s/secretstore.yaml
kubectl apply -f k8s/externalsecret.yaml

# Attendre la synchronisation des secrets
echo ""
echo "⏳ Attente de la synchronisation des secrets depuis Vault..."
for i in {1..60}; do
    STATUS=$(kubectl -n $NAMESPACE get externalsecret cinegest-back-vault -o jsonpath='{.status.conditions[?(@.type=="Ready")].status}' 2>/dev/null || echo "")
    
    if [ "$STATUS" = "True" ]; then
        echo -e "${GREEN}✅ Secrets synchronisés !${NC}"
        break
    fi
    
    if [ $i -eq 60 ]; then
        echo -e "${RED}❌ Timeout: secrets non synchronisés${NC}"
        echo "Vérifiez: kubectl -n $NAMESPACE describe externalsecret cinegest-back-vault"
        exit 1
    fi
    
    echo -n "."
    sleep 2
done

echo ""
echo -e "${GREEN}✅ Configuration terminée !${NC}"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 INSTALLATION COMPLÈTE${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "✅ Infrastructure configurée et prête"
echo "✅ Vault installé et configuré"
echo "✅ Secrets synchronisés"
echo "✅ GitHub Actions configuré"
echo ""
echo -e "${YELLOW}📋 Prochaines étapes:${NC}"
echo ""
echo "1. ${GREEN}Commit et push sur main:${NC}"
echo "   git add ."
echo "   git commit -m 'chore: configuration infrastructure'"
echo "   git push origin main"
echo ""
echo "2. ${GREEN}La pipeline GitHub Actions va:${NC}"
echo "   - Exécuter les tests"
echo "   - Builder l'image Docker"
echo "   - Déployer sur Kubernetes"
echo "   - Exécuter les migrations"
echo ""
echo "3. ${GREEN}Vérifier le déploiement:${NC}"
echo "   kubectl -n $NAMESPACE get pods"
echo "   kubectl -n $NAMESPACE logs -f deployment/cinegest-back"
echo ""
echo -e "${RED}⚠️  N'oubliez pas de supprimer les fichiers temporaires:${NC}"
echo "   rm vault-keys.json github-kubeconfig-secret.txt"
echo ""
echo -e "${YELLOW}📚 Documentation complète:${NC}"
echo "   docs/QUICKSTART-PRODUCTION.md"
echo "   .github/README.md"
echo ""
