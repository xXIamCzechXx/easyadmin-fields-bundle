<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\Type;

use Iamczech\EasyAdminFieldsBundle\Field\QrField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class QrType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'qr';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                QrField::OPTION_QR => '',
                QrField::OPTION_URL => '',
                QrField::OPTION_ROUTE => '',
                QrField::OPTION_LOGO => null,
                QrField::OPTION_LABEL => null,
                QrField::OPTION_FONT => null,
                QrField::OPTION_TARGET => '_blank',
            ])
            ->setRequired([])
            ->setAllowedTypes(QrField::OPTION_QR, ['string'])
            ->setAllowedTypes(QrField::OPTION_URL, ['string'])
            ->setAllowedTypes(QrField::OPTION_ROUTE, ['string'])
            ->setAllowedTypes(QrField::OPTION_LOGO, ['string', 'null'])
            ->setAllowedTypes(QrField::OPTION_LABEL, ['string', 'null'])
            ->setAllowedTypes(QrField::OPTION_FONT, ['string', 'null'])
            ->setAllowedTypes(QrField::OPTION_TARGET, ['string']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars[QrField::OPTION_URL] = $view->vars['value'] = $options[QrField::OPTION_URL];
        $view->vars[QrField::OPTION_QR] = $options[QrField::OPTION_QR];
        $view->vars[QrField::OPTION_TARGET] = $options[QrField::OPTION_TARGET];
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
