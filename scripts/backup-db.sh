#!/bin/bash

#######################################
# Gerindra EMS - Database Backup Script
# 
# Usage: ./backup-db.sh [full|incremental]
# 
# Schedule with cron:
# 0 2 * * * /var/www/gerindra/scripts/backup-db.sh full >> /var/log/gerindra-backup.log 2>&1
#######################################

set -e

# =====================================================
# CONFIGURATION
# =====================================================
BACKUP_TYPE="${1:-full}"
APP_NAME="gerindra"
BACKUP_DIR="/var/backups/${APP_NAME}"
DATE=$(date +%Y%m%d_%H%M%S)
DAY_OF_WEEK=$(date +%u)
RETENTION_DAYS=30

# Database credentials (from .env)
ENV_FILE="/var/www/${APP_NAME}/current/.env"
if [ -f "$ENV_FILE" ]; then
    source <(grep -E '^DB_' "$ENV_FILE" | sed 's/^/export /')
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-gerindra_ems}"
DB_USERNAME="${DB_USERNAME:-gerindra_user}"
DB_PASSWORD="${DB_PASSWORD}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# =====================================================
# HELPER FUNCTIONS
# =====================================================
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# =====================================================
# BACKUP FUNCTIONS
# =====================================================

create_backup_dir() {
    mkdir -p "${BACKUP_DIR}/daily"
    mkdir -p "${BACKUP_DIR}/weekly"
    mkdir -p "${BACKUP_DIR}/monthly"
}

full_backup() {
    log "Starting full database backup..."
    
    BACKUP_FILE="${BACKUP_DIR}/daily/${APP_NAME}_${DATE}.sql.gz"
    
    # Create backup with mysqldump
    mysqldump \
        --host="${DB_HOST}" \
        --port="${DB_PORT}" \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-table \
        --complete-insert \
        "${DB_DATABASE}" | gzip > "${BACKUP_FILE}"
    
    # Get backup size
    BACKUP_SIZE=$(du -h "${BACKUP_FILE}" | cut -f1)
    
    log_success "Backup created: ${BACKUP_FILE} (${BACKUP_SIZE})"
    
    # Create weekly backup on Sunday
    if [ "${DAY_OF_WEEK}" -eq 7 ]; then
        cp "${BACKUP_FILE}" "${BACKUP_DIR}/weekly/"
        log "Weekly backup created"
    fi
    
    # Create monthly backup on 1st
    if [ "$(date +%d)" -eq 01 ]; then
        cp "${BACKUP_FILE}" "${BACKUP_DIR}/monthly/"
        log "Monthly backup created"
    fi
    
    echo "${BACKUP_FILE}"
}

backup_uploads() {
    log "Backing up uploads..."
    
    UPLOADS_DIR="/var/www/${APP_NAME}/shared/storage/app/public"
    UPLOADS_BACKUP="${BACKUP_DIR}/daily/${APP_NAME}_uploads_${DATE}.tar.gz"
    
    if [ -d "${UPLOADS_DIR}" ]; then
        tar -czf "${UPLOADS_BACKUP}" -C "${UPLOADS_DIR}" .
        BACKUP_SIZE=$(du -h "${UPLOADS_BACKUP}" | cut -f1)
        log_success "Uploads backup created: ${UPLOADS_BACKUP} (${BACKUP_SIZE})"
    else
        log "Uploads directory not found, skipping"
    fi
}

cleanup_old_backups() {
    log "Cleaning up old backups..."
    
    # Remove daily backups older than RETENTION_DAYS
    find "${BACKUP_DIR}/daily" -type f -mtime +${RETENTION_DAYS} -delete
    
    # Remove weekly backups older than 90 days
    find "${BACKUP_DIR}/weekly" -type f -mtime +90 -delete
    
    # Remove monthly backups older than 365 days
    find "${BACKUP_DIR}/monthly" -type f -mtime +365 -delete
    
    log_success "Old backups cleaned up"
}

verify_backup() {
    local backup_file="$1"
    
    log "Verifying backup integrity..."
    
    if [ ! -f "${backup_file}" ]; then
        log_error "Backup file not found: ${backup_file}"
        return 1
    fi
    
    # Test gzip integrity
    if gzip -t "${backup_file}" 2>/dev/null; then
        log_success "Backup verified successfully"
        return 0
    else
        log_error "Backup verification failed!"
        return 1
    fi
}

send_notification() {
    local status="$1"
    local message="$2"
    
    # Telegram notification (if configured)
    if [ -n "${TELEGRAM_BOT_TOKEN}" ] && [ -n "${TELEGRAM_CHAT_ID}" ]; then
        curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
            -d chat_id="${TELEGRAM_CHAT_ID}" \
            -d text="🗄️ Backup ${status}: ${message}" \
            > /dev/null 2>&1 || true
    fi
}

# =====================================================
# MAIN
# =====================================================
main() {
    log "==========================================="
    log "Gerindra EMS - Database Backup"
    log "==========================================="
    log "Type: ${BACKUP_TYPE}"
    log "Database: ${DB_DATABASE}"
    log ""
    
    START_TIME=$(date +%s)
    
    # Create directories
    create_backup_dir
    
    # Run backup
    case "${BACKUP_TYPE}" in
        full)
            BACKUP_FILE=$(full_backup)
            backup_uploads
            ;;
        db-only)
            BACKUP_FILE=$(full_backup)
            ;;
        uploads-only)
            backup_uploads
            BACKUP_FILE=""
            ;;
        *)
            log_error "Unknown backup type: ${BACKUP_TYPE}"
            exit 1
            ;;
    esac
    
    # Verify backup
    if [ -n "${BACKUP_FILE}" ]; then
        verify_backup "${BACKUP_FILE}"
    fi
    
    # Cleanup old backups
    cleanup_old_backups
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    log ""
    log "==========================================="
    log_success "Backup completed in ${DURATION} seconds"
    log "==========================================="
    
    # Send notification
    send_notification "SUCCESS" "Database backup completed (${DURATION}s)"
}

# Run main
main
