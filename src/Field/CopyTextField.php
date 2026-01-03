<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Iamczech\EasyAdminFieldsBundle\Form\Type\CopyTextType;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class CopyTextField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_TARGET_FIELD_NAME = 'targetFieldName';
    public const OPTION_COPY_BUTTON_LABEL = 'copyButtonLabel';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/field/name.html.twig')
            ->setFormType(CopyTextType::class)
            ->addFormTheme('@EasyAdminFields/themes/copy.html.twig')
            ->addCssClass('field-text')
            ->addCssFiles('@iamczech/easyadmin-fields/styles/copy.css')
            ->setDefaultColumns('col-md-6 col-xxl-5')
            ->setCustomOption(self::OPTION_TARGET_FIELD_NAME, null)
            ->setCustomOption(self::OPTION_COPY_BUTTON_LABEL, null);
    }

    /**
     * @param string|array<string> $fieldName
     */
    public function setTargetFieldName(string|array $fieldName): self
    {
        $this->setCustomOption(self::OPTION_TARGET_FIELD_NAME, \is_string($fieldName) ? [$fieldName] : $fieldName);

        return $this;
    }

    public function setCopyButtonLabel(string|TranslatableInterface $message): self
    {
        $this->setCustomOption(self::OPTION_COPY_BUTTON_LABEL, $message);

        return $this;
    }
}
