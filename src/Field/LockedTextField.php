<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class LockedTextField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_UNLOCK_GROUP = 'attr.data-group';
    public const OPTION_CONTENT_TEXT = 'attr.data-text';
    public const OPTION_CONFIRM_TEXT = 'attr.data-confirm';
    public const OPTION_CANCEL_TEXT = 'attr.data-cancel';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/field/name.html.twig')
            ->setFormType(TextType::class)
            ->setFormTypeOptions([
                'attr.data-controller' => 'iamczech--easyadmin-fields-bundle--locked',
                'attr.data-action' => 'click->iamczech--easyadmin-fields-bundle--locked#unlock',
                'attr.readonly' => 'readonly',
            ])
            ->setFormTypeOption(self::OPTION_UNLOCK_GROUP, 'default')
            ->setFormTypeOption(self::OPTION_CONTENT_TEXT, 'Accept changes of locked fields?')
            ->setFormTypeOption(self::OPTION_CONFIRM_TEXT, 'Confirm')
            ->setFormTypeOption(self::OPTION_CANCEL_TEXT, 'Cancel')
            ->addCssClass('field-text')
            ->setDefaultColumns('col-md-6 col-xxl-5');
    }

    public function setUnlockGroup(?string $group): self
    {
        $this->setFormTypeOption(self::OPTION_UNLOCK_GROUP, $group);

        return $this;
    }

    public function setContentText(?string $text): self
    {
        $this->setFormTypeOption(self::OPTION_CONTENT_TEXT, $text);

        return $this;
    }

    public function setConfirmText(?string $text): self
    {
        $this->setFormTypeOption(self::OPTION_CONFIRM_TEXT, $text);

        return $this;
    }

    public function setCancelText(?string $text): self
    {
        $this->setFormTypeOption(self::OPTION_CANCEL_TEXT, $text);

        return $this;
    }
}
