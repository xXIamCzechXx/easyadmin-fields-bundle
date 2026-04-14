<?php

namespace Iamczech\EasyAdminFieldsBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class AssociationExtendedField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_AUTOCOMPLETE = 'autocomplete';
    public const OPTION_AUTOCOMPLETE_CALLBACK = 'autocompleteCallback';
    public const OPTION_AUTOCOMPLETE_TEMPLATE = 'autocompleteTemplate';
    public const OPTION_EMBEDDED_CRUD_FORM_CONTROLLER = 'crudControllerFqcn';
    public const OPTION_WIDGET = 'widget';
    public const OPTION_QUERY_BUILDER_CALLABLE = 'queryBuilderCallable';
    /** @internal this option is intended for internal use only */
    public const OPTION_RELATED_URL = 'relatedUrl';
    /** @internal this option is intended for internal use only */
    public const OPTION_DOCTRINE_ASSOCIATION_TYPE = 'associationType';

    public const WIDGET_AUTOCOMPLETE = 'autocomplete';
    public const WIDGET_NATIVE = 'native';

    /** @var string */
    public const OPTION_ALLOW_ADD = 'allow_add';

    /** @var string */
    public const OPTION_BUTTON_ADD_LABEL = 'button_add_label';

    /** @var string */
    public const OPTION_BUTTON_ADD_ICON = 'button_add_icon';

    /** @var string */
    public const OPTION_LIST_SELECTOR = 'list_selector';

    /** @var string */
    public const OPTION_LIST_BUTTON_LABEL = 'list_button_label';

    /** @var string */
    public const OPTION_LIST_BUTTON_ICON = 'list_button_icon';

    /** @var string */
    public const OPTION_LIST_BUTTON_CANCEL_LABEL = 'list_button_cancel_label';

    /** @var string */
    public const OPTION_LIST_BUTTON_VALIDATE_LABEL = 'list_button_validate_label';

    /** @var string */
    public const OPTION_LIST_SHOW_FILTER = 'list_show_filter';

    /** @var string */
    public const OPTION_LIST_SHOW_SEARCH = 'list_show_search';

    /** @var string */
    public const OPTION_LIST_DISPLAY_COLUMNS = 'list_display_columns';

    /** @var string */
    public const OPTION_LIST_FILTERS = 'list_filters';

    public const PARAM_AUTOCOMPLETE_CONTEXT = 'autocompleteContext';

    public const OPTION_RENDER_AS_EMBEDDED_FORM = 'renderAsEmbeddedForm';

    public const OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME = 'crudNewPageName';
    public const OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME = 'crudEditPageName';
    // the name of the property in the associated entity used to sort the results (only for *-To-One associations)
    public const OPTION_SORT_PROPERTY = 'sortProperty';
    public const OPTION_ESCAPE_HTML_CONTENTS = 'escapeHtml';
    public const OPTION_PREFERRED_CHOICES = 'preferredChoices';

    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('@EasyAdmin/crud/field/association.html.twig')
            ->addFormTheme('@EasyAdminFields/theme/association.html.twig')
            ->setFormType(EntityType::class)
            ->addCssClass('field-association')
            ->setDefaultColumns('col-md-7 col-xxl-6')
            ->setCustomOption(self::OPTION_AUTOCOMPLETE, false)
            ->setCustomOption(self::OPTION_AUTOCOMPLETE_CALLBACK, null)
            ->setCustomOption(self::OPTION_AUTOCOMPLETE_TEMPLATE, null)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, null)
            ->setCustomOption(self::OPTION_WIDGET, self::WIDGET_AUTOCOMPLETE)
            ->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, null)
            ->setCustomOption(self::OPTION_RELATED_URL, null)
            ->setCustomOption(self::OPTION_DOCTRINE_ASSOCIATION_TYPE, null)
            ->setCustomOption(self::OPTION_ALLOW_ADD, false)
            ->setCustomOption(self::OPTION_LIST_SELECTOR, false)
            ->setCustomOption(self::OPTION_RENDER_AS_EMBEDDED_FORM, false)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME, null)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME, null)
            ->setCustomOption(self::OPTION_ESCAPE_HTML_CONTENTS, true)
            ->setCustomOption(self::OPTION_PREFERRED_CHOICES, null);
    }

    public static function getSettableOptions(): array
    {
        return [
            self::OPTION_BUTTON_ADD_LABEL,
            self::OPTION_BUTTON_ADD_ICON,
            self::OPTION_ALLOW_ADD,
            self::OPTION_LIST_SELECTOR,
            self::OPTION_LIST_BUTTON_LABEL,
            self::OPTION_LIST_BUTTON_ICON,
            self::OPTION_LIST_BUTTON_CANCEL_LABEL,
            self::OPTION_LIST_BUTTON_VALIDATE_LABEL,
            self::OPTION_LIST_SHOW_FILTER,
            self::OPTION_LIST_SHOW_SEARCH,
            self::OPTION_LIST_FILTERS,
            self::OPTION_LIST_DISPLAY_COLUMNS,
        ];
    }

    public function allowAdd(bool $allow = true): self
    {
        $this->setCustomOption(self::OPTION_ALLOW_ADD, $allow);

        return $this;
    }

    public function autocomplete(bool $enable = true, ?callable $callback = null, ?string $template = null, bool $renderAsHtml = false): self
    {
        if (!$enable) {
            return $this;
        }

        $this->setCustomOption(self::OPTION_AUTOCOMPLETE, true);

        if (null !== $callback) {
            $this->setCustomOption(self::OPTION_AUTOCOMPLETE_CALLBACK, $callback);
        }

        if (null !== $template) {
            $this->setCustomOption(self::OPTION_AUTOCOMPLETE_TEMPLATE, $template);
        }

        // the renderAsHtml parameter controls the same option as renderAsHtml() method
        $this->setCustomOption(self::OPTION_ESCAPE_HTML_CONTENTS, !$renderAsHtml);

        return $this;
    }

    public function renderAsNativeWidget(bool $asNative = true): self
    {
        $this->setCustomOption(self::OPTION_WIDGET, $asNative ? self::WIDGET_NATIVE : self::WIDGET_AUTOCOMPLETE);

        return $this;
    }

    public function setCrudController(string $crudControllerFqcn): self
    {
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, $crudControllerFqcn);

        return $this;
    }

    public function setQueryBuilder(\Closure $queryBuilderCallable): self
    {
        $this->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, $queryBuilderCallable);

        return $this;
    }

    public function setButtonAddLabel(string $label): self
    {
        $this->setCustomOption(self::OPTION_BUTTON_ADD_LABEL, $label);

        return $this;
    }

    public function setButtonAddIcon(string $icon): self
    {
        $this->setCustomOption(self::OPTION_BUTTON_ADD_ICON, $icon);

        return $this;
    }

    public function listSelector(bool $add = true): self
    {
        $this->setCustomOption(self::OPTION_LIST_SELECTOR, $add);

        return $this;
    }

    public function listButtonIcon(string $icon): self
    {
        $this->setCustomOption(self::OPTION_LIST_BUTTON_ICON, $icon);

        return $this;
    }

    public function listButtonLabel(string $label): self
    {
        $this->setCustomOption(self::OPTION_LIST_BUTTON_LABEL, $label);

        return $this;
    }

    public function listButtonCancelLabel(string $label): self
    {
        $this->setCustomOption(self::OPTION_LIST_BUTTON_CANCEL_LABEL, $label);

        return $this;
    }

    public function listButtonValidateLabel(string $label): self
    {
        $this->setCustomOption(self::OPTION_LIST_BUTTON_VALIDATE_LABEL, $label);

        return $this;
    }

    /**
     * @deprecated does not work in EA 5.0.2 and newer correctly
     */
    public function listShowFilter(bool $show = true): self
    {
        $this->setCustomOption(self::OPTION_LIST_SHOW_FILTER, $show);

        return $this;
    }

    /**
    * @deprecated does not work in EA 5.0.2 and newer correctly
    */
    public function listShowSearch(bool $show = true): self
    {
        $this->setCustomOption(self::OPTION_LIST_SHOW_SEARCH, $show);

        return $this;
    }

    public function listFilters(array $filters = []): self
    {
        $this->setCustomOption(self::OPTION_LIST_FILTERS, $filters);

        return $this;
    }

    public function listDisplayColumns($columns = 1, $separator = '-'): self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }

        $this->setCustomOption(self::OPTION_LIST_DISPLAY_COLUMNS, ['columns' => $columns, 'separator' => $separator]);

        return $this;
    }

    public function renderAsEmbeddedForm(?string $crudControllerFqcn = null, ?string $crudNewPageName = null, ?string $crudEditPageName = null): self
    {
        $this->setCustomOption(self::OPTION_RENDER_AS_EMBEDDED_FORM, true);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, $crudControllerFqcn);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME, $crudNewPageName);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME, $crudEditPageName);

        return $this;
    }

    public function setSortProperty(string $orderProperty): self
    {
        $this->setCustomOption(self::OPTION_SORT_PROPERTY, $orderProperty);

        return $this;
    }

    public function renderAsHtml(bool $asHtml = true): self
    {
        $this->setCustomOption(self::OPTION_ESCAPE_HTML_CONTENTS, !$asHtml);

        return $this;
    }

    /**
     * Sets the preferred entities that will be displayed at the top of the dropdown,
     * visually separated from the rest of entities.
     *
     * You can pass an array of entity objects or their primary key values:
     *   ->setPreferredChoices([1, 2, 3])
     *   ->setPreferredChoices([$featuredCategory1, $featuredCategory2])
     *
     * Or a callable that receives an entity and returns true for preferred choices:
     *   ->setPreferredChoices(fn (Category $category) => $category->isFeatured())
     *
     * Note: This option is not compatible with remote autocomplete (->autocomplete()).
     *
     * @param array<mixed>|callable $preferredChoices
     */
    public function setPreferredChoices(array|callable $preferredChoices): self
    {
        $this->setCustomOption(self::OPTION_PREFERRED_CHOICES, $preferredChoices);

        return $this;
    }
}
