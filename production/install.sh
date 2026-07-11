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
mkdir -p "$PROJECT_DIR"

echo ""
echo "[3/9] Clonando código da aplicação para app/ (git)..."

# MUDANÇA: antes, o app/ era criado com "cp -r" a partir do checkout local,
# então nunca tinha pasta .git — e o auto-deploy.sh (que faz git fetch/reset)
# não tinha como funcionar. Agora clonamos de verdade, para app/ ser um
# repositório git independente que o auto-deploy.sh consegue atualizar.

# Descobre a URL de origin e a branch atual a partir do clone usado para instalar,
# assim não fica hardcoded e acompanha o repo/branch que a pessoa está usando.
if ! REPO_URL="$(git -C "$REPO_ROOT" config --get remote.origin.url 2>/dev/null)"; then
    echo "ERRO: não consegui detectar a URL do repositório git em $REPO_ROOT."
    echo "Rode o install.sh a partir de um clone git válido do Finance-Control."
    exit 1
fi

BRANCH="$(git -C "$REPO_ROOT" rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")"
if [ "$BRANCH" = "HEAD" ]; then
    BRANCH="main"
fi
echo "Repositório detectado: $REPO_URL (branch: $BRANCH)"

# Remove qualquer app/ residual de uma instalação anterior antes de clonar
rm -rf "$PROJECT_DIR/app"
git clone --branch "$BRANCH" --single-branch "$REPO_URL" "$PROJECT_DIR/app"

# Validação: confirma que a estrutura esperada veio no clone
for item in config includes public .htaccess; do
    if [ ! -e "$PROJECT_DIR/app/$item" ]; then
        echo "ERRO: '$item' não está presente em $PROJECT_DIR/app após o clone."
        exit 1
    fi
done
echo "Clonado com sucesso. Conteúdo de app/:"
ls -la "$PROJECT_DIR/app"

echo ""
echo "[4/9] Copiando arquivos Docker e scripts..."
cp -r "$FILES_DIR"/* "$PROJECT_DIR"/

echo ""
echo "[5/9] Aplicando senhas e porta personalizadas..."
sed -i "s/MYSQL_ROOT_PASSWORD: financecontrol/MYSQL_ROOT_PASSWORD: $ROOT_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/MYSQL_PASSWORD: financecontrol/MYSQL_PASSWORD: $USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"
sed -i "s/DB_PASSWORD=financecontrol/DB_PASSWORD=$USER_PASSWORD/" "$PROJECT_DIR/docker-compose.yml"

# Injeta a porta e a senha do banco como variáveis de ambiente
# (DB_USER_PASSWORD é usada pelos scripts backup-db.sh e run-migrations.sh)
cat > "$PROJECT_DIR/.env" <<EOF
APP_PORT=$APP_PORT
DB_USER_PASSWORD=$USER_PASSWORD
EOF

# Garante que o auto-deploy.sh siga acompanhando a mesma branch usada no clone
sed -i "s/^BRANCH=\".*\"/BRANCH=\"$BRANCH\"/" "$PROJECT_DIR/auto-deploy.sh"

echo ""
echo "[6/9] Configurando permissões e log..."
chmod +x "$PROJECT_DIR/auto-deploy.sh" 2>/dev/null || true
chmod +x "$PROJECT_DIR/run-migrations.sh" 2>/dev/null || true
chmod +x "$PROJECT_DIR/backup-db.sh" 2>/dev/null || true
touch "$PROJECT_DIR/deploy.log"

echo ""
echo "[7/9] Configurando Auto-Deploy (Cron) e Backup diário..."

# BUG CORRIGIDO: quando não existia crontab prévio, "crontab -l" falhava e
# "grep -v" num input vazio retornava código 1. Com "set -e" isso abortava
# o subshell ANTES do echo rodar, resultando num crontab vazio instalado.
# O "|| true" abaixo garante que o subshell sempre chegue até o echo.
(
  crontab -l 2>/dev/null | grep -v "auto-deploy.sh" | grep -v "backup-db.sh" || true
  echo "* * * * * $PROJECT_DIR/auto-deploy.sh"
  echo "0 3 * * * $PROJECT_DIR/backup-db.sh"
) | crontab -

echo "Cron instalado. Conteúdo atual:"
crontab -l

echo ""
echo "[8/9] Subindo containers..."
cd "$PROJECT_DIR"
docker compose up -d --build

echo ""
echo "[9/9] Aguardando o MariaDB ficar pronto..."

# Na primeira execução, o MariaDB cria o banco/usuário e REINICIA internamente,
# então não basta o container estar "Started" — é preciso esperar o servidor
# aceitar conexões de fato, senão a importação falha com erro de socket.
MAX_TRIES=30
TRIES=0
until docker exec finance-db mariadb -u financeAdmin -p"$USER_PASSWORD" -e "SELECT 1;" &> /dev/null; do
    TRIES=$((TRIES+1))
    if [ "$TRIES" -ge "$MAX_TRIES" ]; then
        echo "ERRO: MariaDB não ficou pronto a tempo. Verifique com: docker logs finance-db"
        exit 1
    fi
    echo "  Ainda não está pronto, tentando novamente ($TRIES/$MAX_TRIES)..."
    sleep 2
done
echo "MariaDB pronto."

echo ""
echo "Importando banco de dados..."
docker exec -i finance-db mariadb -u financeAdmin -p"$USER_PASSWORD" financecontrol < "$PROJECT_DIR/banco.sql"

echo ""
echo "Aplicando migrations pendentes..."
"$PROJECT_DIR/run-migrations.sh"

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