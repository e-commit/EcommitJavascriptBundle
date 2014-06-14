<?php

/*
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
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

        $container->setParameter('ecommit_javascript.tiny_mce.script_url', $config['tiny_mce']['script_url']);
        $container->setParameter('ecommit_javascript.tiny_mce.jQuery_script_url', $config['tiny_mce']['jQuery_script_url']);
        $container->setParameter('ecommit_javascript.recaptcha.public_key', $config['recaptcha']['public_key']);
        $container->setParameter('ecommit_javascript.recaptcha.private_key', $config['recaptcha']['private_key']);
        $container->setParameter('ecommit_javascript.recaptcha.secure', $config['recaptcha']['secure']);
        $container->setParameter('ecommit_javascript.recaptcha.enable', $config['recaptcha']['enable']);
        $container->setParameter('ecommit_javascript.use_bootstrap', $config['use_bootstrap']);
    }
}
