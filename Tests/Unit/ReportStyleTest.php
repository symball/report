<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\Meta;
use Symball\ReportBundle\Service\ReportStyle;
use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\StyleInterface;

/* Extend the default PHPUnit test case */
class ReportStyleTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $reportStyle = new ReportStyle();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($reportStyle));
    }
    public function testAddStyle()
    {
        $reportStyle = new ReportStyle();

        $mockStyle = $this->createMock(StyleInterface::class);
        $reportStyle->addStyle($mockStyle, 'an_alias');

        $this->assertEquals(['an_alias'], $reportStyle->getStylesLoaded());
    }
    public function testRunStyle()
    {
        $reportStyle = new ReportStyle();

        $mockStyle = $this->createMock(StyleInterface::class);
        $mockStyle->method('run')->willReturn(true);
        $reportStyle->addStyle($mockStyle, 'an_alias');

        $mockMeta = $this->createMock(Meta::class);
        $mockMeta->method('getOptions')->willReturn([]);

        $mockReportBuilder= $this->createMock(ReportBuilder::class);
        $mockReportBuilder->method('meta')->will($this->returnValue($mockMeta));

        $result = $reportStyle->run('an_alias', $mockReportBuilder, 'A1');

        $this->assertEquals(true, $result);
    }
    public function testRunNonExistentStyle()
    {
        $reportStyle = new ReportStyle();

        $mockMeta = $this->createMock(Meta::class);
        $mockMeta->method('getOptions')->willReturn([]);

        $mockReportBuilder= $this->createMock(ReportBuilder::class);
        $mockReportBuilder->method('meta')->will($this->returnValue($mockMeta));

        try {
            $result = $reportStyle->run('an_alias', $mockReportBuilder, 'A1');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Report style is not registered: an_alias', $ex->getMessage());
        }
    }
}
