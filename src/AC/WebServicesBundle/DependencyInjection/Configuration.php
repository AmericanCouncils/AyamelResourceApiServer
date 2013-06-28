<?php

namespace AC\WebServicesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ac_web_services');

        $rootNode
            ->children()
                ->booleanNode('allow_code_suppression')->defaultTrue()->end()
                ->booleanNode('include_response_data')->defaultFalse()->end()
                ->booleanNode('include_dev_exceptions')->defaultFalse()->end()
                ->scalarNode('default_response_format')->defaultValue('json')->isRequired()->end()
                ->variableNode('api_paths')
                    ->info('array of route regex matchers')
                    ->defaultNull()
                    ->treatNullLike(array())
                ->end()
                ->variableNode('exception_map')
                    ->info('array of exceptions to status code conversions')
                    ->defaultNull()
                    ->treatNullLike(array())
                ->end()
            ->end();

        return $treeBuilder;
    }
}
