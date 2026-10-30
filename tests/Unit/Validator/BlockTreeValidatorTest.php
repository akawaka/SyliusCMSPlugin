<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\Validator;

use AKAWAKA\SyliusCMSPlugin\Validator\BlockTreeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Tests\AKAWAKA\SyliusCMSPlugin\Unit\BlockFactoryTrait;

final class BlockTreeValidatorTest extends TestCase
{
    use BlockFactoryTrait;

    private BlockTreeValidator $validator;

    protected function setUp(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension(new CsrfTokenManager()))
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
        ;

        $this->validator = new BlockTreeValidator($this->createRegistry(), $formFactory);
    }

    /**
     * @param list<\AKAWAKA\SyliusCMSPlugin\Validator\BlockTreeViolation> $violations
     *
     * @return list<string>
     */
    private function messages(array $violations): array
    {
        return array_map(static fn ($violation): string => $violation->message, $violations);
    }

    public function testAValidTreePasses(): void
    {
        $tree = [
            ['id' => 'a', 'type' => 'rich_text', 'props' => ['html' => '<p>Hi</p>']],
            ['id' => 'b', 'type' => 'columns', 'props' => ['columns' => 2], 'children' => [
                ['id' => 'c', 'type' => 'column', 'children' => [['id' => 'd', 'type' => 'button', 'props' => ['label' => 'Go', 'url' => '/x', 'style' => 'primary', 'alignment' => 'left']]]],
                ['id' => 'e', 'type' => 'column'],
            ]],
        ];

        self::assertSame([], $this->validator->validate($tree));
        self::assertSame([], $this->validator->validate([]));
    }

    /** @return iterable<string, array{mixed, string}> */
    public static function invalidTrees(): iterable
    {
        yield 'not a list' => [['a' => 1], 'akawaka_cms.block_tree.invalid_tree'];
        yield 'unknown type' => [[['id' => 'a', 'type' => 'nope']], 'akawaka_cms.block_tree.unknown_type'];
        yield 'missing id' => [[['type' => 'rich_text']], 'akawaka_cms.block_tree.missing_id'];
        yield 'duplicate id' => [[['id' => 'a', 'type' => 'rich_text'], ['id' => 'a', 'type' => 'rich_text']], 'akawaka_cms.block_tree.duplicate_id'];
        yield 'extra key' => [[['id' => 'a', 'type' => 'rich_text', 'foo' => 1]], 'akawaka_cms.block_tree.unknown_keys'];
        yield 'column at root' => [[['id' => 'a', 'type' => 'column']], 'akawaka_cms.block_tree.internal_at_root'];
        yield 'child in a leaf' => [[['id' => 'a', 'type' => 'rich_text', 'children' => [['id' => 'b', 'type' => 'rich_text']]]], 'akawaka_cms.block_tree.child_not_allowed'];
        yield 'block directly in columns' => [[['id' => 'a', 'type' => 'columns', 'children' => [['id' => 'b', 'type' => 'rich_text']]]], 'akawaka_cms.block_tree.child_not_allowed'];
        yield 'missing required prop' => [[['id' => 'a', 'type' => 'image', 'props' => ['alt' => 'x']]], 'akawaka_cms.block_tree.invalid_prop'];
        yield 'extra prop' => [[['id' => 'a', 'type' => 'image', 'props' => ['src' => 'x', 'evil' => 1]]], 'akawaka_cms.block_tree.invalid_prop'];
        yield 'bad enum' => [[['id' => 'a', 'type' => 'button', 'props' => ['label' => 'x', 'url' => 'y', 'style' => 'neon', 'alignment' => 'left']]], 'akawaka_cms.block_tree.invalid_prop'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTrees')]
    public function testInvalidTreesAreRejected(mixed $tree, string $expectedMessage): void
    {
        $messages = array_map(static fn ($violation): string => $violation->message, $this->validator->validate($tree));

        self::assertContains($expectedMessage, $messages);
    }

    public function testTooDeepTreesAreRejected(): void
    {
        $node = ['id' => 'leaf', 'type' => 'rich_text'];
        for ($i = 0; $i < BlockTreeValidator::MAX_DEPTH; ++$i) {
            $node = ['id' => 'c' . $i, 'type' => 'column', 'children' => [$node]];
        }

        $messages = array_map(static fn ($violation): string => $violation->message, $this->validator->validate([['id' => 'r', 'type' => 'columns', 'children' => [$node]]]));

        self::assertContains('akawaka_cms.block_tree.too_deep', $messages);
    }
}
