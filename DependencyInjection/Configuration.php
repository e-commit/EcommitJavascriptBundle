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
                ->arrayNode('recaptcha')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_key')->defaultValue(null)->end()
                        ->scalarNode('private_key')->defaultValue(null)->end()
                        ->booleanNode('secure')->defaultFalse()->end()
                        ->booleanNode('enable')->defaultTrue()->end()
                    ->end()
                ->end()
                ->booleanNode('use_bootstrap')->defaultValue(false)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
