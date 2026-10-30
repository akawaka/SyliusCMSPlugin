<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Controller\Shop;

use AKAWAKA\SyliusCMSPlugin\Entity\HomepageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/** Renders the CMS homepage selected by the HomepageControllerListener in place of the Sylius one. */
final class HomepageController
{
    public const REQUEST_ATTRIBUTE = '_akawaka_cms_homepage';

    public function __construct(private readonly Environment $twig)
    {
    }

    public function __invoke(Request $request): Response
    {
        $homepage = $request->attributes->get(self::REQUEST_ATTRIBUTE);
        if (!$homepage instanceof HomepageInterface) {
            throw new NotFoundHttpException();
        }

        return new Response($this->twig->render('@AkawakaSyliusCMSPlugin/shop/homepage/show.html.twig', [
            'homepage' => $homepage,
            'translation' => $homepage->getHomepageTranslation($request->getLocale()),
        ]));
    }
}
