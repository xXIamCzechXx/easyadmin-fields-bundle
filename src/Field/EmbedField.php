<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class EmbedField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_CALLBACK_URL = 'callbackUrl';
    public const OPTION_DEFAULT_HEIGHT = 'height';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormTypeOption('block_prefix', 'embed') // 'admin/widget/template.html.twig'
            ->addFormTheme('@EasyAdminFields/themes/embed.html.twig')
            ->setTemplatePath('@EasyAdminFields/fields/embed.html.twig')
            ->setFormType(TextType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'by_reference' => false,
                'label_attr.style' => 'display: none;', // Label must be hidden so it does not waste space on page
            ])
            ->hideOnIndex()
            ->setCustomOption(self::OPTION_DEFAULT_HEIGHT, '400px')
            ->setCustomOption(self::OPTION_CALLBACK_URL, null)
            ->addCssClass('embedded-form');
    }

    public function setCallbackUrl(AdminUrlGeneratorInterface $callbackUrl, $action = Crud::PAGE_INDEX): self
    {
        return $this->setCustomOption(self::OPTION_CALLBACK_URL, $callbackUrl->set('embed', 1)->setAction($action)->generateUrl());
    }

    public function setHeight(string $height): self
    {
        return $this->setCustomOption(self::OPTION_DEFAULT_HEIGHT, $height);
    }
}
