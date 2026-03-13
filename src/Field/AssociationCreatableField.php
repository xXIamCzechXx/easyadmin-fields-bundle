<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 * @deprecated in development (experimental feature)
 */
final class AssociationCreatableField
{
    public static function extend(FieldInterface $field): FieldInterface
    {
        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        if (!$field instanceof AssociationField) {
            throw new \InvalidArgumentException(sprintf(
                "Extended field should be an instance of AssociationField, instance of %s given",
                get_class($field)
            ));
        }

        $fieldDto = $field->getAsDto();

        return $field
            ->autocomplete() // must be autocomplete, otherwise it could not ever work
            ->setFormTypeOptions([
                'attr' => array_merge($fieldDto->getFormTypeOption('attr') ?? [], [
                    'data-controller' => 'iamczech--easyadmin-fields-bundle--creatable',
                ])
            ]);
    }
}
