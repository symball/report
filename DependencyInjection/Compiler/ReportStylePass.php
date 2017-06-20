<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\DependencyInjection\Compiler;

/**
 * Description of ReportStylePass
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ReportStylePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('symball_report.style')) {
            return;
        }

        $definition = $container->findDefinition('symball_report.style');

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('symball_report.style');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                // add the transport service to the ChainTransport service
                $definition->addMethodCall('addStyle', array(
                    new Reference($id),
                    $attributes["alias"],
                ));
            }
        }
    }
}
