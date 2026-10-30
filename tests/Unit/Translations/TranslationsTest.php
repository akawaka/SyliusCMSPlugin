<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\Translations;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class TranslationsTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function domains(): iterable
    {
        foreach (['messages', 'validators', 'flashes'] as $domain) {
            yield $domain => [$domain];
        }
    }

    #[DataProvider('domains')]
    public function testEveryKeyIsTranslatedInAllLanguages(string $domain): void
    {
        $english = $this->keys($domain, 'en');
        $french = $this->keys($domain, 'fr');

        self::assertSame([], array_values(array_diff($english, $french)), sprintf('Keys of %s missing in French.', $domain));
        self::assertSame([], array_values(array_diff($french, $english)), sprintf('Keys of %s missing in English.', $domain));
    }

    /** @return list<string> */
    private function keys(string $domain, string $locale): array
    {
        $data = Yaml::parseFile(sprintf('%s/../../../translations/%s.%s.yml', __DIR__, $domain, $locale));
        self::assertIsArray($data);

        $keys = $this->flatten($data);
        sort($keys);

        return $keys;
    }

    /**
     * @param array<mixed> $data
     *
     * @return list<string>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $keys = [];

        foreach ($data as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;
            if (\is_array($value)) {
                array_push($keys, ...$this->flatten($value, $path));
            } else {
                $keys[] = $path;
            }
        }

        return $keys;
    }
}
