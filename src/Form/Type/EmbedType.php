<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\Type;

use Iamczech\EasyAdminFieldsBundle\Field\EmbedField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class EmbedType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'embed';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                EmbedField::OPTION_EMBEDDED_CALLBACK_URL => '',
                EmbedField::OPTION_EMBEDDED_CONTROLLER => '',
                EmbedField::OPTION_EMBEDDED_HEIGHT => '',
                EmbedField::OPTION_EMBEDDED_PAGE_ADD_TEXT => '',
                EmbedField::OPTION_EMBEDDED_PARAMETERS => [],
            ])
            ->setRequired([]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars[EmbedField::OPTION_EMBEDDED_CALLBACK_URL] = $view->vars['value'] = $options[EmbedField::OPTION_EMBEDDED_CALLBACK_URL];
        $view->vars[EmbedField::OPTION_EMBEDDED_CONTROLLER] = $options[EmbedField::OPTION_EMBEDDED_CONTROLLER];
        $view->vars[EmbedField::OPTION_EMBEDDED_HEIGHT] = $options[EmbedField::OPTION_EMBEDDED_HEIGHT];
        $view->vars[EmbedField::OPTION_EMBEDDED_PAGE_ADD_TEXT] = $options[EmbedField::OPTION_EMBEDDED_PAGE_ADD_TEXT];
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
