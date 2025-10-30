<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Asset\AssetPackage;
use Iamczech\EasyAdminFieldsBundle\Form\EventListener\DependentAutocompleteSubscriber;
use Iamczech\EasyAdminFieldsBundle\Service\EmbedConfigurator;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
return static function (ContainerConfigurator $container)
{
    $container->services()->defaults()->public()

    ->set(AssetPackage::class)
        ->arg(0, service('request_stack'))
        ->tag('assets.package', ['package' => AssetPackage::PACKAGE_NAME])

    ->set(EmbedConfigurator::class)
        ->autowire()
        ->autoconfigure()

    ->set(DependentAutocompleteSubscriber::class)
        ->autowire()
        ->autoconfigure();
};
