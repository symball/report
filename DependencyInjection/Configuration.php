<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symball_report');

        $rootNode
            ->children()
              ->scalarNode('excel_factory_namespace')
                ->defaultValue('\PHPExcel_IOFactory')
              ->end()
              ->scalarNode('layout_style')
                ->defaultValue('horizontal')
              ->end()
              ->scalarNode('output_format')
                ->defaultValue('Excel2007')
              ->end()
              ->scalarNode('default_report_path')
                ->isRequired()
              ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
