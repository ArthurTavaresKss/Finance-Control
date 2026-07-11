#!/bin/bash
set -e

# ==================== CONFIGURAÇÕES ====================
COMPOSE_DIR="$HOME/finance-control"
MIGRATIONS_DIR="$COMPOSE_DIR/app/migrations"
LOG_FILE="$COMPOSE_DIR/deploy.log"
DB_CONTAINER="finance-db"
DB_USER="financeAdmin"
DB_NAME="financecontrol"
# ==========================================================

if [ ! -f "$COMPOSE_DIR/.env" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: $COMPOSE_DIR/.env não encontrado (precisa de DB_USER_PASSWORD)." | tee -a "$LOG_FILE"
    exit 1
fi
source "$COMPOSE_DIR/.env"

if [ -z "$DB_USER_PASSWORD" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: DB_USER_PASSWORD não está definido em $COMPOSE_DIR/.env." | tee -a "$LOG_FILE"
    exit 1
fi

# Garante que a tabela de controle de migrations existe
docker exec -i "$DB_CONTAINER" mariadb -u "$DB_USER" -p"$DB_USER_PASSWORD" "$DB_NAME" <<'SQL'
CREATE TABLE IF NOT EXISTS _migrations (
  id INT NOT NULL AUTO_INCREMENT,
  filename VARCHAR(255) NOT NULL UNIQUE,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
SQL

if [ ! -d "$MIGRATIONS_DIR" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Pasta de migrations não encontrada ($MIGRATIONS_DIR), nada a fazer." >> "$LOG_FILE"
    exit 0
fi

shopt -s nullglob
MIGRATION_FILES=("$MIGRATIONS_DIR"/*.sql)

if [ ${#MIGRATION_FILES[@]} -eq 0 ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Nenhum arquivo de migration encontrado." >> "$LOG_FILE"
    exit 0
fi

for file in "${MIGRATION_FILES[@]}"; do
    name="$(basename "$file")"

    ALREADY=$(docker exec -i "$DB_CONTAINER" mariadb -u "$DB_USER" -p"$DB_USER_PASSWORD" -N -B "$DB_NAME" \
        -e "SELECT COUNT(*) FROM _migrations WHERE filename='$name';")

    if [ "$ALREADY" -eq 0 ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Aplicando migration: $name" | tee -a "$LOG_FILE"

        if docker exec -i "$DB_CONTAINER" mariadb -u "$DB_USER" -p"$DB_USER_PASSWORD" "$DB_NAME" < "$file" 2>>"$LOG_FILE"; then
            docker exec -i "$DB_CONTAINER" mariadb -u "$DB_USER" -p"$DB_USER_PASSWORD" "$DB_NAME" \
                -e "INSERT INTO _migrations (filename) VALUES ('$name');"
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] Migration $name aplicada com sucesso." | tee -a "$LOG_FILE"
        else
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO ao aplicar migration $name! Verifique o log acima." | tee -a "$LOG_FILE"
            exit 1
        fi
    fi
done