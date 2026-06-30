#!/bin/bash

set -e

echo "=========================================="
echo "   INSTALAÇÃO DO FINANCE CONTROL"
echo "=========================================="

# ==================== CONFIGURAÇÕES ====================
PROJECT_DIR="$HOME/finance-control"
REPO_ROOT="$(cd "$(dirname "$0")/../../" && pwd)"   # Detecta a raiz do repositório clonado
FILES_DIR="$(dirname "$0")/files"
# ========================================================

# Lê parâmetros -rp e -p
ROOT_PASSWORD="financecontrol"
USER_PASSWORD="financecontrol"

while [[ $# -gt 0 ]]; do
  case $1 in
    -rp|--root-password)
      ROOT_PASSWORD="$2"; shift 2 ;;
    -p|--password)
      USER_PASSWORD="$2"; shift 2 ;;
    *)
      echo "Uso: $0 [-rp senha_root] [-p senha_usuario]"
      exit 1 ;;
  esac
done

echo ""
echo "[1/9] Verificando dependências..."

if ! command -v git &> /dev/null; then
    sudo apt update && sudo apt install -y git
fi

if ! command -v docker &> /dev/null; then
    sudo apt update && sudo apt install -y docker.io docker-compose-plugin
    sudo systemctl enable docker
    sudo systemctl start docker
    sudo usermod -aG docker "$USER"
fi

echo ""
echo "[2/9] Criando diretório do projeto: $PROJECT_DIR"
mkdir -p "$PROJECT_DIR/app"

echo ""
echo "[3/9] Copiando código da aplicação para app/..."
cp -r "$REPO_ROOT/config" "$REPO_ROOT/includes" "$REPO_ROOT/public" "$REPO_ROOT/.htaccess" "$PROJECT_DIR/app/" 2>/dev/null || true

echo ""
echo "[4/9] Copiando arquivos Docker e scripts..."
cp -r "$FILES_DIR"/* "$PROJECT_DIR"/

echo ""
echo "[5/9] Aplicando senhas personalizadas..."
sed -i "s/MYSQL_ROOT_PASSWORD: financecontrol/MYSQL_ROOT_PASSWORD: $ROOT_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/MYSQL_PASSWORD: financecontrol/MYSQL_PASSWORD: $USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/DB_PASSWORD=financecontrol/DB_PASSWORD=$USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"

echo ""
echo "[6/9] Configurando permissões e log..."
chmod +x "$PROJECT_DIR/auto-deploy.sh" 2>/dev/null || true
touch "$PROJECT_DIR/deploy.log"

echo ""
echo "[7/9] Configurando Auto-Deploy (Cron)..."
(crontab -l 2>/dev/null | grep -v "auto-deploy.sh"; echo "* * * * * $PROJECT_DIR/auto-deploy.sh") | crontab -

echo ""
echo "[8/9] Subindo containers..."
cd "$PROJECT_DIR"
docker compose up -d --build

echo ""
echo "[9/9] Importando banco de dados..."
docker exec -i finance-db mariadb -u financeAdmin -p"$USER_PASSWORD" financecontrol < "$PROJECT_DIR/banco.sql" 2>/dev/null || true

echo ""
echo "=========================================="
echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "=========================================="
echo ""
echo "Acesse em: http://SEU_IP:8085"
echo "Diretório do projeto: $PROJECT_DIR"
echo ""
echo "Senhas configuradas:"
echo "  Root MariaDB:        $ROOT_PASSWORD"
echo "  Usuário financeAdmin: $USER_PASSWORD"
echo ""
echo "Comandos úteis:"
echo "  cat $PROJECT_DIR/deploy.log"
echo "  docker compose ps"
echo "  docker compose restart finance-app"
echo ""