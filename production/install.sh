#!/bin/bash

set -e

echo "=========================================="
echo "   INSTALAÇÃO DO FINANCE CONTROL"
echo "=========================================="

# ==================== CONFIGURAÇÕES ====================
SERVICE_USER="financecontrol"
PROJECT_DIR="/opt/financecontrol"

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
echo "[1/11] Criando usuário do sistema operacional..."

# Cria o usuário de serviço, sem shell de login, com home em /opt/financecontrol.
# Idempotente: se já existir (reinstalação), não tenta criar de novo.
if id "$SERVICE_USER" &>/dev/null; then
    echo "Usuário '$SERVICE_USER' já existe, pulando criação."
else
    sudo useradd -r -m -d "$PROJECT_DIR" -s /usr/sbin/nologin "$SERVICE_USER"
    echo "Usuário '$SERVICE_USER' criado (home: $PROJECT_DIR)."
fi

echo ""
echo "[2/11] Verificando dependências..."
if ! command -v git &> /dev/null; then
    sudo apt update && sudo apt install -y git
fi

if ! command -v docker &> /dev/null; then
    sudo apt update && sudo apt install -y docker.io docker-compose-plugin
    sudo systemctl enable docker
    sudo systemctl start docker
    sudo usermod -aG docker "$USER"
fi

# Só agora o grupo "docker" com certeza existe (criado pela instalação do
# pacote acima), então é o momento certo de adicionar o usuário de serviço a ele.
sudo usermod -aG docker "$SERVICE_USER"
echo "Usuário '$SERVICE_USER' adicionado ao grupo docker."

echo ""
echo "[3/11] Clonando código da aplicação para app/ (git)..."

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

# Remove qualquer app/ residual de uma instalação anterior antes de clonar.
# Tudo em /opt/financecontrol pertence (ou vai passar a pertencer) ao usuário
# de serviço, então essas operações de arquivo precisam de sudo.
sudo rm -rf "$PROJECT_DIR/app"
sudo git clone --branch "$BRANCH" --single-branch "$REPO_URL" "$PROJECT_DIR/app"

# Validação: confirma que a estrutura esperada veio no clone
for item in config includes public .htaccess; do
    if ! sudo test -e "$PROJECT_DIR/app/$item"; then
        echo "ERRO: '$item' não está presente em $PROJECT_DIR/app após o clone."
        exit 1
    fi
done
echo "Clonado com sucesso. Conteúdo de app/:"
sudo ls -la "$PROJECT_DIR/app"

echo ""
echo "[4/11] Copiando arquivos Docker e scripts..."
sudo cp -r "$FILES_DIR"/* "$PROJECT_DIR"/

echo ""
echo "[5/11] Aplicando senhas e porta personalizadas..."
ROOT_PASSWORD_ESCAPED=$(printf '%s' "$ROOT_PASSWORD" | sed 's/[\/&]/\\&/g')
USER_PASSWORD_ESCAPED=$(printf '%s' "$USER_PASSWORD" | sed 's/[\/&]/\\&/g')
sudo sed -i \
"s/MYSQL_ROOT_PASSWORD: financecontrol/MYSQL_ROOT_PASSWORD: $ROOT_PASSWORD_ESCAPED/" \
"$PROJECT_DIR/docker-compose.yml"

sudo sed -i \
"s/MYSQL_PASSWORD: financecontrol/MYSQL_PASSWORD: $USER_PASSWORD_ESCAPED/" \
"$PROJECT_DIR/docker-compose.yml"

sudo sed -i \
"s/DB_PASSWORD=financecontrol/DB_PASSWORD=$USER_PASSWORD_ESCAPED/" \
"$PROJECT_DIR/docker-compose.yml"

# Injeta a porta e a senha do banco como variáveis de ambiente
# (DB_USER_PASSWORD é usada pelos scripts backup-db.sh e run-migrations.sh)
sudo tee "$PROJECT_DIR/.env" > /dev/null <<EOF
APP_PORT="$APP_PORT"
DB_USER_PASSWORD="$USER_PASSWORD"
EOF

# Garante que o auto-deploy.sh siga acompanhando a mesma branch usada no clone
sudo sed -i "s/^BRANCH=\".*\"/BRANCH=\"$BRANCH\"/" "$PROJECT_DIR/auto-deploy.sh"

echo ""
echo "[6/11] Configurando permissões, dono dos arquivos e log..."

# Log fica em /var/log/financecontrol, separado dos arquivos da aplicação em
# /opt - é o lugar padrão do Linux para logs, e facilita rotação (logrotate)
# e backup/limpeza sem mexer no diretório da aplicação.
# Dois arquivos separados: deploy.log só com o que o auto-deploy.sh faz
# (fetch/reset/restart), e database.log com tudo que mexe no banco
# (backup-db.sh, run-migrations.sh e restore-db.sh).
LOG_DIR="/var/log/financecontrol"
sudo mkdir -p "$LOG_DIR"
sudo touch "$LOG_DIR/deploy.log" "$LOG_DIR/database.log"
sudo chown -R "$SERVICE_USER:$SERVICE_USER" "$LOG_DIR"

# Tudo em /opt/financecontrol passa a pertencer ao usuário de serviço -
# é ele quem vai rodar o cron, o systemd, e precisa poder ler/escrever aqui
# (inclusive o bind mount ./app:/var/www/html usado pelo container).
sudo chown -R "$SERVICE_USER:$SERVICE_USER" "$PROJECT_DIR"

sudo chmod +x "$PROJECT_DIR/auto-deploy.sh" 2>/dev/null || true
sudo chmod +x "$PROJECT_DIR/run-migrations.sh" 2>/dev/null || true
sudo chmod +x "$PROJECT_DIR/backup-db.sh" 2>/dev/null || true
sudo chmod +x "$PROJECT_DIR/restore-db.sh" 2>/dev/null || true

echo ""
echo "[7/11] Configurando Auto-Deploy (Cron) e Backup diário..."

# O cron agora roda como o usuário de serviço (financecontrol), não como quem
# executou o install.sh - precisa de sudo pra editar o crontab de outro usuário.
# BUG CORRIGIDO (herdado de versões anteriores): quando não existia crontab
# prévio, "crontab -l" falhava e "grep -v" num input vazio retornava código 1.
# Com "set -e" isso abortava o subshell ANTES do echo rodar, resultando num
# crontab vazio instalado. O "|| true" garante que o subshell sempre chegue
# até os echos finais.
(
  sudo crontab -u "$SERVICE_USER" -l 2>/dev/null | grep -v "auto-deploy.sh" | grep -v "backup-db.sh" || true
  echo "* * * * * $PROJECT_DIR/auto-deploy.sh"
  echo "0 3 * * * $PROJECT_DIR/backup-db.sh"
) | sudo crontab -u "$SERVICE_USER" -

echo "Cron instalado para o usuário $SERVICE_USER. Conteúdo atual:"
sudo crontab -u "$SERVICE_USER" -l

echo ""
echo "[8/11] Subindo containers temporariamente para configurar o banco..."

# Roda como o usuário de serviço, do mesmo jeito que o systemd vai rodar depois -
# testa o caminho de permissões real (grupo docker) já durante a instalação.
sudo -u "$SERVICE_USER" bash -c "cd '$PROJECT_DIR' && docker compose up -d --build"

echo ""
echo "[9/11] Aguardando o MariaDB, importando banco e aplicando migrations..."

# Na primeira execução, o MariaDB cria o banco/usuário e REINICIA internamente,
# então não basta o container estar "Started" - é preciso esperar o servidor
# aceitar conexões de fato, senão a importação falha com erro de socket.
MAX_TRIES=30
TRIES=0
until sudo -u "$SERVICE_USER" docker exec finance-db mariadb -u financeAdmin -p"$USER_PASSWORD" -e "SELECT 1;" &> /dev/null; do
    TRIES=$((TRIES+1))
    if [ "$TRIES" -ge "$MAX_TRIES" ]; then
        echo "ERRO: MariaDB não ficou pronto a tempo. Verifique com: docker logs finance-db"
        exit 1
    fi
    echo "  Ainda não está pronto, tentando novamente ($TRIES/$MAX_TRIES)..."
    sleep 2
done
echo "MariaDB pronto."

echo "Importando banco de dados..."
sudo -u "$SERVICE_USER" bash -c "docker exec -i finance-db mariadb -u financeAdmin -p'$USER_PASSWORD' financecontrol < '$PROJECT_DIR/banco.sql'"

echo "Aplicando migrations pendentes..."
sudo -u "$SERVICE_USER" "$PROJECT_DIR/run-migrations.sh"

echo ""
echo "[10/11] Parando os containers (o systemd assume o controle a partir daqui)..."

# "docker compose down" (sem -v!) remove containers e rede, mas preserva o
# volume nomeado do banco (db_data) - os dados importados acima não se perdem.
sudo -u "$SERVICE_USER" bash -c "cd '$PROJECT_DIR' && docker compose down"

echo ""
echo "[11/11] Criando e habilitando o serviço systemd..."

sudo cp "$PROJECT_DIR/financecontrol.service" /etc/systemd/system/financecontrol.service
sudo systemctl daemon-reload
sudo systemctl enable financecontrol.service
sudo systemctl start financecontrol.service

echo "Serviço systemd 'financecontrol' criado, habilitado e iniciado."
sudo systemctl status financecontrol.service --no-pager || true

echo ""
echo "=========================================="
echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "=========================================="
echo ""
echo "Acesse em: http://SEU_IP:$APP_PORT"
echo "Diretório do projeto: $PROJECT_DIR"
echo "Usuário de sistema: $SERVICE_USER"
echo ""
echo "Senhas configuradas:"
echo "  Root MariaDB:         $ROOT_PASSWORD"
echo "  Usuário financeAdmin: $USER_PASSWORD"
echo "  Porta da aplicação:   $APP_PORT"
echo ""
echo "Comandos úteis:"
echo "  sudo systemctl status financecontrol      # ver status do serviço"
echo "  sudo systemctl reload financecontrol      # reiniciar containers (restart, sem down/up)"
echo "  sudo systemctl restart financecontrol     # parar e subir tudo de novo"
echo "  sudo systemctl stop financecontrol        # parar tudo"
echo "  sudo -u $SERVICE_USER cat /var/log/financecontrol/deploy.log     # logs do auto-deploy"
echo "  sudo -u $SERVICE_USER cat /var/log/financecontrol/database.log  # logs de backup/migrations/restore"
echo "  sudo -u $SERVICE_USER bash -c \"cd $PROJECT_DIR && docker compose ps\""
echo ""