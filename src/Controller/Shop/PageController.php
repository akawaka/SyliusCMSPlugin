<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Controller\Shop;

use AKAWAKA\SyliusCMSPlugin\Repository\PageRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class PageController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly ChannelContextInterface $channelContext,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(string $_locale, string $slug): Response
    {
        $page = $this->pageRepository->findOnePublishedBySlug($slug, $_locale, $this->channelContext->getChannel());
        if (null === $page) {
            throw new NotFoundHttpException();
        }

        return new Response($this->twig->render('@AkawakaSyliusCMSPlugin/shop/page/show.html.twig', [
            'page' => $page,
            'translation' => $page->getPageTranslation($_locale),
        ]));
    }
}
