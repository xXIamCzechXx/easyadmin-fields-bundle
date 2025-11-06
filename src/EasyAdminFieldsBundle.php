<?php

namespace Iamczech\EasyAdminFieldsBundle;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
final class EasyAdminFieldsBundle extends AbstractBundle implements PrependExtensionInterface
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        if (!class_exists(StimulusBundle::class)) {
            throw new \LogicException('⚠️ symfony/stimulus-bundle is required for EasyAdminFieldsBundle to work.');
        }

        if (interface_exists(AssetMapperInterface::class)) {
            $builder->setParameter('asset_mapper.paths', ['vendor/iamczech/easyadmin-fields-bundle/assets' => '@easyadmin_fields']);
        }

        $projectDir = $builder->getParameter('kernel.project_dir');
        if (!file_exists($projectDir . '/importmap.php')) {
            throw new InvalidConfigurationException(
                '❌ Missing importmap.php file. Run: php bin/console importmap:install'
            );
        }

        $importMap = include $projectDir . '/importmap.php';

        $missingMaps = [];
        if (!isset($importMap['@symfony/stimulus-bridge'])) {
            $missingMaps[] = '@symfony/stimulus-bridge';
        }
        if (!isset($importMap['sortablejs'])) {
            $missingMaps[] = 'sortablejs';
        }

        if (!empty($missingMaps)) {
            throw new InvalidConfigurationException(sprintf(
                "❌ Required JS packages for EasyAdminFieldsBundle are missing in importmap.php.\n\n" .
                "👉 Please install them by running:\n" .
                "   php bin/console importmap:require %s\n",
                implode(' ', $missingMaps)
            ));
        }
    }

    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        if (!$this->isAssetMapperAvailable($container)) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig('twig', ['form_themes' => ['@EasyAdminFields/layouts/embed.html.twig']]);
        }

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath() . '/assets/dist' => '@iamczech/easyadmin-fields',
                ],
            ],
        ]);
    }

    private function isAssetMapperAvailable(ContainerBuilder $container): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // check that FrameworkBundle 6.3 or higher is installed
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig('twig', ['form_themes' => ['@EasyAdminFields/layouts/embed.html.twig']]);
        }
    }
}
