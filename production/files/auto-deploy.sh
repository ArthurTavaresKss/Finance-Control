#!/bin/bash

# ==================== CONFIGURAÇÕES ====================
# BUG CORRIGIDO: "~" não expande dentro de aspas em bash, então
# PROJECT_DIR/COMPOSE_DIR viravam paths literais que não existiam.
# Usar $HOME resolve isso corretamente.
PROJECT_DIR="$HOME/finance-control/app"
COMPOSE_DIR="$HOME/finance-control"
COMPOSE_FILE="$COMPOSE_DIR/docker-compose.yml"
LOG_FILE="$COMPOSE_DIR/deploy.log"
BRANCH="main"
# =======================================================

# Se o log passar de 2000 linhas, mantém apenas as últimas 1000
if [ -f "$LOG_FILE" ] && [ "$(wc -l < "$LOG_FILE")" -gt 2000 ]; then
    tail -n 1000 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Log rotacionado para economizar espaço." >> "$LOG_FILE"
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Iniciando verificação de deploy..." | tee -a "$LOG_FILE"

cd "$PROJECT_DIR" || {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: não foi possível acessar $PROJECT_DIR" | tee -a "$LOG_FILE"
    exit 1
}

# Confirma que app/ é de fato um repositório git antes de tentar comandos git nele
if [ ! -d ".git" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO: $PROJECT_DIR não é um repositório git. Rode o install.sh novamente." | tee -a "$LOG_FILE"
    exit 1
fi

# Busca atualizações
git fetch origin "$BRANCH" 2>&1 | tee -a "$LOG_FILE"

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/"$BRANCH")

if [ "$LOCAL" != "$REMOTE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Nova atualização detectada! Aplicando..." | tee -a "$LOG_FILE"

    # Backup ANTES de qualquer mudança de schema, como rede de segurança
    "$COMPOSE_DIR/backup-db.sh" || {
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO no backup, abortando deploy por segurança." | tee -a "$LOG_FILE"
        exit 1
    }

    # Força a atualização (sobrescreve mudanças locais)
    git reset --hard origin/"$BRANCH" 2>&1 | tee -a "$LOG_FILE"

    # Aplica migrations pendentes (novas tabelas/colunas), sem apagar dados existentes
    "$COMPOSE_DIR/run-migrations.sh" || {
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERRO ao aplicar migrations, abortando deploy." | tee -a "$LOG_FILE"
        exit 1
    }

    # Reinicia o container (executa do diretório correto)
    # BUG CORRIGIDO: faltava espaço antes de "2>&1" ("app2>&1" era lido
    # como o serviço "app2", que não existe).
    cd "$COMPOSE_DIR" || exit 1
    docker compose restart app 2>&1 | tee -a "$LOG_FILE"

    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy concluído com sucesso." | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Nenhuma atualização encontrada." >> "$LOG_FILE"
fi