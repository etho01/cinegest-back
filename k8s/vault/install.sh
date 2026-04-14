#!/bin/bash
set -euo pipefail

echo "📦 Installation de External Secrets Operator..."

# Installer ESO avec Helm
helm repo add external-secrets https://charts.external-secrets.io
helm repo update

helm upgrade --install external-secrets \
  external-secrets/external-secrets \
  --namespace external-secrets \
  --create-namespace \
  --set installCRDs=true \
  --wait

echo "✅ External Secrets Operator installé"

echo ""
echo "📦 Déploiement de Vault..."

# Créer les namespaces
kubectl apply -f k8s/vault/namespace.yaml

# Déployer Vault
kubectl apply -f k8s/vault/pvc.yaml
kubectl apply -f k8s/vault/deployment.yaml
kubectl apply -f k8s/vault/service.yaml

echo "⏳ Attente du démarrage de Vault..."
kubectl -n vault wait --for=condition=ready pod -l app=vault --timeout=300s

echo ""
echo "🔧 Initialisation de Vault..."
echo "Exécuter manuellement :"
echo ""
echo "  kubectl -n vault exec -it deploy/vault -- sh"
echo ""
echo "Puis dans le pod :"
echo "  export VAULT_ADDR=http://localhost:8200"
echo "  vault operator init -key-shares=1 -key-threshold=1"
echo ""
echo "Sauvegarder UNSEAL_KEY et ROOT_TOKEN générés !"
echo ""
echo "Ensuite, unseal Vault :"
echo "  vault operator unseal <UNSEAL_KEY>"
echo ""
echo "Puis, configurer Vault avec le script :"
echo "  ./k8s/vault/configure-vault.sh <ROOT_TOKEN>"
