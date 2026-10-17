<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity\Concern;

/** Block tree and SEO fields shared by the translations of pages and of the homepage. */
trait BlocksContentTrait
{
    use BlocksTrait;
    use SeoTrait;
}
