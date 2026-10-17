<p align="center">
    <img src="doc/logo-akawaka.png" style="width: 500px;">
</p>

<p align="center">
AKAWAKA is a software development company specializing in e-commerce platforms and custom web applications. 
We help businesses evolve or rebuild their platforms with a pragmatic approach: improving existing solutions or starting from scratch, notably with Sylius. 
Our team also handles data migration, SEO preservation, and business continuity. 
With strong expertise in PHP and Symfony, Akawaka builds web solutions tailored to our clients’ business needs.
</p>

<h1 align="center">Akawaka Sylius CMS Plugin</h1>

<p align="center">CMS pages for **Sylius 2.x**, built with a block-based **page builder** in the admin and a **real, live preview** of the storefront on mobile, tablet and desktop.</p>

## Features

- **Pages** with translations (title, slug, SEO fields, content), channels, and a `draft` → `published` workflow (Symfony Workflow).
- **Page builder** (Stimulus + SortableJS): block palette, nested drag & drop canvas, per-block settings, duplicate / remove, undo / redo, fullscreen, widenable settings panel.
- **Rich text** blocks edited with a WYSIWYG ([SunEditor](https://suneditor.com)), sanitized on output.
- **Live preview**: the unsaved content is rendered by your real storefront layout and theme in an iframe sized as a mobile (375 px), tablet (768 px) or desktop (1280 px) screen.
- **Copy the content of one language into another** (undoable).
- **Homepage**: build the storefront homepage per channel and language, with a safe fallback to the Sylius one (see [Homepage](#homepage)).
- **Content blocks on twig hooks**: reusable blocks attached, by priority, to one or several Sylius twig hooks chosen in a tree (see [Content blocks](#content-blocks-on-twig-hooks)).
- **Blocks**: rich text, image, banner, columns, product grid, button, video (YouTube / Vimeo / file), custom HTML. Add your own in a few lines (see [Custom blocks](#custom-blocks)).
- **SEO**: meta title / description, canonical URL, `hreflang` alternates, Open Graph and Twitter tags.
- Translations: English and French.

The page content is stored as a JSON block tree in the page translation, so adding a block type never needs a migration.

## Requirements

- PHP 8.2+
- Sylius 2.0+
- Node / Yarn to build the admin assets

## Installation

### 1. Install the package

Add the [Akawaka Flex recipes](https://github.com/akawaka/symfony-recipes) endpoint to your project, then require the plugin:

```bash
composer config --json extra.symfony.endpoint '["https://api.github.com/repos/akawaka/symfony-recipes/contents/index.json?ref=flex/main","flex://defaults"]'
composer require akawaka/sylius-cms-plugin
```

Symfony Flex registers the bundle and creates `config/packages/akawaka_cms.yaml` and `config/routes/akawaka_cms.yaml` (see [Manual installation](#manual-installation-without-flex) for their content).

### 2. Update the database

```bash
bin/console doctrine:migrations:migrate
```

### 3. Build the admin assets

The page builder is a JavaScript entry that must be part of your **admin** Webpack Encore build. Install its dependencies and add the entry:

```bash
yarn add @hotwired/stimulus sortablejs suneditor
```

```js
// webpack.config.js, in the admin configuration
Encore
    .addEntry('akawaka-cms-admin', './vendor/akawaka/sylius-cms-plugin/assets/admin/entrypoint.js')
```

Then load it in the admin layout, for example with Sylius Twig hooks:

```yaml
# config/packages/sylius_twig_hooks.yaml
sylius_twig_hooks:
    hooks:
        'sylius_admin.base#javascripts':
            akawaka_cms:
                template: 'admin/akawaka_cms/javascripts.html.twig'
        'sylius_admin.base#stylesheets':
            akawaka_cms:
                template: 'admin/akawaka_cms/stylesheets.html.twig'
```

```twig
{# templates/admin/akawaka_cms/javascripts.html.twig #}
{{ encore_entry_script_tags('akawaka-cms-admin', null, 'admin') }}

{# templates/admin/akawaka_cms/stylesheets.html.twig #}
{{ encore_entry_link_tags('akawaka-cms-admin', null, 'admin') }}
```

(`'admin'` is the name of your admin Encore build.) The plugin starts its own Stimulus application, so it does not depend on your `controllers.json`.

### Manual installation (without Flex)

Replace the first step above with:

1. Require the plugin and register the bundle:

    ```bash
    composer require akawaka/sylius-cms-plugin
    ```

    ```php
    // config/bundles.php
    return [
        // ...
        Akawaka\SyliusCMSPlugin\AkawakaSyliusCMSPlugin::class => ['all' => true],
    ];
    ```

1. Import the configuration and the routes:

    ```yaml
    # config/packages/akawaka_cms.yaml
    imports:
        - { resource: '@AkawakaSyliusCMSPlugin/config/config.yaml' }
    ```

    ```yaml
    # config/routes/akawaka_cms.yaml
    akawaka_cms_admin:
        resource: '@AkawakaSyliusCMSPlugin/config/routes/admin.yaml'

    akawaka_cms_shop:
        resource: '@AkawakaSyliusCMSPlugin/config/routes/shop.yaml'
    ```

    The admin routes live under `/admin/cms/...` and the storefront pages under `/{_locale}/pages/{slug}`.

## Usage

1. **CMS → Pages → Create**. Fill the title and slug of each language, pick the channels, and build the content in the **Content** card (one tab per language).
2. Drag blocks from the palette (or click them) into the canvas; drag a block by its bar to reorder or move it into a column. Select a block to edit its settings on the right.
3. Use **Preview** to see the page with your real storefront layout, and the device buttons to check mobile / tablet / desktop. **Copy from** (bottom of the *Blocks* column) copies another language's blocks.
4. **Update**, then **Publish** with the button next to *Back* and *Update* on the edit page (or from the list; it only changes the status, so save your changes first). The eye button of the list opens the published page. The same button exists for the homepage and the content blocks.

### Homepage

**CMS → Homepage** builds the storefront homepage with the same builder and preview. There is one homepage per channel, with content per language. It replaces the default Sylius homepage (`sylius_shop_homepage`) only while it is **published** and has blocks for the visitor's language; otherwise (draft, no content in that language, another channel) the default Sylius homepage is displayed, so nothing breaks while you prepare it. The page gets its own canonical URL, `hreflang` alternates and Open Graph tags; the meta title falls back to the channel name.

No route configuration is needed: the override is done by an event listener, so it does not depend on the order in which the routes are imported.

### Preview

The builder posts the unsaved content to `/admin/cms/preview` (CSRF protected). It is kept in the application cache for 30 minutes under a random 128-bit token, and rendered on the storefront at `/{locale}/cms-preview/{token}` (`noindex`, never cached). The token is the only access control: only a logged-in administrator can create one.


### Content blocks on twig hooks

**CMS → Content blocks** creates reusable content with the same builder and displays it **inside the Sylius twig hooks** of the storefront: a promo banner under the product price, reassurance text in the cart, a block in the footer…

- **Placements**: pick the hooks in a searchable, collapsible **tree** (`sylius_shop.product.show.content.info.summary.prices`, …). A block can be attached to several hooks.
- **Priority**: like any hookable, a block has a priority (higher renders first, −1000…1000). Selecting a hook shows what it already renders with its priority, and where your block will land. Equal priorities render after the existing content.
- **Conditions**: only **published** blocks are displayed; restrict a block to some **channels** (none = all); the content is edited **per language**, a language without blocks displays nothing.
- **Custom hooks**: the tree lists the hooks that have at least one hookable configured. Type the name of any other hook (for example one of your own templates) in the *Custom hook name* field.

By default only the storefront hooks (`sylius_shop`) can be used. To allow the hooks of your application too:

```yaml
# config/packages/akawaka_cms.yaml
akawaka_sylius_cms:
    hook_prefixes: [sylius_shop, app]
```

How it works: the plugin decorates the twig hooks registry (`sylius_twig_hooks.registry.hookables`) so that published blocks are added to the hookables of the hook being rendered, then sorted by priority with the others. They go through the normal pipeline, so they show up in the Symfony profiler and in the HTML debug comments of the dev environment, and a block is loaded once per request. The blocks are static (they do not receive the context of the hook, such as the current product). If the CMS homepage replaces the Sylius homepage, the hooks of `sylius_shop.homepage.index` are not rendered at all.

## Theming

Every block is rendered by a Twig template. Override any of them in your theme or application:

```
templates/bundles/AkawakaSyliusCMSPlugin/shop/blocks/banner.html.twig
templates/bundles/AkawakaSyliusCMSPlugin/shop/page/show.html.twig
```

Block templates receive `block` (`id`, `type`), `props` (the settings merged with the defaults), `data` (extra data from the block type) and `children` (the rendered children). Keep `data-cms-block="{{ block.id }}"` on the root element.

## Custom blocks

A block type is a service implementing `Akawaka\SyliusCMSPlugin\Block\BlockTypeInterface` (usually by extending `AbstractBlockType`). It is registered automatically through autoconfiguration.

```php
// src/Cms/QuoteBlockType.php
namespace App\Cms;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;

final class QuoteBlockType extends AbstractBlockType
{
    public function getCode(): string { return 'quote'; }          // stored in the JSON tree
    public function getIcon(): string { return 'tabler:quote'; }   // Tabler icon of the palette
    public function getFormType(): string { return QuoteBlockFormType::class; }
    public function getDefaults(): array { return ['text' => '', 'author' => '']; }
}
```

```php
// src/Cms/QuoteBlockFormType.php: describes, renders (inspector) and validates the settings
final class QuoteBlockFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, ['label' => 'app.cms.quote.text', 'constraints' => [new NotBlank()]])
            ->add('author', TextType::class, ['label' => 'app.cms.quote.author', 'required' => false])
        ;
    }
}
```

```twig
{# templates/bundles/AkawakaSyliusCMSPlugin/shop/blocks/quote.html.twig #}
<blockquote class="cms-block container" data-cms-block="{{ block.id }}">
    {{ props.text }}<footer>{{ props.author }}</footer>
</blockquote>
```

```yaml
# translations/messages.en.yml
akawaka_cms:
    block:
        quote:
            label: Quote
```

That is all: the block appears in the palette, its settings form is rendered in the inspector, its settings are validated on save, and it is rendered on the storefront. The base class takes the label key from `akawaka_cms.block.<code>.label` and the template from `@AkawakaSyliusCMSPlugin/shop/blocks/<code>.html.twig`; override `getLabel()` / `getTemplate()` to change them.

Other hooks of `BlockTypeInterface`:

| Method | Use |
| --- | --- |
| `getViewData(array $props)` | Extra data for the template (`data`), e.g. load products (see `ProductGridBlockType`). |
| `acceptsChild(string $code)` | Make the block a container (see `ColumnsBlockType`). |
| `isInternal()` | Hide the block from the palette (only created by its parent). |

Mark a field of the settings form with `'attr' => ['data-akawaka-wysiwyg' => '1']` to edit a textarea with the WYSIWYG, as the rich text block does.

## Development

```bash
composer install
(cd vendor/sylius/test-application && yarn install && yarn build)
vendor/bin/console doctrine:database:create && vendor/bin/console doctrine:migrations:migrate -n
vendor/bin/console sylius:fixtures:load -n
symfony server:start        # the test application, admin at /admin

vendor/bin/phpunit --testsuite Unit   # PHP unit tests
APP_ENV=test vendor/bin/phpunit --testsuite Functional   # needs the test database (see below)
npm test                    # JavaScript unit tests (node --test)
vendor/bin/ecs check        # coding standard
vendor/bin/phpstan analyse -c phpstan.neon   # level max
```

The functional tests boot the test application and use the `test` database (`DATABASE_URL` in `tests/TestApplication/.env.test`, override it in your environment). Create it once with `APP_ENV=test vendor/bin/console doctrine:database:create && APP_ENV=test vendor/bin/console doctrine:migrations:migrate -n && APP_ENV=test vendor/bin/console sylius:fixtures:load -n`; the tests clean up the pages they create.

Project conventions are listed in [`AGENTS.md`](AGENTS.md).

## Multi-tenant platforms

The plugin only relies on the ORM and overridable resources; see [docs/multi-tenant.md](docs/multi-tenant.md).

## Limitations

- Images are plain URLs (no integration with a media library yet).
- Blocks are validated against their form type; the preview of an incomplete page is displayed with warnings instead of being blocked.
- The storefront routes are not listed in a sitemap: use your sitemap solution of choice with `akawaka_cms_shop_page_show`.

## License

MIT.
