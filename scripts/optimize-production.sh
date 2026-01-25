#!/bin/bash

#######################################
# Gerindra EMS - Production Optimization Script
# Run this after deployment to optimize the application
#######################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Gerindra EMS - Production Optimization${NC}"
echo "=============================================="
echo ""

# Check if we're in the Laravel root
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Run this from Laravel root directory.${NC}"
    exit 1
fi

#-----------------------------------------------
# Step 1: Composer Optimization
#-----------------------------------------------
echo -e "${YELLOW}[1/7] Optimizing Composer autoloader...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Composer optimized${NC}"
echo ""

#-----------------------------------------------
# Step 2: NPM Build (if package.json exists)
#-----------------------------------------------
if [ -f "package.json" ]; then
    echo -e "${YELLOW}[2/7] Building frontend assets...${NC}"
    npm ci --silent
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
else
    echo -e "${YELLOW}[2/7] Skipping asset build (no package.json)${NC}"
fi
echo ""

#-----------------------------------------------
# Step 3: Clear old caches
#-----------------------------------------------
echo -e "${YELLOW}[3/7] Clearing old caches...${NC}"
php artisan optimize:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

#-----------------------------------------------
# Step 4: Run migrations
#-----------------------------------------------
echo -e "${YELLOW}[4/7] Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations complete${NC}"
echo ""

#-----------------------------------------------
# Step 5: Cache configuration
#-----------------------------------------------
echo -e "${YELLOW}[5/7] Caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✓ Configuration cached${NC}"
echo ""

#-----------------------------------------------
# Step 6: Optimize Laravel
#-----------------------------------------------
echo -e "${YELLOW}[6/7] Running Laravel optimize...${NC}"
php artisan optimize
echo -e "${GREEN}✓ Laravel optimized${NC}"
echo ""

#-----------------------------------------------
# Step 7: Create storage link
#-----------------------------------------------
echo -e "${YELLOW}[7/7] Creating storage link...${NC}"
php artisan storage:link --force 2>/dev/null || true
echo -e "${GREEN}✓ Storage linked${NC}"
echo ""

#-----------------------------------------------
# Summary
#-----------------------------------------------
echo "=============================================="
echo -e "${GREEN}✅ Optimization Complete!${NC}"
echo ""
echo "Next steps:"
echo "  1. Restart PHP-FPM: sudo systemctl reload php8.3-fpm"
echo "  2. Restart queue workers: sudo supervisorctl restart gerindra-worker:*"
echo "  3. Verify health: curl https://ems.gerindra.or.id/health"
echo ""
