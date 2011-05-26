<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) Hubert LECORCHE <hlecorche@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EcommitJavascriptExtension extends Extension {

    
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $config, ContainerBuilder $container) 
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->mergeExternalConfig($config, $container, $this->getAlias());
    }

    
    /**
     * Merges app config with bundle config
     * 
     * @param array $config
     * @param ContainerBuilder $container
     * @param string $alias 
     */
    private function mergeExternalConfig(array $config, ContainerBuilder $container, $alias)
    {
        $mergedConfig = array();

        foreach ($config as $cnf) 
        {
            $mergedConfig = array_merge($mergedConfig, $cnf);
        }

        if (isset($mergedConfig['jQuery_core']['auto_enable'])) 
        {
            $container->setParameter($alias . '.jQuery_core.auto_enable', $mergedConfig['jQuery_core']['auto_enable']);
        }
        if (isset($mergedConfig['jQuery_core']['js'])) 
        {
            $container->setParameter($alias . '.jQuery_core.js', $mergedConfig['jQuery_core']['js']);
        }
        if (isset($mergedConfig['jQuery_tools']['js'])) 
        {
            $container->setParameter($alias . '.jQuery_tools.js', $mergedConfig['jQuery_tools']['js']);
        }
        if (isset($mergedConfig['ajax']['autocallbacks'])) 
        {
            $container->setParameter($alias . '.ajax.autocallbacks', $mergedConfig['ajax']['autocallbacks']);
        }
    }
}
