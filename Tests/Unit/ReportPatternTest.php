<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\ReportPattern;
use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\PatternInterface;

/* Extend the default PHPUnit test case */
class ReportPatternTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $reportPattern = new ReportPattern();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($reportPattern));
    }
    public function testAddPattern()
    {
        $reportPattern = new ReportPattern();

        $mockPattern = $this->createMock(PatternInterface::class);
        $reportPattern->addPattern($mockPattern, 'an_alias');

        $this->assertEquals(['an_alias'], $reportPattern->getPatternsLoaded());
    }
    public function testRunPattern()
    {
        $reportPattern = new ReportPattern();

        $mockPattern = $this->createMock(PatternInterface::class);
        $mockPattern->method('run')->willReturn(true);
        $reportPattern->addPattern($mockPattern, 'an_alias');

        $mockReportBuilder= $this->createMock(ReportBuilder::class);

        $result = $reportPattern->run('an_alias', $mockReportBuilder);

        $this->assertEquals(true, $result);
    }
    public function testRunNonExistentPattern()
    {
        $reportPattern = new ReportPattern();

        try {
            $mockReportBuilder= $this->createMock(ReportBuilder::class);
            $result = $reportPattern->run('an_alias', $mockReportBuilder);
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Report pattern is not registered: an_alias', $ex->getMessage());
        }
    }
}
