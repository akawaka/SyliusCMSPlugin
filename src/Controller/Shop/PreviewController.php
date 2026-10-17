<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Controller\Shop;

use Akawaka\SyliusCMSPlugin\Entity\PageTranslationInterface;
use Akawaka\SyliusCMSPlugin\Preview\PreviewStore;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/** Renders unsaved builder content with the real storefront layout. Never indexed, never cached. */
final class PreviewController
{
    /** @param FactoryInterface<PageTranslationInterface> $pageTranslationFactory */
    public function __construct(
        private readonly PreviewStore $store,
        private readonly Environment $twig,
        private readonly FactoryInterface $pageTranslationFactory,
    ) {
    }

    public function __invoke(string $_locale, string $token): Response
    {
        $preview = $this->store->get($token);
        if (null === $preview) {
            throw new NotFoundHttpException();
        }

        $response = new Response($this->twig->render('@AkawakaSyliusCMSPlugin/shop/page/show.html.twig', [
            'translation' => $preview->applyTo($this->newTranslation()),
            'preview' => true,
        ]));
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function newTranslation(): PageTranslationInterface
    {
        $translation = $this->pageTranslationFactory->createNew();
        \assert($translation instanceof PageTranslationInterface);

        return $translation;
    }
}
