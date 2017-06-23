<?php

/*
 * This file is part of
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Tests\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symball\ReportBundle\DependencyInjection\Compiler\ReportStylePass;
/**
 * Description of CompilerTest
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ReportStyleCompilerTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReportStylePass());
    }
    
    public function reportStyleTest()
    {
        $collectingService = new Definition();
        $this->setDefinition('symball_report.style', $collectingService);
        
        $collectedService = new Definition();
        $collectedService->addTag('symball_report.style', ['alias' => 'test_alias']);
        $this->setDefinition('some_unimportant_service_id', $collectedService);
        
        $this->compile();
        
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'symball_report.style',
            'add',
            array(
                new Reference('some_unimportant_service_id')
            )
        );
        
    }
}