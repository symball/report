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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symball\ReportBundle\DependencyInjection\SymballReportExtension;
/**
 * Description of ReportBundle
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ServiceTest extends AbstractExtensionTestCase
{
    
    protected function getContainerExtensions() {
       return array(
         new SymballReportExtension()
       );
    }
    
    protected function getMinimalConfiguration()
    {
        return array(
            'default_report_path' => '/a/fake/path',
        );
    }
    
    public function testServiceIdentification() {
        $this->load();
        $this->assertContainerBuilderHasService('symball_report.excel_service');
        $this->assertContainerBuilderHasService('symball_report.meta');
        $this->assertContainerBuilderHasService('symball_report.report_builder');
        $this->assertContainerBuilderHasService('symball_report.pattern');
        $this->assertContainerBuilderHasService('symball_report.query');
        $this->assertContainerBuilderHasService('symball_report.style');
        
    }
}
