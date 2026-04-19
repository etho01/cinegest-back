#!/bin/bash
# Script de mise à jour des secrets Vault - CineGest Backend

set -e

echo "🔐 Mise à jour des secrets Vault - CineGest"
echo "============================================"
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Vérifier que le ROOT_TOKEN est fourni
if [ -z "${1:-}" ]; then
  echo -e "${RED}❌ Usage: $0 <ROOT_TOKEN>${NC}"
  echo ""
  echo "Exemple:"
  echo "  $0 hvs.XXXXXXXXXXXXXXXXXXXXXX"
  exit 1
fi

ROOT_TOKEN="$1"

# Vérifier kubectl
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl non trouvé${NC}"
    exit 1
fi

# Vérifier vault CLI
if ! command -v vault &> /dev/null; then
    echo -e "${RED}❌ vault CLI non trouvé${NC}"
    exit 1
fi

# Port-forward Vault en arrière-plan
echo -e "${CYAN}📡 Démarrage du port-forward vers Vault...${NC}"
kubectl -n vault port-forward svc/vault 8200:8200 > /dev/null 2>&1 &
PF_PID=$!
sleep 3

# Créer un fichier temporaire sécurisé
TEMP_FILE=$(mktemp)

# Trap pour arrêter le port-forward à la fin
cleanup() {
    echo ""
    echo -e "${CYAN}🧹 Nettoyage...${NC}"
    kill $PF_PID 2>/dev/null || true
    rm -f "$TEMP_FILE" 2>/dev/null || true
}
trap cleanup EXIT

# Configuration Vault
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Vérifier la connexion
echo -e "${CYAN}🔍 Vérification de la connexion à Vault...${NC}"
if ! vault status &>/dev/null; then
    echo -e "${RED}❌ Impossible de se connecter à Vault${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Connexion à Vault OK${NC}"
echo ""

# Récupérer les secrets actuels
echo -e "${CYAN}📥 Récupération des secrets actuels...${NC}"
if ! vault kv get -format=json secret/cinegest/app > "$TEMP_FILE" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Aucun secret existant, création d'une nouvelle configuration${NC}"
    echo '{"data":{"data":{}}}' > "$TEMP_FILE"
fi

# Fonction pour récupérer une valeur actuelle
get_current_value() {
    local key=$1
    jq -r ".data.data.${key} // \"\"" "$TEMP_FILE"
}

# Fonction pour demander une valeur
ask_value() {
    local key=$1
    local description=$2
    local current_value=$(get_current_value "$key")
    local value
    
    echo "" >&2
    if [ -n "$current_value" ] && [ "$current_value" != "null" ]; then
        echo -e "${YELLOW}🔑 ${key}${NC} - ${description}" >&2
        echo -e "${CYAN}   Actuelle: ${current_value}${NC}" >&2
        read -p "   Nouvelle (ENTRÉE pour garder): " value >&2
        echo "${value:-$current_value}"
    else
        echo -e "${YELLOW}🔑 ${key}${NC} - ${description}" >&2
        read -p "   Valeur: " value >&2
        echo "${value}"
    fi
}

# Fonction pour demander une valeur sensible (masquée)
ask_secret() {
    local key=$1
    local description=$2
    local current_value=$(get_current_value "$key")
    local value
    
    echo "" >&2
    if [ -n "$current_value" ] && [ "$current_value" != "null" ]; then
        echo -e "${YELLOW}🔐 ${key}${NC} - ${description}" >&2
        echo -e "${CYAN}   Actuelle: ${current_value:0:10}...${NC} (masquée)" >&2
        read -sp "   Nouvelle (ENTRÉE pour garder): " value
        echo "" >&2
        echo "${value:-$current_value}"
    else
        echo -e "${YELLOW}🔐 ${key}${NC} - ${description}" >&2
        read -sp "   Valeur: " value
        echo "" >&2
        echo "${value}"
    fi
}

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}📝 CONFIGURATION DES SECRETS${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Appuyez sur ENTRÉE sans saisir de valeur pour conserver la valeur actuelle."

# Application
echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Application${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
APP_NAME=$(ask_value "APP_NAME" "Nom de l'application")
APP_ENV=$(ask_value "APP_ENV" "Environnement (production/staging/local)")
APP_KEY=$(ask_value "APP_KEY" "Clé Laravel (générez avec: php artisan key:generate --show)")
APP_DEBUG=$(ask_value "APP_DEBUG" "Debug mode (true/false)")
APP_URL=$(ask_value "APP_URL" "URL de l'API")
APP_FRONTEND_URL=$(ask_value "APP_FRONTEND_URL" "URL du frontend")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Base de données${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
DB_CONNECTION=$(ask_value "DB_CONNECTION" "Type de connexion")
DB_HOST=$(ask_value "DB_HOST" "Hôte MySQL")
DB_PORT=$(ask_value "DB_PORT" "Port MySQL")
DB_DATABASE=$(ask_value "DB_DATABASE" "Nom de la base de données")
DB_USERNAME=$(ask_value "DB_USERNAME" "Utilisateur MySQL")
DB_PASSWORD=$(ask_secret "DB_PASSWORD" "Mot de passe MySQL")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Cache & Sessions${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
SESSION_DRIVER=$(ask_value "SESSION_DRIVER" "Driver de session")
CACHE_STORE=$(ask_value "CACHE_STORE" "Store de cache")
QUEUE_CONNECTION=$(ask_value "QUEUE_CONNECTION" "Connexion de queue")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Sanctum${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
SANCTUM_STATEFUL_DOMAINS=$(ask_value "SANCTUM_STATEFUL_DOMAINS" "Domaines stateful Sanctum")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Email (Mailjet)${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
MAIL_MAILER=$(ask_value "MAIL_MAILER" "Mailer (smtp)")
MAIL_HOST=$(ask_value "MAIL_HOST" "Hôte SMTP")
MAIL_PORT=$(ask_value "MAIL_PORT" "Port SMTP")
MAIL_USERNAME=$(ask_secret "MAIL_USERNAME" "Username SMTP")
MAIL_PASSWORD=$(ask_secret "MAIL_PASSWORD" "Password SMTP")
MAIL_ENCRYPTION=$(ask_value "MAIL_ENCRYPTION" "Encryption (tls/ssl)")
MAIL_FROM_ADDRESS=$(ask_value "MAIL_FROM_ADDRESS" "Adresse email d'envoi")
MAIL_FROM_NAME=$(ask_value "MAIL_FROM_NAME" "Nom de l'expéditeur")
MAILJET_APIKEY=$(ask_secret "MAILJET_APIKEY" "Mailjet API Key")
MAILJET_APISECRET=$(ask_secret "MAILJET_APISECRET" "Mailjet API Secret")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Stripe${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
STRIPE_KEY=$(ask_secret "STRIPE_KEY" "Stripe Publishable Key")
STRIPE_SECRET=$(ask_secret "STRIPE_SECRET" "Stripe Secret Key")
STRIPE_WEBHOOK_SECRET=$(ask_secret "STRIPE_WEBHOOK_SECRET" "Stripe Webhook Secret")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Autre${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
MOVIE_API_KEY=$(ask_secret "MOVIE_API_KEY" "Movie API Key")

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}📋 RÉSUMÉ DE LA CONFIGURATION${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Application: $APP_NAME"
echo "Environnement: $APP_ENV"
echo "URL API: $APP_URL"
echo "URL Frontend: $APP_FRONTEND_URL"
echo ""
echo "Base de données: $DB_USERNAME@$DB_HOST:$DB_PORT/$DB_DATABASE"
echo "Email: $MAIL_FROM_ADDRESS via $MAIL_HOST"
echo ""

read -p "Voulez-vous sauvegarder cette configuration dans Vault ? (o/N): " confirm
if [[ ! "$confirm" =~ ^([oO][uU][iI]|[oO])$ ]]; then
    echo -e "${RED}❌ Annulé${NC}"
    exit 0
fi

echo ""
echo -e "${CYAN}💾 Sauvegarde des secrets dans Vault...${NC}"

vault kv put secret/cinegest/app \
  APP_NAME="$APP_NAME" \
  APP_ENV="$APP_ENV" \
  APP_KEY="$APP_KEY" \
  APP_DEBUG="$APP_DEBUG" \
  APP_URL="$APP_URL" \
  APP_FRONTEND_URL="$APP_FRONTEND_URL" \
  DB_CONNECTION="$DB_CONNECTION" \
  DB_HOST="$DB_HOST" \
  DB_PORT="$DB_PORT" \
  DB_DATABASE="$DB_DATABASE" \
  DB_USERNAME="$DB_USERNAME" \
  DB_PASSWORD="$DB_PASSWORD" \
  SESSION_DRIVER="$SESSION_DRIVER" \
  CACHE_STORE="$CACHE_STORE" \
  QUEUE_CONNECTION="$QUEUE_CONNECTION" \
  SANCTUM_STATEFUL_DOMAINS="$SANCTUM_STATEFUL_DOMAINS" \
  MAIL_MAILER="$MAIL_MAILER" \
  MAIL_HOST="$MAIL_HOST" \
  MAIL_PORT="$MAIL_PORT" \
  MAIL_USERNAME="$MAIL_USERNAME" \
  MAIL_PASSWORD="$MAIL_PASSWORD" \
  MAIL_ENCRYPTION="$MAIL_ENCRYPTION" \
  MAIL_FROM_ADDRESS="$MAIL_FROM_ADDRESS" \
  MAIL_FROM_NAME="$MAIL_FROM_NAME" \
  MAILJET_APIKEY="$MAILJET_APIKEY" \
  MAILJET_APISECRET="$MAILJET_APISECRET" \
  STRIPE_KEY="$STRIPE_KEY" \
  STRIPE_SECRET="$STRIPE_SECRET" \
  STRIPE_WEBHOOK_SECRET="$STRIPE_WEBHOOK_SECRET" \
  MOVIE_API_KEY="$MOVIE_API_KEY"

echo ""
echo -e "${GREEN}✅ Secrets sauvegardés dans Vault${NC}"
echo ""

echo -e "${CYAN}🔍 Vérification des secrets...${NC}"
vault kv get secret/cinegest/app

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Configuration mise à jour avec succès !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📋 Prochaines étapes:${NC}"
echo ""
echo "1. Les secrets seront automatiquement synchronisés vers Kubernetes"
echo "   (délai: jusqu'à 15 minutes ou au prochain redémarrage des pods)"
echo ""
echo "2. Pour forcer la resynchronisation immédiate:"
echo "   kubectl -n cinegest rollout restart deployment/cinegest-back"
echo ""
echo "3. Vérifier la synchronisation:"
echo "   kubectl -n cinegest get externalsecret"
echo "   kubectl -n cinegest describe externalsecret cinegest-back-vault"
echo ""

# Cleanup automatique via trap
