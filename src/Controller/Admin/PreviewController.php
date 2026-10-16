<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Controller\Admin;

use Akawaka\SyliusCMSPlugin\Preview\PagePreview;
use Akawaka\SyliusCMSPlugin\Preview\PreviewStore;
use Akawaka\SyliusCMSPlugin\Validator\BlockTreeValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** Stores the unsaved content of the builder and returns the storefront URL that renders it. */
final class PreviewController
{
    public const CSRF_TOKEN_ID = 'akawaka_cms_preview';

    private const MAX_BODY_BYTES = 2_000_000;

    public function __construct(
        private readonly PreviewStore $store,
        private readonly BlockTreeValidator $treeValidator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, (string) $request->headers->get('X-CSRF-TOKEN')))) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $content = $request->getContent();
        if (\strlen($content) > self::MAX_BODY_BYTES) {
            return new JsonResponse(['error' => 'Payload too large.'], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $payload = json_decode($content, true);
        if (!\is_array($payload) || !\is_string($payload['locale'] ?? null) || 1 !== preg_match('/^[a-z]{2,3}(?:_[A-Z]{2})?$/', $payload['locale'])) {
            return new JsonResponse(['error' => 'Invalid payload.'], Response::HTTP_BAD_REQUEST);
        }

        $page = \is_array($payload['page'] ?? null) ? $payload['page'] : [];
        $preview = PagePreview::fromArray(['locale' => $payload['locale'], 'blocks' => $payload['blocks'] ?? []] + $page);

        $token = \is_string($payload['token'] ?? null) ? $payload['token'] : '';
        if (!$this->store->update($token, $preview)) {
            $token = $this->store->create($preview);
        }

        // Incomplete blocks are still previewed; the problems are only reported as warnings.
        $warnings = array_map(
            fn ($violation): string => $this->translator->trans($violation->message, ['{{ path }}' => $violation->path] + $violation->parameters, 'validators'),
            $this->treeValidator->validate($preview->blocks),
        );

        return new JsonResponse([
            'token' => $token,
            'url' => $this->urlGenerator->generate('akawaka_cms_shop_page_preview', ['_locale' => $preview->locale, 'token' => $token]),
            'warnings' => $warnings,
        ]);
    }
}
