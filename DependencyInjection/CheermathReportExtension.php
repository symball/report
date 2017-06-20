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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class SymballReportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('symball_report.excel_service');
        $definition->replaceArgument(0, $config['default_report_path']);
        $definition->replaceArgument(1, $config['excel_factory_namespace']);
        $definition->replaceArgument(2, $config['output_format']);

        $builderDefinition = $container->getDefinition('symball_report.report_builder');
        $builderDefinition->replaceArgument(0, new Reference('symball_report.excel_service'));

        $navServiceName = 'symball_report.navigation.' . $config['layout_style'];
        $builderDefinition->replaceArgument(2, new Reference($navServiceName));
    }
}
