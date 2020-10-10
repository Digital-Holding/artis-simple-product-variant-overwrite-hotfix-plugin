<?php

declare(strict_types=1);

namespace DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Form;

use DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Subscriber\SimpleProductVariantHotfixEventSubscriber;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new SimpleProductVariantHotfixEventSubscriber());
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
