<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 * @deprecated in development (experimental feature)
 */
final class CollectionExtendField
{
    const PROPERTY_ALIAS = 'property';

    public static function switchable(FieldInterface $field): FieldInterface
    {
        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        if (!$field instanceof CollectionField) {
            throw new \InvalidArgumentException(sprintf(
                "Extended field should be an instance of CollectionField, instance of %s given",
                get_class($field)
            ));
        }

        $fieldDto = $field->getAsDto();

        return $field
            ->setFormTypeOptions([
                'row_attr' => array_merge($fieldDto->getFormTypeOption('row_attr') ?? [], [
                    'data-controller' => 'iamczech--easyadmin-fields-bundle--switchable',
                ])
            ]);
    }

    public static function configurable(FieldInterface $field, ?string $propertyAlias = 'entityInstanceId'): FieldInterface
    {
        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        if (!$field instanceof CollectionField) {
            throw new \InvalidArgumentException(sprintf(
                "Extended field should be an instance of CollectionField, instance of %s given",
                get_class($field)
            ));
        }

        $fieldDto = $field->getAsDto();
        $field->setCustomOption(self::PROPERTY_ALIAS, $propertyAlias);

        return $field
            ->setFormTypeOptions([
                'attr' => array_merge($fieldDto->getFormTypeOption('attr') ?? [], [
                    'data-controller' => 'iamczech--easyadmin-fields-bundle--configurable',
                ])
            ]);
    }
}
