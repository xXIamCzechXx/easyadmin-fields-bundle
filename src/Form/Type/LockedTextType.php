<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 * @deprecated use TextField instead
 */
final class LockedTextType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'locked_text'; // locked_text_widget
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['group'])
            ->setAllowedTypes('group', ['string'])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['group'] = $options['group'];
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
