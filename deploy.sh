#!/bin/bash

#######################################
# Gerindra EMS - Zero Downtime Deploy
# Usage: ./deploy.sh [branch]
# Example: ./deploy.sh main
#######################################

set -e

# =====================================================
# CONFIGURATION
# =====================================================
APP_NAME="gerindra"
DEPLOY_PATH="/var/www/${APP_NAME}"
REPO_URL="git@github.com:gangsteryogyakarta/gerinda.git"
BRANCH="${1:-main}"
KEEP_RELEASES=5
PHP_VERSION="8.3"
PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"
SUPERVISOR_WORKERS="${APP_NAME}-worker:*"

# Paths
RELEASES_PATH="${DEPLOY_PATH}/releases"
SHARED_PATH="${DEPLOY_PATH}/shared"
CURRENT_PATH="${DEPLOY_PATH}/current"
RELEASE=$(date +%Y%m%d%H%M%S)
RELEASE_PATH="${RELEASES_PATH}/${RELEASE}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# =====================================================
# HELPER FUNCTIONS
# =====================================================
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# =====================================================
# PRE-FLIGHT CHECKS
# =====================================================
preflight_check() {
    log_info "Running pre-flight checks..."
    
    # Check if running as correct user
    if [ "$EUID" -eq 0 ]; then
        log_error "Please run this script as a non-root user"
        exit 1
    fi
    
    # Check if deploy directory exists
    if [ ! -d "$DEPLOY_PATH" ]; then
        log_error "Deploy path $DEPLOY_PATH does not exist"
        exit 1
    fi
    
    # Check for required commands
    for cmd in git composer npm php; do
        if ! command -v $cmd &> /dev/null; then
            log_error "$cmd is not installed"
            exit 1
        fi
    done
    
    log_success "Pre-flight checks passed"
}

# =====================================================
# DEPLOYMENT STEPS
# =====================================================

step_clone() {
    log_info "[1/9] Cloning repository (branch: ${BRANCH})..."
    
    cd "${RELEASES_PATH}"
    git clone --depth 1 --branch "${BRANCH}" "${REPO_URL}" "${RELEASE}"
    
    log_success "Repository cloned"
}

step_composer() {
    log_info "[2/9] Installing Composer dependencies..."
    
    cd "${RELEASE_PATH}"
    composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --prefer-dist
    
    log_success "Composer dependencies installed"
}

step_npm() {
    log_info "[3/9] Installing NPM dependencies and building assets..."
    
    cd "${RELEASE_PATH}"
    npm ci --silent
    npm run build
    
    log_success "Assets built"
}

step_symlinks() {
    log_info "[4/9] Creating symlinks to shared resources..."
    
    # Link .env
    ln -sf "${SHARED_PATH}/.env" "${RELEASE_PATH}/.env"
    
    # Link storage
    rm -rf "${RELEASE_PATH}/storage"
    ln -sf "${SHARED_PATH}/storage" "${RELEASE_PATH}/storage"
    
    # Link public storage
    rm -rf "${RELEASE_PATH}/public/storage"
    ln -sf "${SHARED_PATH}/storage/app/public" "${RELEASE_PATH}/public/storage"
    
    log_success "Symlinks created"
}

step_migrate() {
    log_info "[5/9] Running database migrations..."
    
    cd "${RELEASE_PATH}"
    php artisan migrate --force
    
    log_success "Migrations completed"
}

step_cache() {
    log_info "[6/9] Caching configuration..."
    
    cd "${RELEASE_PATH}"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan icons:cache 2>/dev/null || true
    
    log_success "Configuration cached"
}

step_switch() {
    log_info "[7/9] Switching to new release..."
    
    # Atomic symlink switch
    ln -sfn "${RELEASE_PATH}" "${CURRENT_PATH}"
    
    log_success "Release switched"
}

step_restart() {
    log_info "[8/9] Restarting services..."
    
    # Reload PHP-FPM
    sudo systemctl reload "${PHP_FPM_SERVICE}"
    
    # Restart queue workers
    sudo supervisorctl restart "${SUPERVISOR_WORKERS}" 2>/dev/null || true
    
    # Give workers time to restart
    sleep 3
    
    log_success "Services restarted"
}

step_cleanup() {
    log_info "[9/9] Cleaning up old releases..."
    
    cd "${RELEASES_PATH}"
    ls -dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
    
    log_success "Old releases cleaned up"
}

# =====================================================
# HEALTH CHECK
# =====================================================
health_check() {
    log_info "Running health check..."
    
    sleep 2
    
    # Try to get health endpoint
    if command -v curl &> /dev/null; then
        HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "http://localhost/health" 2>/dev/null || echo "000")
        
        if [ "$HTTP_STATUS" = "200" ]; then
            log_success "Health check passed (HTTP ${HTTP_STATUS})"
        else
            log_warning "Health check returned HTTP ${HTTP_STATUS}"
            log_warning "Please verify the deployment manually"
        fi
    else
        log_warning "curl not found, skipping health check"
    fi
}

# =====================================================
# ROLLBACK
# =====================================================
rollback() {
    log_warning "Rolling back to previous release..."
    
    PREVIOUS=$(ls -dt "${RELEASES_PATH}"/*/ | sed -n '2p' | xargs basename 2>/dev/null)
    
    if [ -z "${PREVIOUS}" ]; then
        log_error "No previous release found to rollback"
        exit 1
    fi
    
    ln -sfn "${RELEASES_PATH}/${PREVIOUS}" "${CURRENT_PATH}"
    sudo systemctl reload "${PHP_FPM_SERVICE}"
    sudo supervisorctl restart "${SUPERVISOR_WORKERS}" 2>/dev/null || true
    
    log_success "Rolled back to ${PREVIOUS}"
}

# =====================================================
# MAIN
# =====================================================
main() {
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║      GERINDRA EMS - ZERO DOWNTIME DEPLOYMENT           ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Branch:  ${BRANCH}"
    echo "Release: ${RELEASE}"
    echo "Path:    ${RELEASE_PATH}"
    echo ""
    
    # Handle rollback command
    if [ "${1}" = "rollback" ]; then
        rollback
        exit 0
    fi
    
    # Run deployment
    START_TIME=$(date +%s)
    
    preflight_check
    step_clone
    step_composer
    step_npm
    step_symlinks
    step_migrate
    step_cache
    step_switch
    step_restart
    step_cleanup
    health_check
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║              DEPLOYMENT SUCCESSFUL!                    ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Release:  ${RELEASE}"
    echo "Branch:   ${BRANCH}"
    echo "Duration: ${DURATION} seconds"
    echo ""
    echo "To rollback: ./deploy.sh rollback"
    echo ""
}

# Run main function
main "$@"
