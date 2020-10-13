<?php

declare(strict_types=1);

namespace DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Form;

use App\Entity\Product\Product;
use App\Factory\Product\DHVariantSpecificationItemValueViewFactoryInterface;
use DH\Artis\Product\Specification\SpecificationItem\SpecificationItemValueResolverInterface;
use DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Subscriber\SimpleProductVariantHotfixEventSubscriber;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    /** @var SpecificationItemValueResolverInterface */
    protected $specificationItemValueResolver;

    public function __construct(SpecificationItemValueResolverInterface $specificationItemValueResolver)
    {
        $this->specificationItemValueResolver = $specificationItemValueResolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new SimpleProductVariantHotfixEventSubscriber($this->specificationItemValueResolver));
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
