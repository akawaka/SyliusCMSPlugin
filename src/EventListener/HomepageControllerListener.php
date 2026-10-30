<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\EventListener;

use AKAWAKA\SyliusCMSPlugin\Controller\Shop\HomepageController;
use AKAWAKA\SyliusCMSPlugin\Repository\HomepageRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Replaces the Sylius homepage by the published CMS homepage of the current channel when it has content for the
 * current locale. Anywhere else (other route, no homepage, draft, locale without blocks) Sylius is left untouched.
 */
#[AsEventListener(event: KernelEvents::CONTROLLER)]
final class HomepageControllerListener
{
    public const string HOMEPAGE_ROUTE = 'sylius_shop_homepage';

    public function __construct(
        private readonly HomepageRepository $homepageRepository,
        private readonly ChannelContextInterface $channelContext,
        private readonly HomepageController $controller,
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || self::HOMEPAGE_ROUTE !== $request->attributes->get('_route')) {
            return;
        }

        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return;
        }

        $homepage = $this->homepageRepository->findOnePublishedForChannel($channel);
        if (null === $homepage || [] === $homepage->getHomepageTranslation($request->getLocale())->getBlocks()) {
            return;
        }

        $request->attributes->set(HomepageController::REQUEST_ATTRIBUTE, $homepage);
        $event->setController($this->controller);
    }
}
