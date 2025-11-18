<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class LinkField
{
    use FieldTrait;

    public const URL_TO_ACTION = 'urlToAction';
    public const TARGET = 'target';
    public const OPTION_CRUD_CONTROLLER = 'crudControllerFqcn';

    public static function link(FieldInterface $field, array $options = []): FieldInterface
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        $options = $resolver->resolve($options);

        if (!method_exists($field, 'setFormTypeOptions')) {
            return $field;
        }

        if (!$field instanceof AssociationField) {
            throw new \InvalidArgumentException(sprintf(
                "Adapted DependentField should be an instance of AssociationField, instance of %s given",
                get_class($field)
            ));
        }
        $url = $options[self::URL_TO_ACTION];

        return $field
            ->addFormTheme('@EasyAdminFields/themes/link.html.twig')
            ->setFormTypeOption('attr', [
                self::URL_TO_ACTION => $url,
                self::OPTION_CRUD_CONTROLLER => $url->get(self::OPTION_CRUD_CONTROLLER),
                self::TARGET => $options[self::TARGET] ?? '_blank',
            ]);
    }

    private static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::TARGET => '_blank',
        ]);

         $resolver
             ->setRequired(self::URL_TO_ACTION)
             ->setAllowedTypes(self::URL_TO_ACTION, AdminUrlGeneratorInterface::class);
    }
}
