#!/bin/bash
set -e

# ==================== CONFIGURAÇÕES ====================
SERVICE_USER="financecontrol"
PROJECT_DIR="/opt/financecontrol"
LOG_DIR="/var/log/financecontrol"
SERVICE_FILE="/etc/systemd/system/financecontrol.service"
PRESERVE_DIR="$HOME/financecontrol-uninstall-backup-$(date +%Y%m%d_%H%M%S)"
# ==========================================================

echo "=========================================================="
echo "  ATENÇÃO: isso vai REMOVER COMPLETAMENTE o Finance Control"
echo "  deste servidor:"
echo ""
echo "    - Serviço systemd 'financecontrol'"
echo "    - Containers e volumes Docker (incluindo o banco de dados)"
echo "    - Usuário de sistema '$SERVICE_USER' e sua home ($PROJECT_DIR)"
echo "    - Cron do usuário '$SERVICE_USER'"
echo "    - Logs em $LOG_DIR"
echo ""
echo "  Uma cópia dos backups e logs será preservada em:"
echo "    $PRESERVE_DIR"
echo "=========================================================="
read -p "Digite SIM para confirmar a desinstalação: " CONFIRM

if [ "$CONFIRM" != "SIM" ]; then
    echo "Cancelado."
    exit 1
fi

echo ""
echo "[1/6] Preservando backups e logs antes de remover tudo..."
sudo mkdir -p "$PRESERVE_DIR/backups" "$PRESERVE_DIR/logs"

if sudo test -d "$PROJECT_DIR/backups"; then
    sudo cp -a "$PROJECT_DIR/backups/." "$PRESERVE_DIR/backups/" 2>/dev/null || true
fi
if sudo test -d "$LOG_DIR"; then
    sudo cp -a "$LOG_DIR/." "$PRESERVE_DIR/logs/" 2>/dev/null || true
fi

# Dono os arquivos preservados pra quem está rodando o uninstall,
# senão ficam só acessíveis via sudo (foram copiados como root).
sudo chown -R "$(id -u):$(id -g)" "$PRESERVE_DIR"
echo "Backups e logs preservados em: $PRESERVE_DIR"

echo ""
echo "[2/6] Parando e removendo containers, volumes e imagem da aplicação..."
if id "$SERVICE_USER" &>/dev/null && sudo test -f "$PROJECT_DIR/docker-compose.yml"; then
    sudo -u "$SERVICE_USER" bash -c "cd '$PROJECT_DIR' && docker compose down -v --rmi local" || true
else
    echo "Nenhuma instalação Docker ativa encontrada, pulando esta etapa."
fi

echo ""
echo "[3/6] Removendo o serviço systemd..."
if systemctl list-unit-files 2>/dev/null | grep -q "^financecontrol.service"; then
    sudo systemctl stop financecontrol.service 2>/dev/null || true
    sudo systemctl disable financecontrol.service 2>/dev/null || true
fi
sudo rm -f "$SERVICE_FILE"
sudo systemctl daemon-reload
echo "Serviço systemd removido."

echo ""
echo "[4/6] Removendo o cron do usuário '$SERVICE_USER'..."
if id "$SERVICE_USER" &>/dev/null; then
    sudo crontab -u "$SERVICE_USER" -r 2>/dev/null || true
    echo "Cron removido."
else
    echo "Usuário '$SERVICE_USER' não existe, nada a remover no cron."
fi

echo ""
echo "[5/6] Removendo o usuário '$SERVICE_USER' e sua home ($PROJECT_DIR)..."
if id "$SERVICE_USER" &>/dev/null; then
    sudo userdel -r "$SERVICE_USER" 2>/dev/null || {
        # Se o userdel -r falhar por algum motivo, remove a conta e a pasta
        # separadamente, pra garantir que nada fique pra trás.
        sudo userdel "$SERVICE_USER" 2>/dev/null || true
        sudo rm -rf "$PROJECT_DIR"
    }
    echo "Usuário '$SERVICE_USER' e $PROJECT_DIR removidos."
else
    echo "Usuário '$SERVICE_USER' não existe. Removendo $PROJECT_DIR, se existir..."
    sudo rm -rf "$PROJECT_DIR"
fi

echo ""
echo "[6/6] Removendo logs em $LOG_DIR..."
sudo rm -rf "$LOG_DIR"
echo "Logs removidos (uma cópia ficou em $PRESERVE_DIR/logs)."

echo ""
echo "=========================================="
echo "   DESINSTALAÇÃO CONCLUÍDA"
echo "=========================================="
echo ""
echo "O Finance Control foi removido deste servidor."
echo ""
echo "Backups e logs preservados em:"
echo "  $PRESERVE_DIR"
echo ""
echo "Se quiser reinstalar no futuro, use o install.sh normalmente —"
echo "ele recria o usuário, o serviço e os containers do zero."
echo ""