<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ButtonTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class DynamicButtonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        foreach ($builder->all() as $field) {
            $builder->remove($field->getName()); # only as a button to invoke a popup
        }

        $builder->add('button', ButtonType::class, [
            'label' => $options['button_label'],
            'attr' => $options['button_attr'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'button_label' => 'Submit',
            'button_attr' => [],
            'auto_initialize' => false,
            'required' => false,
        ]);

        $resolver
            ->setAllowedTypes('button_label', 'string')
            ->setAllowedTypes('button_attr', 'array')
            ->setAllowedTypes('mapped', 'bool')
            ->setAllowedTypes('auto_initialize', 'bool')
            ->setAllowedTypes('required', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'dynamic_button';
    }
}
