<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit\Form\Type;

use Akawaka\SyliusCMSPlugin\Entity\Page;
use Akawaka\SyliusCMSPlugin\Entity\PageTranslation;
use Akawaka\SyliusCMSPlugin\Form\Type\PageAutocompleteType;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\AdminBundle\Form\Type\TranslatableAutocompleteType;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageAutocompleteTypeTest extends TestCase
{
    public function testItExtendsTheTranslatableAutocomplete(): void
    {
        $type = new PageAutocompleteType(Page::class);

        self::assertSame(TranslatableAutocompleteType::class, $type->getParent());
        self::assertSame('akawaka_cms_page_autocomplete', $type->getBlockPrefix());
    }

    public function testItSearchesTheTitleSlugAndCode(): void
    {
        $options = $this->resolve();

        self::assertSame(Page::class, $options['class']);
        self::assertSame(['code'], $options['entity_fields']);
        self::assertSame(['title', 'slug'], $options['translation_fields']);
    }

    public function testTheLabelContainsTheTitleAndTheSlug(): void
    {
        $label = $this->resolve()['choice_label'];

        self::assertSame('About (/about-us)', $label($this->page('about', 'About', 'about-us')));
    }

    public function testTheLabelFallsBackOnTheCode(): void
    {
        $label = $this->resolve()['choice_label'];

        $page = new Page();
        $page->setCode('about');
        $page->setCurrentLocale('en_US');
        $page->setFallbackLocale('en_US');
        self::assertSame('about', $label($page));
    }

    public function testItOnlyShowsPublishedPagesByDefault(): void
    {
        $builder = $this->createMock(QueryBuilder::class);
        $builder->expects(self::once())->method('andWhere')->with('entity.status = :akawaka_cms_status')->willReturnSelf();
        $builder->expects(self::once())->method('setParameter')->willReturnSelf();
        $builder->expects(self::never())->method('innerJoin');

        $this->resolve()['filter_query']($builder, 'about', $this->createMock(EntityRepository::class));
    }

    public function testItCanIncludeDraftsAndFilterOnAChannel(): void
    {
        $builder = $this->createMock(QueryBuilder::class);
        $builder->expects(self::never())->method('andWhere');
        $builder->expects(self::once())->method('innerJoin')->willReturnSelf();
        $builder->expects(self::once())->method('setParameter')->with('akawaka_cms_channel_code', 'WEB')->willReturnSelf();

        $options = $this->resolve(['only_published' => false, 'channel_code' => 'WEB']);
        $options['filter_query']($builder, 'about', $this->createMock(EntityRepository::class));
    }

    /**
     * @param array<string, mixed> $extraOptions
     *
     * @return array<string, mixed>
     */
    private function resolve(array $extraOptions = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['extra_options' => [], 'filter_query' => null, 'choice_label' => null]);
        (new PageAutocompleteType(Page::class))->configureOptions($resolver);

        return $resolver->resolve(['extra_options' => $extraOptions]);
    }

    private function page(string $code, string $title, string $slug): Page
    {
        $page = new Page();
        $page->setCode($code);
        $page->setCurrentLocale('en_US');
        $page->setFallbackLocale('en_US');
        $translation = $page->getTranslation('en_US');
        \assert($translation instanceof PageTranslation);
        $translation->setTitle($title);
        $translation->setSlug($slug);

        return $page;
    }
}
