<?php

namespace Iamczech\EasyAdminFieldsBundle\Form\Extension;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CrudAutocompleteType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Iamczech\EasyAdminFieldsBundle\Field\AssociationExtendedField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTypeExtension extends AbstractTypeExtension
{
    public function __construct(protected AdminUrlGenerator $adminUrlGenerator) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            AssociationExtendedField::OPTION_ALLOW_ADD => false,
            AssociationExtendedField::OPTION_BUTTON_ADD_LABEL => 'action.add_new_item',
            AssociationExtendedField::OPTION_BUTTON_ADD_ICON => 'fa-plus',
            AssociationExtendedField::OPTION_LIST_SELECTOR => false,
            AssociationExtendedField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER => null,
            AssociationExtendedField::OPTION_LIST_BUTTON_LABEL => 'action.list_items',
            AssociationExtendedField::OPTION_LIST_BUTTON_ICON => 'fa-list',
            AssociationExtendedField::OPTION_LIST_BUTTON_CANCEL_LABEL => 'action.list.cancel',
            AssociationExtendedField::OPTION_LIST_BUTTON_VALIDATE_LABEL => 'action.list.validate',
            AssociationExtendedField::OPTION_LIST_SHOW_FILTER => false,
            AssociationExtendedField::OPTION_LIST_SHOW_SEARCH => false,
            AssociationExtendedField::OPTION_LIST_FILTERS => null,
            AssociationExtendedField::OPTION_LIST_DISPLAY_COLUMNS => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $settableOptions = AssociationExtendedField::getSettableOptions();
        foreach ($settableOptions as $option) {
            $view->vars[$option] = $options[$option];
        }

        if (isset($options[AssociationExtendedField::OPTION_ALLOW_ADD]) && $options[AssociationExtendedField::OPTION_ALLOW_ADD] && !empty($options[AssociationExtendedField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER])) {
            $ajaxEndpointUrl = $this->adminUrlGenerator
                ->setController($options[AssociationExtendedField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER])
                ->setAction('new')
                ->generateUrl();
            $view->vars['attr']['data-ea-ajax-new-endpoint-url'] = $ajaxEndpointUrl;
        }

        // dump($options[AssociationField::OPTION_LIST_SELECTOR]); exit;

        if (isset($options[AssociationExtendedField::OPTION_LIST_SELECTOR]) && $options[AssociationExtendedField::OPTION_LIST_SELECTOR] && !empty($options[AssociationExtendedField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER])) {
            $ajaxEndpointUrl = $this->adminUrlGenerator
                ->setController($options[AssociationExtendedField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER])
                ->setAction('index')
                ->generateUrl();
            $view->vars['attr']['data-ea-ajax-index-url'] = $ajaxEndpointUrl;
        }

        /*if(isset($view->vars['attr']["data-ea-widget"]) && $view->vars['attr']["data-ea-widget"] == "ea-autocomplete"){
            if(!isset($view->vars['attr']['data-ea-autocomplete-endpoint-url'])){
                $view->vars['attr']['data-ea-autocomplete-endpoint-url'] = '/';
            }
        }*/
    }

    /**
     * @return class-string[]
     */
    public static function getExtendedTypes(): array
    {
        return [EntityType::class, CrudAutocompleteType::class];
    }
}
