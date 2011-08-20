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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EcommitJavascriptExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) 
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
		
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
		
		$container->setParameter('ecommit_javascript.jQuery_core.auto_enable', $config['jQuery_core']['auto_enable']);
		$container->setParameter('ecommit_javascript.jQuery_core.js', $config['jQuery_core']['js']);
		$container->setParameter('ecommit_javascript.jQuery_ui.js', $config['jQuery_ui']['js']);
		$container->setParameter('ecommit_javascript.jQuery_ui.css', $config['jQuery_ui']['css']);
		$container->setParameter('ecommit_javascript.jQuery_tools.js', $config['jQuery_tools']['js']);
		$container->setParameter('ecommit_javascript.ajax.autocallbacks', $config['ajax']['autocallbacks']);
    }
}
