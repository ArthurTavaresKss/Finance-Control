#!/bin/bash

set -e

echo "=========================================="
echo "   INSTALAÇÃO DO FINANCE CONTROL"
echo "=========================================="

# ==================== CONFIGURAÇÕES ====================
PROJECT_DIR="$HOME/finance-control"

# BUG CORRIGIDO: era "../../" (subia 2 níveis, saindo do repo).
# install.sh fica em Finance-Control/production/install.sh,
# então basta subir 1 nível para chegar em Finance-Control/.
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FILES_DIR="$(dirname "$0")/files"
# ========================================================

# Lê parâmetros -rp, -p e -pt
ROOT_PASSWORD="financecontrol"
USER_PASSWORD="financecontrol"
APP_PORT="3847"

while [[ $# -gt 0 ]]; do
  case $1 in
    -rp|--root-password)
      ROOT_PASSWORD="$2"; shift 2 ;;
    -p|--password)
      USER_PASSWORD="$2"; shift 2 ;;
    -pt|--port)
      APP_PORT="$2"; shift 2 ;;
    *)
      echo "Uso: $0 [-rp senha_root] [-p senha_usuario] [-pt porta]"
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

# Validação explícita: se algo não existir no REPO_ROOT esperado, o script
# avisa e para, em vez de falhar silenciosamente como antes (2>/dev/null || true).
for item in config includes public .htaccess; do
    if [ ! -e "$REPO_ROOT/$item" ]; then
        echo "ERRO: não encontrei '$REPO_ROOT/$item'."
        echo "Verifique se REPO_ROOT está correto. REPO_ROOT atual: $REPO_ROOT"
        exit 1
    fi
done

cp -r "$REPO_ROOT/config" "$REPO_ROOT/includes" "$REPO_ROOT/public" "$REPO_ROOT/.htaccess" "$PROJECT_DIR/app/"
echo "Copiado com sucesso. Conteúdo de app/:"
ls -la "$PROJECT_DIR/app"

echo ""
echo "[4/9] Copiando arquivos Docker e scripts..."
cp -r "$FILES_DIR"/* "$PROJECT_DIR"/

echo ""
echo "[5/9] Aplicando senhas e porta personalizadas..."
sed -i "s/MYSQL_ROOT_PASSWORD: financecontrol/MYSQL_ROOT_PASSWORD: $ROOT_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/MYSQL_PASSWORD: financecontrol/MYSQL_PASSWORD: $USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/DB_PASSWORD=financecontrol/DB_PASSWORD=$USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"

# Injeta a porta como variável de ambiente para o docker compose
echo "APP_PORT=$APP_PORT" > "$PROJECT_DIR/.env"

echo ""
echo "[6/9] Configurando permissões e log..."
chmod +x "$PROJECT_DIR/auto-deploy.sh" 2>/dev/null || true
touch "$PROJECT_DIR/deploy.log"

echo ""
echo "[7/9] Configurando Auto-Deploy (Cron)..."

# BUG CORRIGIDO: quando não existia crontab prévio, "crontab -l" falhava e
# "grep -v" num input vazio retornava código 1. Com "set -e" isso abortava
# o subshell ANTES do echo rodar, resultando num crontab vazio instalado.
# O "|| true" abaixo garante que o subshell sempre chegue até o echo.
(crontab -l 2>/dev/null | grep -v "auto-deploy.sh" || true; echo "* * * * * $PROJECT_DIR/auto-deploy.sh") | crontab -

echo "Cron instalado. Conteúdo atual:"
crontab -l

echo ""
echo "[8/9] Subindo containers..."
cd "$PROJECT_DIR"
docker compose up -d --build

echo ""
echo "[9/9] Importando banco de dados..."
docker exec -i finance-db mariadb -u financeAdmin -p"$USER_PASSWORD" financecontrol < "$PROJECT_DIR/banco.sql"

echo ""
echo "=========================================="
echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "=========================================="
echo ""
echo "Acesse em: http://SEU_IP:$APP_PORT"
echo "Diretório do projeto: $PROJECT_DIR"
echo ""
echo "Senhas configuradas:"
echo "  Root MariaDB:         $ROOT_PASSWORD"
echo "  Usuário financeAdmin: $USER_PASSWORD"
echo "  Porta da aplicação:   $APP_PORT"
echo ""
echo "Comandos úteis:"
echo "  cat $PROJECT_DIR/deploy.log"
echo "  docker compose ps"
echo "  docker compose restart finance-app"
echo ""
