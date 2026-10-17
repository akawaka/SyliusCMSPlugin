<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit\Renderer;

use Akawaka\SyliusCMSPlugin\Renderer\PageRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\IdentityTranslator;
use Tests\Akawaka\SyliusCMSPlugin\Unit\BlockFactoryTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class PageRendererTest extends TestCase
{
    use BlockFactoryTrait;

    private PageRenderer $renderer;

    protected function setUp(): void
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../../templates', 'AkawakaSyliusCMSPlugin');

        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new TranslationExtension(new IdentityTranslator()));

        $this->renderer = new PageRenderer($this->createRegistry(), $twig);
    }

    public function testItRendersNestedBlocks(): void
    {
        $html = $this->renderer->render([
            ['id' => 'cols', 'type' => 'columns', 'props' => ['columns' => 2], 'children' => [
                ['id' => 'c1', 'type' => 'column', 'children' => [['id' => 't', 'type' => 'rich_text', 'props' => ['html' => '<p>Hello</p>']]]],
                ['id' => 'c2', 'type' => 'column'],
            ]],
        ]);

        self::assertStringContainsString('data-cms-block="cols"', $html);
        self::assertStringContainsString('data-cms-block="c2"', $html);
        self::assertMatchesRegularExpression('~cms-columns.*cms-column.*<p>Hello</p>~s', $html);
    }

    public function testRichTextIsSanitized(): void
    {
        $html = $this->renderer->render([
            ['id' => 't', 'type' => 'rich_text', 'props' => ['html' => '<p onclick="x()">Hi</p><script>alert(1)</script>']],
        ]);

        self::assertStringContainsString('<p>Hi</p>', $html);
        self::assertStringNotContainsString('script', $html);
        self::assertStringNotContainsString('onclick', $html);
    }

    public function testPropsAreEscaped(): void
    {
        $html = $this->renderer->render([
            ['id' => 'i', 'type' => 'image', 'props' => ['src' => '/a.png', 'alt' => '"><script>x</script>']],
        ]);

        self::assertStringNotContainsString('<script>', $html);
    }

    public function testUnknownBlocksAreSkippedAndDefaultsApplied(): void
    {
        $html = $this->renderer->render([
            ['id' => 'x', 'type' => 'nope'],
            ['id' => 'b', 'type' => 'button', 'props' => ['label' => 'Go', 'url' => '/go']],
        ]);

        self::assertStringNotContainsString('nope', $html);
        self::assertStringContainsString('btn btn-primary', $html);
        self::assertStringContainsString('Go', $html);
    }

    public function testVideoEmbedsYoutube(): void
    {
        $html = $this->renderer->render([['id' => 'v', 'type' => 'video', 'props' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']]]);

        self::assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $html);
    }
}
