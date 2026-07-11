#!/bin/bash
set -e

# ==================== CONFIGURAÇÕES ====================
COMPOSE_DIR="$HOME/finance-control"
BACKUP_DIR="$COMPOSE_DIR/backups"
DB_CONTAINER="finance-db"
DB_USER="financeAdmin"
DB_NAME="financecontrol"
# ==========================================================

if [ ! -f "$COMPOSE_DIR/.env" ]; then
    echo "ERRO: $COMPOSE_DIR/.env não encontrado (precisa de DB_USER_PASSWORD)."
    exit 1
fi
source "$COMPOSE_DIR/.env"

if [ -z "$DB_USER_PASSWORD" ]; then
    echo "ERRO: DB_USER_PASSWORD não está definido em $COMPOSE_DIR/.env."
    exit 1
fi

# Sem argumento: lista os backups disponíveis e sai
if [ -z "$1" ]; then
    echo "Uso: $0 <nome-do-arquivo.sql.gz>"
    echo ""
    echo "Backups disponíveis em $BACKUP_DIR:"
    ls -lh "$BACKUP_DIR"/*.sql.gz 2>/dev/null || echo "  (nenhum backup encontrado)"
    exit 1
fi

BACKUP_FILE="$BACKUP_DIR/$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERRO: arquivo não encontrado: $BACKUP_FILE"
    exit 1
fi

echo "=========================================================="
echo "  ATENÇÃO: isso vai SOBRESCREVER o banco de dados atual"
echo "  com o conteúdo de: $1"
echo "=========================================================="
read -p "Digite SIM para confirmar: " CONFIRM

if [ "$CONFIRM" != "SIM" ]; then
    echo "Cancelado."
    exit 1
fi

# Backup de segurança do estado atual, antes de sobrescrever
echo "Fazendo backup de segurança do estado atual antes de restaurar..."
"$COMPOSE_DIR/backup-db.sh"

echo "Restaurando $1 ..."
gunzip -c "$BACKUP_FILE" | docker exec -i "$DB_CONTAINER" mariadb -u "$DB_USER" -p"$DB_USER_PASSWORD" "$DB_NAME"

echo "Restauração concluída."