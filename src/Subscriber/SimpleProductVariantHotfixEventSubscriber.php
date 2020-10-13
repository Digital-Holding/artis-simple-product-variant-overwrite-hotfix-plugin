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

        $variantForm = $event->getForm()->get('variant');
        $variantData = $data['variant'];
        foreach ($variantForm->all() as $fieldName => $val) {
            if (!isset($variantData[$fieldName]) && $fieldName !== 'specificationItemValues') {
                $variantForm->remove($fieldName);
            }
        }

        //fix for specification item values
        /** @var ProductVariantInterface */
        $variant = $variantForm->getNormData();

        if (!isset($data['variant']['specificationItemValues'])) {
            $data['variant']['specificationItemValues'] = [];
        }


        /** @var ProductVariantSpecificationItemValuesInterface */
        foreach ($variant->getSpecificationItemValues() as $item)
        {
            $code = $item->getSpecificationItemValueCode();
            $type = $item->getSpecificationItemValue()->getItem()->getType();
            if (is_string($type)) {
                $type = new SpecificationItemValueType($type);
            }
            $val = $this->specificationItemValueResolver->getSpecificationItemValueByType($type, $item, true);
            $field = $this->specificationItemValueResolver->getSpecificationItemValueFieldByType($type, $item);

            //special case for booleans
            if (!isset($data['variant']['specificationItemValues'][$code])) {               
                $data['variant']['specificationItemValues'][$code] = [
                    $field => $val
                ];
            }
        }

        $event->setData($data);
    }
}
