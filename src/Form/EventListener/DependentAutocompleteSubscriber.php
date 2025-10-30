<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\EventListener;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Makes it possible to validate dependent fields event though they are not initialized with form data.
 *
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class DependentAutocompleteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData() ?? [];

        $options = $form->getConfig()->getOptions();
        $options['compound'] = false;
        $options['choices'] = is_iterable($data) ? $data : [$data];

        $form->add('autocomplete', EntityType::class, $options);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();
        $options = $form->get('autocomplete')->getConfig()->getOptions();

        if (!isset($data['autocomplete']) || '' === $data['autocomplete']) {
            $options['choices'] = [];
        } else {
            $options['choices'] = $options['em']->getRepository($options['class'])->findBy([
                $options['id_reader']->getIdField() => $data['autocomplete'],
            ]);
        }

        unset($options['em'], $options['loader'], $options['empty_data'], $options['choice_list'], $options['choices_as_values']); // reset some critical lazy options

        $form->add('autocomplete', EntityType::class, $options);
    }
}
