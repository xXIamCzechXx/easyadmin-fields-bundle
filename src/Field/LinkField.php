<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class LinkField
{
    use FieldTrait;

    public const URL = 'url';
    public const TARGET = 'target';
    public const PAGE_NAME = 'pageName';

    private const SUPPORTED_CRUD_ACTIONS = [Crud::PAGE_DETAIL, Crud::PAGE_EDIT];

    /**
     * @throws \InvalidArgumentException
     */
    public static function link(FieldInterface $field, array $options = []): FieldInterface
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        $options = $resolver->resolve($options);

        if (!method_exists($field, 'setFormTypeOptions') || !in_array($options[self::PAGE_NAME], self::SUPPORTED_CRUD_ACTIONS)) {
            return $field;
        }

        if (!$field instanceof AssociationField) {
            throw new \InvalidArgumentException(sprintf(
                "Associated field to a LinkField should be an instance of AssociationField, instance of %s given",
                get_class($field)
            ));
        }

        $adminUrlGeneratorDto = $options[self::URL];

        if (!in_array($adminUrlGeneratorDto->get(EA::CRUD_ACTION), self::SUPPORTED_CRUD_ACTIONS)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid action for LinkField: %s. Use a different action than %s, available actions are: %s",
                get_class($field), $adminUrlGeneratorDto->get(EA::CRUD_ACTION), implode(', ', self::SUPPORTED_CRUD_ACTIONS)
            ));
        }

        return $field
            ->addFormTheme('@EasyAdminFields/themes/link.html.twig')
            ->setFormTypeOption('attr', [
                self::URL => $adminUrlGeneratorDto,
                EA::CRUD_CONTROLLER_FQCN => $adminUrlGeneratorDto->get(EA::CRUD_CONTROLLER_FQCN),
                EA::CRUD_ACTION => $adminUrlGeneratorDto->get(EA::CRUD_ACTION),
                self::TARGET => $options[self::TARGET],
            ])
            ->setFormTypeOption('row_attr.data-controller', 'iamczech--easyadmin-fields-bundle--link');
    }

    private static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::TARGET => '_blank',
        ]);

         $resolver
             ->setRequired(self::URL)
             ->setRequired(self::PAGE_NAME)
             ->setAllowedTypes(self::URL, AdminUrlGeneratorInterface::class)
             ->setAllowedTypes(self::PAGE_NAME, 'string');
    }
}
