<?php

namespace DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Subscriber;

use App\Entity\Product\ProductVariantInterface;
use App\Entity\Product\ProductVariantSpecificationItemInterface;
use App\Entity\Product\ProductVariantSpecificationItemValue;
use App\Entity\Product\ProductVariantSpecificationItemValueInterface;
use App\Entity\Product\ProductVariantSpecificationItemValues;
use App\Entity\Product\ProductVariantSpecificationItemValuesInterface;
use App\Factory\Product\DHVariantSpecificationItemValueViewFactoryInterface;
use DH\Artis\Product\Specification\SpecificationItem\SpecificationItemValueResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use DH\Artis\Product\Specification\SpecificationItem\SpecificationItemValueType;
use PhpSpec\Specification;

class SimpleProductVariantHotfixEventSubscriber implements EventSubscriberInterface
{
    /** @var SpecificationItemValueResolverInterface */
    protected $specificationItemValueResolver;

    public function __construct(SpecificationItemValueResolverInterface $specificationItemValueResolver)
    {
        $this->specificationItemValueResolver = $specificationItemValueResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $product = $event->getForm()->getNormData();
        if (!$product->isSimple()) {
            return;
        }

        $variantForm = $event->getForm()->get('variant');
        $variantData = $data['variant'];

        //special case for 3 fields hardcoded in sylius
        foreach (['on_hand', 'tracked', 'version'] as $hardcodedEntry) {
            if (!isset($variantData[$hardcodedEntry])) {
                $variantData[$hardcodedEntry] = false;
            }
        }

        foreach ($variantForm->all() as $fieldName => $val) {
            if (!isset($variantData[$fieldName]) && $fieldName !== 'specificationItemValues') {
                $variantForm->remove($fieldName);
            }
        }

        //fix for specification item values
        /** @var ProductVariantInterface */
        $variant = $variantForm->getNormData();
        $existing = $variant->getSpecificationItemValues(); //cannot use empty due to collection
        if (count($existing) !== 0 && !isset($data['variant']['specificationItemValues'])) {
            $data['variant']['specificationItemValues'] = [];
        }


        /** @var ProductVariantSpecificationItemValuesInterface */
        foreach ($variant->getSpecificationItemValues() as $item)
        {
            $type = $item->getSpecificationItemValue()->getItem()->getType();
            if (is_string($type)) {
                $type = new SpecificationItemValueType($type);
            }

            $field = $this->specificationItemValueResolver->getSpecificationItemValueFieldByType($type, $item);

            $code = $item->getSpecificationItemValueCode();
            $itemId =  $item->getSpecificationItemValue()->getItem()->getId();
            $val = $this->specificationItemValueResolver->getSpecificationItemValueByType($type, $item, true);

            if ($code) {
                //special case for booleans
                if (!isset($data['variant']['specificationItemValues'][$code])) {
                    $data['variant']['specificationItemValues'][$code] = [
                        $field => $val,
                    ];
                }
            } else {
                $data['variant']['specificationItemValues'][] = [$field => $val];
            }
        }

        $event->setData($data);
    }
}
