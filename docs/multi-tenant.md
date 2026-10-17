# Multi-tenant compatibility sheet

The plugin never deals with organizations: it only uses the ORM and overridable resource classes, so a multi-tenant platform can subclass the entities (trait adding `organization_id`) and repoint `classes.model`.

## Entities

| Entity | Table | Resource (`sylius_resource`) | Role |
|---|---|---|---|
| `Page` | `akawaka_cms_page` | `akawaka_cms.page` | customer data |
| `PageTranslation` | `akawaka_cms_page_translation` | `akawaka_cms.page` (translation) | child |
| `Homepage` | `akawaka_cms_homepage` | `akawaka_cms.homepage` | customer data (one per channel) |
| `HomepageTranslation` | `akawaka_cms_homepage_translation` | `akawaka_cms.homepage` (translation) | child |
| `ContentBlock` | `akawaka_cms_content_block` | `akawaka_cms.content_block` | customer data |
| `ContentBlockTranslation` | `akawaka_cms_content_block_translation` | `akawaka_cms.content_block` (translation) | child |

Every resource declares `model`, `interface`, `repository` and `factory`; code is typed on the interfaces. Entities are not `final`, mapped with ORM attributes, relations target `ChannelInterface`. `createTranslation()` of the translatable entities instantiates the translation class (Sylius `TranslatableTrait` convention): override the entity and that method together.

## Uniqueness

| Constraint | Scope today | Expected on the platform |
|---|---|---|
| `Page.code`, `ContentBlock.code` | global | `(organization_id, code)` |
| `PageTranslation (locale, slug)` | global | `(organization_id, locale, slug)` |
| `Homepage.channel` | one homepage per channel | unchanged |
| `(translatable_id, locale)` on translations | per parent | unchanged |

Pages and blocks are attached to several channels, so these codes cannot be scoped by channel. They are declared in the ORM mapping (attributes), not in an opaque SQL migration. Storefront lookups go through the repositories, filtered by the current channel.

## Sequences
None.

## SQL outside the ORM
None. All queries are QueryBuilder queries in the resource repositories (`src/Repository`). Only the Doctrine migrations contain DDL.

## Cache
One pool, used by `Preview\PreviewStore` for unsaved page previews. Its service id is configurable (`akawaka_sylius_cms.preview_cache_pool`, default `cache.app`) so it can be replaced or decorated. Key: `akawaka_cms_preview_<128-bit random token>`, TTL 1800 s, the token being the only access control. No static state; `ContentBlockHookablesProvider` is reset on `kernel.reset`.

## Files
None stored. Images are URLs typed in the builder.

## Secrets
None.

## Webhooks
None. Public routes (`/{_locale}/pages/{slug}`, `/{_locale}/cms-preview/{token}`, homepage) resolve the channel with `ChannelContextInterface`.

## Async / scheduled
No message, command or scheduled task.

## Admin
Menu entries added through the Sylius menu event (`Menu\AdminMenuListener`): homepage, content blocks, pages. All resource routes use `permission: true`. There is no instance-wide settings page: nothing is global except the `hook_prefixes` container parameter (structure, not customer data). Admin grids are not filtered by channel; isolation relies on the platform's ORM filter. A content block without channel is displayed on all channels of the organization.

## Outgoing calls
No server-side call and no telemetry. The video block renders YouTube / Vimeo iframes loaded by the visitor's browser.
