<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\EventListener;

use AKAWAKA\SyliusCMSPlugin\Controller\Shop\HomepageController;
use AKAWAKA\SyliusCMSPlugin\EventListener\HomepageControllerListener;
use AKAWAKA\SyliusCMSPlugin\Repository\HomepageRepository;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class HomepageControllerListenerTest extends TestCase
{
    public function testOtherRoutesAreNotTouched(): void
    {
        $repository = $this->createMock(HomepageRepository::class);
        $repository->expects(self::never())->method('findOnePublishedForChannel');

        $event = $this->event('sylius_shop_product_show');
        $original = $event->getController();

        $this->listener($repository)($event);

        self::assertSame($original, $event->getController());
    }

    public function testTheHomepageIsLeftToSyliusWhenTheChannelIsUnknown(): void
    {
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willThrowException(new ChannelNotFoundException());
        $repository = $this->createMock(HomepageRepository::class);
        $repository->expects(self::never())->method('findOnePublishedForChannel');

        $event = $this->event(HomepageControllerListener::HOMEPAGE_ROUTE);
        $original = $event->getController();

        (new HomepageControllerListener($repository, $channelContext, new HomepageController(new Environment(new ArrayLoader()))))($event);

        self::assertSame($original, $event->getController());
    }

    private function listener(HomepageRepository $repository): HomepageControllerListener
    {
        return new HomepageControllerListener($repository, $this->createMock(ChannelContextInterface::class), new HomepageController(new Environment(new ArrayLoader())));
    }

    private function event(string $route): ControllerEvent
    {
        $request = new Request(attributes: ['_route' => $route]);

        return new ControllerEvent($this->createMock(HttpKernelInterface::class), static fn (): null => null, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
