# AGENTS.md

This file provides guidance to coding agents (Claude Code, etc.) when working with code in this repository.

## Development Commands

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

# Database reset with fixtures / frontend rebuild / complete test app initialization
composer run database-reset
composer run frontend-clear
composer run test-app-init
```

## Architecture

Sylius 2 plugin (`Akawaka\SyliusCMSPlugin`) providing CMS pages with a block-based page builder. See `README.md` for the feature overview; the coding conventions are in the "Project rules" section below.

- **Domain**: `src/Entity` (`Page`, `PageTranslation` with the block tree stored as JSON), `src/Enum` (`PageStatus`, `PageTransition`, `BlockType`, ...), publication workflow in `config/app/workflow.yaml`.
- **Homepage**: `Homepage` / `HomepageTranslation` (one per channel, same workflow as pages, shared `BlocksContentTrait`); `EventListener/HomepageControllerListener` swaps the controller of `sylius_shop_homepage` for `Controller/Shop/HomepageController` when a published homepage has blocks for the locale (else Sylius is untouched).
- **Content blocks on hooks**: `ContentBlock` (placements JSON `[{hook, priority}]`, channels, workflow) ; `Hook/CmsHookablesRegistry` decorates `sylius_twig_hooks.registry.hookables` and merges the published blocks (`Hook/ContentBlockHookablesProvider`) by priority ; `Hook/HookTreeBuilder` builds the picker tree (`assets/admin/controllers/hook_picker_controller.js`, pure logic in `assets/admin/builder/hooktree.js`) ; `Hook/HookPrefixes` restricts the allowed hooks (`akawaka_sylius_cms.hook_prefixes`).
- **Blocks**: `src/Block` (`BlockTypeInterface`, `BlockRegistry`, `BlockCatalog`) and `src/Block/Type/*` (one class per block); each has a settings form in `src/Form/Type/Block` and a storefront template in `templates/shop/blocks`.
- **Tree validation / rendering**: `src/Validator/BlockTreeValidator`, `src/Renderer/PageRenderer`, `src/Twig/CmsExtension`.
- **Admin**: Sylius resource + grid (`config/app/*.yaml`), page form in `templates/admin/page`, builder JavaScript in `assets/admin` (Stimulus controllers `builder` and `preview`, pure logic in `assets/admin/builder`).
- **Storefront & preview**: `src/Controller/Shop` (page and preview), `src/Preview` (cache-backed storage of unsaved content), admin preview endpoint in `src/Controller/Admin`.
- **Config**: `src/DependencyInjection` (alias `akawaka_sylius_cms`), `config/services.xml`, `config/routes`.
- **Multi-tenant**: keep the plugin compatible with the multi-tenant SaaS rules (resources overridable, no SQL outside the ORM, no global state); see `docs/multi-tenant.md`.
- **Test application**: `tests/TestApplication` (built on `sylius/test-application`).

### Tests and quality
- `vendor/bin/phpunit` (unit tests in `tests/Unit`, functional tests in `tests/Functional` booting the test application on the `test` database — see README), `npm test` (JavaScript logic), `vendor/bin/ecs check`, `vendor/bin/phpstan analyse -c phpstan.neon` (level max).
- Rebuild the admin assets after JavaScript/SCSS changes: `(cd vendor/sylius/test-application && yarn build)`.

# Project rules

## Constants and states

- Never use class constants (`public const STATUS_DRAFT = '...'`) to represent a fixed set of values (statuses, types, transitions, modes…). Use a PHP backed **Enum** instead, in `src/Enum/` (e.g. `PageStatus`, `PageTransition`).
- Doctrine columns holding such a value are mapped with `enumType:` and typed with the enum in the entity (never `string`).
- Forms use `EnumType` (or choices built from the enum cases); labels come from a `label()` method on the enum returning a translation key.
- In YAML, reference enum cases with `!php/enum Fqcn::Case->value`, never with hardcoded strings.

## Workflow / state machine

- Any lifecycle status (e.g. page draft/published) is driven by the **Symfony Workflow** component, configured as a `state_machine` under `framework.workflows` in `config/app/workflow.yaml`.
- Places and transition names come from enums (`PageStatus`, `PageTransition`); the marking store is `method` on the `status` property.
- The status is never edited through a free form field: it changes only through transitions (grid `apply_transition` actions and routes using `applyStateMachineTransitionAction`, or `WorkflowInterface::apply()` in code). Guard with `$workflow->can()`.
