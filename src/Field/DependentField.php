<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Iamczech\EasyAdminFieldsBundle\Form\Type\CrudDependentType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class DependentField
{
    public static function adapt(FieldInterface $field, array $options = []): FieldInterface
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        $options = $resolver->resolve($options);

        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        if (!$field instanceof ChoiceField && !$field instanceof AssociationField) {
            throw new \InvalidArgumentException(sprintf(
                "Adapted DependentField should be an instance of ChoiceField or AssociationField, instance of %s given",
                get_class($field)
            ));
        }

        $field->setFormType(CrudDependentType::class);

        return $field
            ->setFormTypeOptions([
                'row_attr' => [
                    'data-controller' => 'iamczech--easyadmin-fields-bundle--dependent',
                    'data-dependent-field-options' => self::encodeOptions($options),
                    'data-dependent-field' => 'adapt',
                ],
            ]);
    }

    public static function hide(FieldInterface $field, array $options = [])
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        $options = $resolver->resolve($options);

        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        return $field
            ->setFormTypeOptions([
                'row_attr' => [
                    'data-controller' => 'iamczech--easyadmin-fields-bundle--dependent',
                    'data-dependent-field-options' => self::encodeOptions($options),
                    'data-dependent-field' => 'hide',
                    'style' => 'display: none;',
                ],
            ]);
    }

    public static function encodeOptions(array $options): false|string
    {
        try {
            return json_encode($options, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return '';
        }
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'fetch_on_init' => false,
        ]);

        $resolver->setRequired('callback_url');
        $resolver->setRequired('dependencies');

        $resolver->setAllowedTypes('callback_url', 'string');
        $resolver->setAllowedTypes('dependencies', 'string[]');
        $resolver->setAllowedTypes('fetch_on_init', 'boolean');
    }
}
