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

//use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use PHPUnit\Framework\TestCase;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Symball\ReportBundle\DependencyInjection\SymballReportExtension;
use Symball\ReportBundle\DependencyInjection\Configuration;
/**
 * Description of ReportBundle
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ConfigurationTest extends TestCase
{
    
    use ConfigurationTestCaseTrait;
    
    protected function getContainerExtension() {
       return new SymballReportExtension();
    }
    
    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testBaseParameters() {
        
        $this->assertConfigurationIsInvalid(
            array(
                array()
            ),
            'default_report_path'
        );
        
    }

}
