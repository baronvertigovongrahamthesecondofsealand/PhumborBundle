<?php

namespace Jb\Bundle\PhumborBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * PhumborBundle extension
 *
 * @author Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 */
class JbPhumborExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $this->loadConfiguration($container, $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Load configuration
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function loadConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('phumbor.publicroot', $config['local']['publicroot']);
        $container->setParameter('phumbor.server.upload_enabled', $config['server']['upload_enabled']);
        $container->setParameter('phumbor.server.upload_auth_username', $config['server']['upload_auth_username']);
        $container->setParameter('phumbor.server.upload_auth_password', $config['server']['upload_auth_password']);
        $container->setParameter('phumbor.server.url', $config['server']['url']);
        $container->setParameter('phumbor.secret', $config['server']['secret']);
        $container->setParameter('phumbor.transformations', $config['transformations']);
    }
}
