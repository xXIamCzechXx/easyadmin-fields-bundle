<?php

namespace Iamczech\EasyAdminFieldsBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Iamczech\EasyAdminFieldsBundle\Field\LockedTextField;
use function Symfony\Component\Translation\t;
use Symfony\Contracts\Translation\TranslatableInterface;
use RuntimeException;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class LockedTextConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return LockedTextField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $targetUnlockGroupName = (string)$field->getCustomOption(LockedTextField::OPTION_UNLOCK_GROUP);
        if ('' === $targetUnlockGroupName) {
            throw new RuntimeException(sprintf('The "%s" field must define the name(s) of the group using the "setUnlockGroup()" method.', $field->getProperty()));
        }

        $field->setFormTypeOption('group', $targetUnlockGroupName);
        $field->setFormTypeOption('attr.data-group', $targetUnlockGroupName);
        $field->setFormTypeOption('attr.data-controller', 'iamczech--easyadmin-fields-bundle--locked');
        $field->setFormTypeOption('attr.data-action', 'click->iamczech--easyadmin-fields-bundle--locked#unlock');
        $field->setFormTypeOption('attr.readonly', 'readonly');

        if (null !== $contentText = $field->getCustomOption(LockedTextField::OPTION_CONTENT_TEXT)) {
            if (!$contentText instanceof TranslatableInterface) {
                $contentText = t($contentText, [], $context->getI18n()->getTranslationDomain());
            }

            $field->setFormTypeOption(LockedTextField::OPTION_CONTENT_TEXT, $contentText);
        }
        if (null !== $confirmText = $field->getCustomOption(LockedTextField::OPTION_CONFIRM_TEXT)) {
            if (!$confirmText instanceof TranslatableInterface) {
                $confirmText = t($confirmText, [], $context->getI18n()->getTranslationDomain());
            }

            $field->setFormTypeOption(LockedTextField::OPTION_CONFIRM_TEXT, $confirmText);
        }
        if (null !== $cancelText = $field->getCustomOption(LockedTextField::OPTION_CANCEL_TEXT)) {
            if (!$cancelText instanceof TranslatableInterface) {
                $cancelText = t($cancelText, [], $context->getI18n()->getTranslationDomain());
            }

            $field->setFormTypeOption(LockedTextField::OPTION_CANCEL_TEXT, $cancelText);
        }
    }
}
