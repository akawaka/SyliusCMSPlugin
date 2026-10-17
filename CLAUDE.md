# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Docker Environment (Recommended)
```bash
# Initialize Docker environment and install dependencies
make init

# Initialize database and run migrations
make database-init

# Load fixtures (optional)
make load-fixtures

# Start/stop containers
make up
make down

# Access containers
make php-shell
make node-shell
```

### Traditional Development
```bash
# Frontend setup
(cd vendor/sylius/test-application && yarn install)
(cd vendor/sylius/test-application && yarn build)
vendor/bin/console assets:install

# Database setup
vendor/bin/console doctrine:database:create
vendor/bin/console doctrine:migrations:migrate -n
vendor/bin/console sylius:fixtures:load -n

# Start server
symfony server:start -d
```

### Testing
```bash
# PHPUnit tests
vendor/bin/phpunit
make phpunit  # Docker

# Behat tests (non-JS)
vendor/bin/behat --strict --tags="~@javascript&&~@mink:chromedriver"
make behat  # Docker

# Behat tests (JS scenarios)
# Requires Chrome headless and symfony server
APP_ENV=test symfony server:start --port=8080 --daemon
vendor/bin/behat --strict --tags="@javascript,@mink:chromedriver"
```

### Code Quality
```bash
# PHPStan analysis
vendor/bin/phpstan analyse -c phpstan.neon
make phpstan  # Docker

# Coding standards
vendor/bin/ecs check
make ecs  # Docker
```

### Composer Scripts
```bash
# Database reset with fixtures
composer run database-reset

# Frontend rebuild
composer run frontend-clear

# Complete test app initialization
composer run test-app-init
```

## Architecture

Sylius 2 plugin (`Akawaka\SyliusCMSPlugin`) providing CMS pages with a block-based page builder. See `README.md` for the feature overview and `AGENTS.md` for the coding conventions.

- **Domain**: `src/Entity` (`Page`, `PageTranslation` with the block tree stored as JSON), `src/Enum` (`PageStatus`, `PageTransition`, `BlockType`, ...), publication workflow in `config/app/workflow.yaml`.
- **Blocks**: `src/Block` (`BlockTypeInterface`, `BlockRegistry`, `BlockCatalog`) and `src/Block/Type/*` (one class per block); each has a settings form in `src/Form/Type/Block` and a storefront template in `templates/shop/blocks`.
- **Tree validation / rendering**: `src/Validator/BlockTreeValidator`, `src/Renderer/PageRenderer`, `src/Twig/CmsExtension`.
- **Admin**: Sylius resource + grid (`config/app/*.yaml`), page form in `templates/admin/page`, builder JavaScript in `assets/admin` (Stimulus controllers `builder` and `preview`, pure logic in `assets/admin/builder`).
- **Storefront & preview**: `src/Controller/Shop` (page and preview), `src/Preview` (cache-backed storage of unsaved content), admin preview endpoint in `src/Controller/Admin`.
- **Config**: `src/DependencyInjection` (alias `akawaka_sylius_cms`), `config/services.xml`, `config/routes`.
- **Test application**: `tests/TestApplication` (built on `sylius/test-application`).

### Tests and quality
- `vendor/bin/phpunit` (unit tests in `tests/Unit`, functional tests in `tests/Functional` booting the test application on the `test` database — see README), `npm test` (JavaScript logic), `vendor/bin/ecs check`, `vendor/bin/phpstan analyse -c phpstan.neon` (level max).
- Rebuild the admin assets after JavaScript/SCSS changes: `(cd vendor/sylius/test-application && yarn build)`.

## AI Development Guides

This project includes specialized AI guides to assist with common plugin development tasks:

- **CLEANUP_GUIDE.md** - Guidelines for cleaning up and organizing plugin code
- **RENAME_GUIDE.md** - Step-by-step instructions for renaming plugins and components
- **COMPATIBILITY_GUIDE.md** - Best practices for maintaining compatibility across different Sylius versions

These guides provide detailed instructions and automated workflows to help maintain code quality and ensure proper plugin structure.
