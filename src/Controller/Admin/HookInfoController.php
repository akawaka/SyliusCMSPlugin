<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Controller\Admin;

use AKAWAKA\SyliusCMSPlugin\Hook\CmsHookablesRegistry;
use AKAWAKA\SyliusCMSPlugin\Hook\HookPrefixes;
use Sylius\TwigHooks\Hookable\DisabledHookable;
use Sylius\TwigHooks\Hookable\HookableComponent;
use Sylius\TwigHooks\Hookable\HookableTemplate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** The hookables already attached to a hook, with their priority, to help choosing where a content block goes. */
final class HookInfoController
{
    public function __construct(
        private readonly CmsHookablesRegistry $registry,
        private readonly HookPrefixes $prefixes,
    ) {
    }

    public function __invoke(string $name): JsonResponse
    {
        if (!$this->prefixes->allows($name)) {
            throw new NotFoundHttpException();
        }

        $hookables = [];
        foreach ($this->registry->getConfiguredFor($name) as $hookable) {
            $hookables[] = [
                'name' => $hookable->name,
                'type' => match (true) {
                    $hookable instanceof HookableTemplate => 'template',
                    $hookable instanceof HookableComponent => 'component',
                    $hookable instanceof DisabledHookable => 'disabled',
                    default => 'other',
                },
                'target' => match (true) {
                    $hookable instanceof HookableTemplate => $hookable->template,
                    $hookable instanceof HookableComponent => $hookable->component,
                    default => '',
                },
                'priority' => $hookable->priority(),
            ];
        }

        return new JsonResponse(['hook' => $name, 'hookables' => $hookables]);
    }
}
