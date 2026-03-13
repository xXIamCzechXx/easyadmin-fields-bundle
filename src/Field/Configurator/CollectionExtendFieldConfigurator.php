<?php

namespace Iamczech\EasyAdminFieldsBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Iamczech\EasyAdminFieldsBundle\Field\CollectionExtendField;
use function Symfony\Component\Translation\t;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 * @deprecated in development (experimental feature)
 */
final class CollectionExtendFieldConfigurator implements FieldConfiguratorInterface
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return CollectionField::class === $field->getFieldFqcn() &&
            $field->getCustomOption(CollectionExtendField::PROPERTY_ALIAS);
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field
            ->setFormTypeOptions([
                'attr' => array_merge($field->getFormTypeOption('attr') ?? [], [
                    'data-iamczech--easyadmin-fields-bundle--configurable-url-value' => $this->adminUrlGenerator
                        ->setController($field->getCustomOption(CollectionField::OPTION_ENTRY_CRUD_CONTROLLER_FQCN))
                        ->setAction(Crud::PAGE_INDEX)
                        ->set($field->getCustomOption(CollectionExtendField::PROPERTY_ALIAS), $entityDto->getInstance()?->getId())
                        ->generateUrl(),
                ])
            ]);
    }
}
