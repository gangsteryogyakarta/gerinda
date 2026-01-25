#!/bin/bash

# =====================================================
# WAHA Deployment Script
# =====================================================

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check Docker
if ! command -v docker &> /dev/null; then
    log_error "Docker is not installed"
    exit 1
fi

# Check Docker Compose
if ! command -v docker-compose &> /dev/null; then
    log_error "Docker Compose is not installed"
    exit 1
fi

# Ensure we are in the project root containing the yaml file
if [ ! -f "docker-compose.waha.yml" ]; then
    log_error "docker-compose.waha.yml not found in current directory"
    exit 1
fi

log_info "Stopping any existing standalone WAHA container..."
docker stop gerindra-waha 2>/dev/null || true
docker rm gerindra-waha 2>/dev/null || true

log_info "Deploying WAHA via Docker Compose..."
# Use -p gerindra to ensure consistent project name across deployments (releases)
docker-compose -f docker-compose.waha.yml -p gerindra up -d

log_success "WAHA container deployed successfully!"
echo ""
echo "Status:"
docker ps | grep gerindra-waha
echo ""
echo "Logs:"
docker logs gerindra-waha --tail 10
