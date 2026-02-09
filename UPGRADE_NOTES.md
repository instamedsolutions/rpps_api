# Upgrade Notes: Symfony 7.4, PHP 8.3+, API Platform 4.2

## Summary

This project has been upgraded from:
- Symfony 6.4 → Symfony 7.4
- PHP 8.3 (min) → PHP 8.3+ (ready for 8.5)
- API Platform 3 → API Platform 4.2
- Doctrine DBAL 2.x → DBAL 3.x
- Doctrine ORM 2.x → ORM 3.x
- Doctrine Annotations 1.x → Annotations 2.x

## Changes Made

### 1. Dependency Updates (composer.json)
- Updated all Symfony packages to 7.4.*
- Updated API Platform to ^4.2
- Updated Doctrine DBAL to ^3.0
- Updated Doctrine ORM to ^2.9 || ^3.0
- Updated Doctrine Annotations to ^2.0
- Updated PHP requirement to >=8.3
- Updated .php-version to 8.5

### 2. Configuration Updates
- Updated API Platform exception namespaces in `config/services.yaml`:
  - `ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException` → `ApiPlatform\Validator\Exception\ValidationException`
  - `ApiPlatform\Core\Exception\ItemNotFoundException` → `ApiPlatform\Exception\ItemNotFoundException`

### 3. Code Updates for DBAL 3 Compatibility
- Updated `src/Doctrine/PointWrapper.php`:
  - Removed SQLLogger usage (deprecated in DBAL 3)
  - Fixed `getDriver()->getDatabasePlatform()` → `getDatabasePlatform()`
  - Removed `setFetchMode()` call (deprecated in DBAL 3)

### 4. Documentation
- Updated README.md to reflect new versions

## Compatibility Notes

### Code Already Compatible
The codebase was already well-prepared for these upgrades:
- ✅ Using PHP 8 attributes instead of annotations
- ✅ Using new API Platform Metadata (ApiResource, Get, GetCollection, ApiFilter, ApiProperty)
- ✅ Using DBAL 3 methods like `fetchAllAssociative()` instead of deprecated `fetchAll()`
- ✅ State Providers using new API Platform 4 `ProviderInterface`
- ✅ Filters extending new API Platform 4 classes

### Installation Notes
Due to GitHub rate limiting during the upgrade process, the full `vendor/` directory installation should be completed in your Docker environment or CI/CD pipeline with proper GitHub authentication. The `composer.lock` file has been successfully updated with all correct versions.

To complete the installation:
```bash
# In Docker container
make shell
composer install --ignore-platform-req=ext-redis
```

### Testing
After completing the vendor installation, run the test suite:
```bash
make phpunit
```

## Potential Breaking Changes

### Doctrine DBAL 3
- If any custom code uses DBAL 2-specific methods, they may need updates
- The `wrapper_class` configuration in doctrine.yaml is still present but may need verification

### Symfony 7
- Review any deprecated Symfony 6 features that may have been removed in Symfony 7
- Check for any custom event listeners or subscribers that might need updates

### API Platform 4
- If using any custom decorators or extensions, verify compatibility with API Platform 4
- Check OpenAPI/Swagger documentation generation

## Post-Upgrade Checklist
- [ ] Complete vendor installation
- [ ] Run database migrations if needed
- [ ] Run all tests: `make phpunit`
- [ ] Check application functionality
- [ ] Review logs for deprecation warnings
- [ ] Update CI/CD pipelines if needed
- [ ] Update deployment documentation

## Resources
- [Symfony 7.4 Upgrade Guide](https://symfony.com/doc/current/setup/upgrade_major.html)
- [API Platform 4 Upgrade Guide](https://api-platform.com/docs/core/upgrade-guide/)
- [Doctrine DBAL 3 Upgrade Guide](https://github.com/doctrine/dbal/blob/3.0.x/UPGRADE.md)
