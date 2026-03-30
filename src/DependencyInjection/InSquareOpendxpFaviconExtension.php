<?php

namespace InSquare\OpendxpFaviconBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class InSquareOpendxpFaviconExtension extends Extension
{
    public function getAlias(): string
    {
        return 'in_square_opendxp_favicon';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    /**
     * @param array<string, mixed> $config
     */
    private function registerParameters(ContainerBuilder $container, array $config): void
    {
        $prefix = 'in_square_opendxp_favicon';

        $container->setParameter($prefix, $config);

        foreach ($config as $key => $value) {
            $container->setParameter(sprintf('%s.%s', $prefix, $key), $value);
        }
    }
}
