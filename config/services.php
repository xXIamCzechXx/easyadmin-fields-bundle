<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Asset\AssetPackage;

return static function (ContainerConfigurator $container)
{
    $container->services()->defaults()->public()

    ->set(AssetPackage::class)
        ->arg(0, service('request_stack'))
        ->tag('assets.package', ['package' => AssetPackage::PACKAGE_NAME]);
};
