#!/bin/bash
set -euo pipefail

# Configurer KUBECONFIG pour accéder au cluster K3s
export KUBECONFIG=/etc/rancher/k3s/k3s.yaml

# Utiliser sudo pour kubectl et exporter KUBECONFIG
KUBECTL="sudo -E kubectl"
HELM="sudo -E helm"

echo "📦 Installation de External Secrets Operator..."

# Installer ESO avec Helm
$HELM repo add external-secrets https://charts.external-secrets.io || echo "Repo already exists"
$HELM repo update

$HELM upgrade --install external-secrets \
  external-secrets/external-secrets \
  --namespace external-secrets \
  --create-namespace \
  --set installCRDs=true \
  --wait

echo "✅ External Secrets Operator installé"

echo ""
echo "📦 Déploiement de Vault..."

# Créer les namespaces
$KUBECTL apply -f k8s/vault/namespace.yaml

# Déployer Vault
$KUBECTL apply -f k8s/vault/pvc.yaml
$KUBECTL apply -f k8s/vault/deployment.yaml
$KUBECTL apply -f k8s/vault/service.yaml

echo "⏳ Attente du démarrage de Vault..."
$KUBECTL -n vault wait --for=condition=ready pod -l app=vault --timeout=300s

echo ""
echo "🔧 Initialisation de Vault..."
echo "Exécuter manuellement :"
echo ""
echo "  $KUBECTL -n vault exec -it deploy/vault -- sh"
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
