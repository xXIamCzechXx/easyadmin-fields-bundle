<?php

namespace Iamczech\EasyAdminFieldsBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Iamczech\EasyAdminFieldsBundle\Field\EmbedField;
use function Symfony\Component\Translation\t;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final readonly class EmbedConfigurator implements FieldConfiguratorInterface
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return EmbedField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        if (in_array($context->getCrud()->getCurrentPage(), [Crud::PAGE_NEW, Crud::PAGE_INDEX])) {
            return;
        }

        if (('' == $embeddedCrudController = $field->getCustomOption(EmbedField::OPTION_EMBEDDED_CRUD_CONTROLLER))) {
            throw new \RuntimeException(sprintf('The "%s" field must have configured embeddedCrudController via "setEmbeddedCrudController()".', $field->getProperty()));
        }

        $callbackUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($embeddedCrudController)
            ->set('embed', 1)
            ->setAction($field->getCustomOption(EmbedField::OPTION_EMBEDDED_ACTION));

        if ('' !== $embeddedProperty = $field->getCustomOption(EmbedField::OPTION_EMBEDDED_PROPERTY_ALIAS)) {
            if ('' !== $relatedEntityProperty = $field->getCustomOption(EmbedField::OPTION_EMBEDDED_RELATED_ENTITY)) {
                $relatedEntityValue = $this->getPropertyValue($entityDto->getInstance(), $relatedEntityProperty);
                $callbackUrl->set($embeddedProperty, $relatedEntityValue);
            } else {
                $callbackUrl->set($embeddedProperty, $entityDto->getPrimaryKeyValue());
            }
        }

        foreach ($field->getCustomOption(EmbedField::OPTION_EMBEDDED_PARAMETERS) as $parameterName => $parameterValue) {
            $callbackUrl->set($parameterName, $parameterValue);
        }

        $field->setCustomOption(EmbedField::OPTION_EMBEDDED_CALLBACK_URL, $callbackUrl->generateUrl());

        $field->setFormTypeOptions([
            EmbedField::OPTION_EMBEDDED_CALLBACK_URL => $callbackUrl->generateUrl(),
            EmbedField::OPTION_EMBEDDED_CONTROLLER => $field->getCustomOption(EmbedField::OPTION_EMBEDDED_CONTROLLER),
            EmbedField::OPTION_EMBEDDED_HEIGHT => $field->getCustomOption(EmbedField::OPTION_EMBEDDED_HEIGHT),
        ]);
    }

    private function getPropertyValue(object $entityInstance, ?string $property): mixed
    {
        $getter = 'get'.ucfirst($property);

        if (!method_exists($entityInstance, $getter)) {
            throw new \RuntimeException(sprintf(
                'Method \'%s::%s()\' does not exist.',
                $entityInstance::class,
                $getter,
            ));
        }

        return $entityInstance->{$getter}()?->getId();
    }
}
