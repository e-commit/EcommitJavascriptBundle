<?php

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
				->arrayNode('ajax')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('autocallbacks')->defaultTrue()->end()
					->end()
				->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
