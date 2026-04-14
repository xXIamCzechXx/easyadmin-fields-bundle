<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Iamczech\EasyAdminFieldsBundle\Form\Type\DynamicButtonType;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class ButtonField implements FieldInterface
{
    use FieldTrait;

    private static array $buttonAttributes = [];

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(DynamicButtonType::class)
            ->setFormTypeOption('mapped', false)
            ->setTemplatePath('@EasyAdminFields/field/button.html.twig')
            ->addFormTheme('@EasyAdminFields/theme/button.html.twig')
            ->addCssFiles('@iamczech/easyadmin-fields/styles/button.css')
            ->addCssClass('field-button')
            ->setDefaultColumns('col-md-6 col-xxl-5');
    }

    public function setButtonUrl(AdminUrlGeneratorInterface $url): static
    {
        $this->setCustomOption('href', $url->generateUrl());

        return $this;
    }

    public function setButtonIcon(string $icon): static
    {
        $this->setCustomOption('icon', $icon);

        return $this;
    }

    public function setModalTemplate(string $template, array $data = []): static
    {
        $this->setCustomOption('modal_template', $template);
        $this->setCustomOption('modal_data', $data);

        return $this;
    }

    public function setButtonLabel($label): static
    {
        $this->setCustomOption('button_label', $label); // Custom for Crud::PAGE_DETAIL
        $this->setFormTypeOption('button_label', $label); // FormType for Crud::PAGE_EDIT

        return $this;
    }

    public function setButtonAction(string $action): self
    {
        self::$buttonAttributes['data-action'] = $action;
        $this->setFormTypeOption('button_attr', self::$buttonAttributes);

        return $this;
    }

    public function setButtonTarget(string $target): self
    {
        self::$buttonAttributes['data-target'] = $target;
        $this->setFormTypeOption('button_attr', self::$buttonAttributes);
        $this->setCustomOption('target', $target);

        return $this;
    }

    public function setButtonClass(string $class): self
    {
        self::$buttonAttributes['class'] = $class;
        $this->setFormTypeOption('button_attr', self::$buttonAttributes);

        return $this;
    }
}
