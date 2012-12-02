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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ecommit_javascript');

        $rootNode
            ->children()
                ->arrayNode('jQuery_core')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_enable')->defaultFalse()->end()
                        ->scalarNode('js')->defaultValue('ejs/jQuery/jquery.min.js')->end()
                    ->end()
                ->end()
                ->arrayNode('jQuery_ui')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('js')->defaultValue('ejs/jQuery/ui/js/jquery-ui.custom.min.js')->end()
                        ->scalarNode('css')->defaultValue('ejs/jQuery/ui/css/smoothness/jquery-ui.custom.css')->end()
                    ->end()
                ->end()
                ->arrayNode('jQuery_tools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('js')->defaultValue('ejs/jQuery/tools/jquery.tools.min.js')->end()
                    ->end()
                ->end()
                ->arrayNode('tiny_mce')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('script_url')->defaultValue('js/tiny_mce/tiny_mce.js')->end()
                        ->scalarNode('jQuery_script_url')->defaultValue('js/tiny_mce/jquery.tinymce.js')->end()
                    ->end()
                ->end()
                ->arrayNode('recaptcha')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_key')->defaultValue(null)->end()
                        ->scalarNode('private_key')->defaultValue(null)->end()
                        ->booleanNode('secure')->defaultFalse()->end()
                        ->booleanNode('enable')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
