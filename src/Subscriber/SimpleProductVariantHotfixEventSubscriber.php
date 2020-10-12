<?php

namespace DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SimpleProductVariantHotfixEventSubscriber implements EventSubscriberInterface
{
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

        $event->setData($data);
    }
}
