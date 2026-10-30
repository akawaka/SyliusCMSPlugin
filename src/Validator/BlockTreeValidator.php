<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Validator;

use AKAWAKA\SyliusCMSPlugin\Block\BlockRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/** Validates the JSON block tree against the registered block types and their prop forms. */
final class BlockTreeValidator
{
    public const MAX_DEPTH = 5;

    public const MAX_BLOCKS = 500;

    private const ALLOWED_KEYS = ['id', 'type', 'props', 'children'];

    public function __construct(
        private readonly BlockRegistry $registry,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /** @return list<BlockTreeViolation> */
    public function validate(mixed $blocks): array
    {
        if (!\is_array($blocks) || !array_is_list($blocks)) {
            return [new BlockTreeViolation('akawaka_cms.block_tree.invalid_tree', '')];
        }

        $violations = [];
        $ids = [];
        $this->validateList($blocks, null, '', 1, $ids, $violations);

        return $violations;
    }

    /**
     * @param array<int, mixed> $blocks
     * @param array<string, true> $ids
     * @param list<BlockTreeViolation> $violations
     */
    private function validateList(array $blocks, ?string $parentType, string $path, int $depth, array &$ids, array &$violations): void
    {
        if ($depth > self::MAX_DEPTH) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.too_deep', $path, ['{{ max }}' => (string) self::MAX_DEPTH]);

            return;
        }

        foreach ($blocks as $index => $block) {
            if (\count($ids) >= self::MAX_BLOCKS) {
                $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.too_many_blocks', $path, ['{{ max }}' => (string) self::MAX_BLOCKS]);

                return;
            }

            $this->validateBlock($block, $parentType, $path . '[' . $index . ']', $depth, $ids, $violations);
        }
    }

    /**
     * @param array<string, true> $ids
     * @param list<BlockTreeViolation> $violations
     */
    private function validateBlock(mixed $block, ?string $parentType, string $path, int $depth, array &$ids, array &$violations): void
    {
        if (!\is_array($block)) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.invalid_block', $path);

            return;
        }

        $extraKeys = array_diff(array_keys($block), self::ALLOWED_KEYS);
        if ([] !== $extraKeys) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.unknown_keys', $path, ['{{ keys }}' => implode(', ', array_map('strval', $extraKeys))]);
        }

        $id = $block['id'] ?? null;
        if (!\is_string($id) || '' === $id) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.missing_id', $path);
        } elseif (isset($ids[$id])) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.duplicate_id', $path, ['{{ id }}' => $id]);
        } else {
            $ids[$id] = true;
        }

        $code = $block['type'] ?? null;
        if (!\is_string($code) || !$this->registry->has($code)) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.unknown_type', $path, ['{{ type }}' => \is_string($code) ? $code : '?']);

            return;
        }

        $type = $this->registry->get($code);
        $this->validatePlacement($code, $parentType, $path, $violations);
        $this->validateProps($type->getFormType(), $block['props'] ?? [], $path, $violations);

        $children = $block['children'] ?? [];
        if (!\is_array($children) || !array_is_list($children)) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.invalid_children', $path);

            return;
        }

        if ([] === $children) {
            return;
        }

        $this->validateList($children, $code, $path . '.children', $depth + 1, $ids, $violations);
    }

    /** @param list<BlockTreeViolation> $violations */
    private function validatePlacement(string $code, ?string $parentType, string $path, array &$violations): void
    {
        if (null === $parentType) {
            if ($this->registry->get($code)->isInternal()) {
                $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.internal_at_root', $path, ['{{ type }}' => $code]);
            }

            return;
        }

        if (!$this->registry->get($parentType)->acceptsChild($code)) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.child_not_allowed', $path, ['{{ type }}' => $code, '{{ parent }}' => $parentType]);
        }
    }

    /** @param list<BlockTreeViolation> $violations */
    private function validateProps(string $formType, mixed $props, string $path, array &$violations): void
    {
        if (!\is_array($props)) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.invalid_props', $path);

            return;
        }

        $form = $this->formFactory->create($formType, null, ['csrf_protection' => false, 'allow_extra_fields' => false]);
        $form->submit($props);

        if ($form->isValid()) {
            return;
        }

        foreach ($this->collectErrors($form) as $field => $message) {
            $violations[] = new BlockTreeViolation('akawaka_cms.block_tree.invalid_prop', $path . '.props' . ('' === $field ? '' : '.' . $field), ['{{ message }}' => $message]);
        }
    }

    /** @return array<string, string> */
    private function collectErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            if ($error instanceof FormError) {
                $errors[''] = $error->getMessage();
            }
        }

        foreach ($form->all() as $name => $child) {
            foreach ($this->collectErrors($child) as $field => $message) {
                $errors[(string) $name . ('' === $field ? '' : '.' . $field)] = $message;
            }
        }

        return $errors;
    }
}
