<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Iamczech\EasyAdminFieldsBundle\Form\Type\EmbedType;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
class EmbedField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_EMBEDDED_CALLBACK_URL = 'iamczech_embedded_callback_url';
    public const OPTION_EMBEDDED_HEIGHT = 'iamczech_embedded_height';
    public const OPTION_EMBEDDED_CONTROLLER = 'iamczech_embedded_controller';
    public const OPTION_EMBEDDED_CRUD_CONTROLLER = 'iamczech_embedded_crud_controller';
    public const OPTION_EMBEDDED_PROPERTY_ALIAS = 'iamczech_embedded_property_alias';
    public const OPTION_EMBEDDED_RELATED_ENTITY = 'iamczech_embedded_related_entity';
    public const OPTION_EMBEDDED_ACTION = 'iamczech_embedded_action';
    public const OPTION_EMBEDDED_PAGE_ADD_TEXT = 'iamczech_embedded_page_add_text';
    public const OPTION_EMBEDDED_PARAMETERS = 'iamczech_embedded_parameters';

    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->addFormTheme('@EasyAdminFields/theme/embed.html.twig')
            ->setTemplatePath('@EasyAdminFields/field/embed.html.twig')
            ->setFormType(EmbedType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'by_reference' => false,
                'label_attr.style' => 'display: none;', // Label must be hidden so it does not waste space on page
            ])
            ->hideOnIndex()
            ->setCustomOption(self::OPTION_EMBEDDED_CALLBACK_URL, null)
            ->setCustomOption(self::OPTION_EMBEDDED_HEIGHT, '400px')
            ->setCustomOption(self::OPTION_EMBEDDED_CONTROLLER, 'iamczech--easyadmin-fields-bundle--embed')
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_CONTROLLER, null)
            ->setCustomOption(self::OPTION_EMBEDDED_PROPERTY_ALIAS, '')
            ->setCustomOption(self::OPTION_EMBEDDED_RELATED_ENTITY, '')
            ->setCustomOption(self::OPTION_EMBEDDED_ACTION, Crud::PAGE_INDEX)
            ->setCustomOption(self::OPTION_EMBEDDED_PAGE_ADD_TEXT, 'Data will be available when you create your record')
            ->setCustomOption(self::OPTION_EMBEDDED_PARAMETERS, [])
            ->addCssClass('embedded-form');
    }

    /**
     * @deprecated because you can't decide which menu item to render expanded by only looking at the menu item itself. You need to check all menu items at the same time.
     * @see setEmbeddedCrudController()
     * @see setEmbeddedPropertyAlias()
     */
    public function setCallbackUrl(AdminUrlGeneratorInterface $callbackUrl, $action = Crud::PAGE_INDEX): self
    {
        @trigger_deprecation('easycorp/easyadmin-bundle', '4.11', 'The "%s()" method is deprecated. Use the "%s()" method instead.', __METHOD__, 'markExpandedMenuItem()');

        throw new \RuntimeException(
            'From version 1.0.7 of iamczech/easyadmin-fields-bundle, you must use the "setEmbeddedCrudController()" and "setEmbeddedPropertyAlias()" methods instead.'
        );
    }

    public function setHeight(string $height): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_HEIGHT, $height);
    }

    public function setEmbeddedCrudController(string $controller): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_CONTROLLER, $controller);
    }

    public function setEmbeddedPropertyAlias(string $propertyAlias): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_PROPERTY_ALIAS, $propertyAlias);
    }

    public function setEmbeddedRelatedEntity(string $relatedEntity): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_RELATED_ENTITY, $relatedEntity);
    }

    public function setEmbeddedAction(string $action = Crud::PAGE_INDEX): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_ACTION, $action);
    }

    public function setEmbeddedPageAddText(string $text): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_PAGE_ADD_TEXT, $text);
    }

    public function setEmbeddedParameters(?array $parameters): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_PARAMETERS, $parameters);
    }

    public function setEmbeddedParameter(?string $parameterName, ?string $parameterValue): self
    {
        return $this->setCustomOption(self::OPTION_EMBEDDED_PARAMETERS, [$parameterName => $parameterValue]);
    }
}
