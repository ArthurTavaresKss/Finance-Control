#!/bin/bash

# ==================== CONFIGURAÇÕES ====================
PROJECT_DIR="~/finance-control/app"
COMPOSE_DIR="~/finance-control"
COMPOSE_FILE="$COMPOSE_DIR/docker-compose.yml"
LOG_FILE="$COMPOSE_DIR/deploy.log"
BRANCH="main"
# =======================================================

# Se o log passar de 2000 linhas, mantém apenas as últimas 1000
if [ -f "$LOG_FILE" ] && [ $(wc -l < "$LOG_FILE") -gt 2000 ]; then
    echo "$(tail -n 1000 "$LOG_FILE")" > "$LOG_FILE"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Log rotacionado para economizar espaço." >> "$LOG_FILE"
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Iniciando verificação de deploy..." | tee -a "$LOG_FILE"

cd "$PROJECT_DIR" || exit 1

# Busca atualizações
git fetch origin "$BRANCH" 2>&1 | tee -a "$LOG_FILE"

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/"$BRANCH")

if [ "$LOCAL" != "$REMOTE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Nova atualização detectada! Aplicando..." | tee -a "$LOG_FILE"

    # Força a atualização (sobrescreve mudanças locais)
    git reset --hard origin/"$BRANCH" 2>&1 | tee -a "$LOG_FILE"

    # Reinicia o container (executa do diretório correto)
    cd "$COMPOSE_DIR"
    docker compose restart 2>&1 | tee -a "$LOG_FILE"

    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy concluído com sucesso." | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Nenhuma atualização encontrada." >> "$LOG_FILE"
fi