<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit\Preview;

use Akawaka\SyliusCMSPlugin\Preview\PagePreview;
use Akawaka\SyliusCMSPlugin\Preview\PreviewStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PreviewStoreTest extends TestCase
{
    private PreviewStore $store;

    protected function setUp(): void
    {
        $this->store = new PreviewStore(new ArrayAdapter());
    }

    public function testItStoresAndRetrievesAPreview(): void
    {
        $token = $this->store->create($this->preview('First'));

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $token);
        $loaded = $this->store->get($token);
        self::assertNotNull($loaded);
        self::assertSame('First', $loaded->title);
        self::assertSame('fr_FR', $loaded->locale);
        self::assertSame([['id' => 'a', 'type' => 'rich_text']], $loaded->blocks);
    }

    public function testItUpdatesAnExistingTokenButNeverCreatesOneFromClientInput(): void
    {
        $token = $this->store->create($this->preview('First'));

        self::assertTrue($this->store->update($token, $this->preview('Second')));
        self::assertSame('Second', $this->store->get($token)?->title);

        $forged = str_repeat('a', 32);
        self::assertFalse($this->store->update($forged, $this->preview('Evil')));
        self::assertNull($this->store->get($forged));
    }

    public function testMalformedTokensAreRejected(): void
    {
        self::assertNull($this->store->get('../etc/passwd'));
        self::assertNull($this->store->get(''));
        self::assertFalse($this->store->update('not-a-token', $this->preview('x')));
    }

    public function testItBuildsATransientTranslation(): void
    {
        $translation = $this->preview('Hello')->toTranslation();

        self::assertSame('Hello', $translation->getTitle());
        self::assertSame('fr_FR', $translation->getLocale());
        self::assertCount(1, $translation->getBlocks());
    }

    public function testBlankFieldsBecomeNullAndInvalidBlocksAreDropped(): void
    {
        $preview = PagePreview::fromArray(['locale' => 'en_US', 'title' => '', 'blocks' => [['id' => 'a'], 'junk', 3]]);

        self::assertNull($preview->title);
        self::assertSame([['id' => 'a']], $preview->blocks);
    }

    private function preview(string $title): PagePreview
    {
        return new PagePreview('fr_FR', $title, 'slug', null, null, null, [['id' => 'a', 'type' => 'rich_text']]);
    }
}
