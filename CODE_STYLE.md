# Code Style Guide

This project uses **Laravel Pint** for code formatting and style checking, not PHPCS.

## Using Laravel Pint

### Format Code
```bash
# Format all files
php artisan pint

# Or using vendor binary
./vendor/bin/pint
```

### Check Code Style (without fixing)
```bash
php artisan pint --test
```

### Format Specific Files/Directories
```bash
php artisan pint app/
php artisan pint database/
php artisan pint resources/
```

## Configuration

Pint configuration is in `pint.json` in the project root. It uses the Laravel preset with custom rules.

## Note

If you see errors about `phpcs` not being recognized, use Pint instead:
- ❌ `phpcs` (not available)
- ✅ `php artisan pint` (use this)

