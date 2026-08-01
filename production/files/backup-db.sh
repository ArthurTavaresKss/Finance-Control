#!/bin/bash
set -e

# ==================== CONFIGURAÇÕES ====================
# Caminho fixo: /opt/financecontrol é a home do usuário de sistema dedicado.
COMPOSE_DIR="/opt/financecontrol"
BACKUP_DIR="$COMPOSE_DIR/backups"
LOG_FILE="/var/log/financecontrol/database.log"
DB_CONTAINER="finance-db"
DB_USER="financeAdmin"
DB_NAME="financecontrol"
KEEP_DAYS=14
# ==========================================================
# OBS: backups ficam em $COMPOSE_DIR/backups, FORA da pasta app/ que é
# montada no container. Isso garante que os backups nunca fiquem
# acessíveis pela web, mesmo que o .htaccess falhe por algum motivo.

if [ ! -f "$COMPOSE_DIR/.env" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: $COMPOSE_DIR/.env não encontrado (precisa de DB_USER_PASSWORD)." | tee -a "$LOG_FILE"
    exit 1
fi
source "$COMPOSE_DIR/.env"

if [ -z "$DB_USER_PASSWORD" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: DB_USER_PASSWORD não está definido em $COMPOSE_DIR/.env." | tee -a "$LOG_FILE"
    exit 1
fi

mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date '+%Y-%m-%d_%H-%M-%S')"
BACKUP_FILE="$BACKUP_DIR/financecontrol_$TIMESTAMP.sql.gz"

if docker exec "$DB_CONTAINER" mariadb-dump -u "$DB_USER" -p"$DB_USER_PASSWORD" "$DB_NAME" | gzip > "$BACKUP_FILE"; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup criado: $BACKUP_FILE" | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO ao criar backup!" | tee -a "$LOG_FILE"
    rm -f "$BACKUP_FILE"
    exit 1
fi

# Remove backups com mais de KEEP_DAYS dias
find "$BACKUP_DIR" -name "financecontrol_*.sql.gz" -mtime +"$KEEP_DAYS" -delete