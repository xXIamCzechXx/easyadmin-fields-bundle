<?php

namespace Iamczech\EasyAdminFieldsBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use Iamczech\EasyAdminFieldsBundle\Field\CopyTextField;
use function Symfony\Component\Translation\t;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class CopyTextConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return CopyTextField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $targetFieldNames = (array) $field->getCustomOption(CopyTextField::OPTION_TARGET_FIELD_NAME);
        if ([] === $targetFieldNames) {
            throw new \RuntimeException(sprintf('The "%s" field must define the name(s) of the field(s) whose contents are used for the slug using the "setTargetFieldName()" method.', $field->getProperty()));
        }

        $field->setFormTypeOption('target', implode('|', $targetFieldNames));

        if (null !== $copyLabel = $field->getCustomOption(CopyTextField::OPTION_COPY_BUTTON_LABEL)) {
            if (!$copyLabel instanceof TranslatableInterface) {
                $copyLabel = t($copyLabel, [], $context->getI18n()->getTranslationDomain());
            }

            $field->setFormTypeOption('attr.data-copy-text', $copyLabel);
        }
    }
}
