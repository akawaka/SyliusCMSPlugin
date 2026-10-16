<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Controller\Admin;

use Akawaka\SyliusCMSPlugin\Block\BlockRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/** Renders the props form of a block type, loaded by the page builder inspector. */
final class BlockFormController
{
    public function __construct(
        private readonly BlockRegistry $registry,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(string $type): Response
    {
        if (!$this->registry->has($type)) {
            throw new NotFoundHttpException(sprintf('Unknown block type "%s".', $type));
        }

        $form = $this->formFactory->createNamed('props', $this->registry->get($type)->getFormType(), null, ['csrf_protection' => false]);

        return new Response($this->twig->render('@AkawakaSyliusCMSPlugin/admin/builder/block_form.html.twig', ['form' => $form->createView()]));
    }
}
