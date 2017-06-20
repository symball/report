<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\Excel;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/* Extend the default PHPUnit test case */
class ExcelTest extends TestCase
{
    public function testCreation()
    {
        $excel = new Excel();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($excel));
    }
    public function testExcelObjectCreation()
    {
        $excel = new Excel();
        $excel->createExcelObject();

        $this->assertEquals('PHPExcel', get_class($excel->getExcelObject()));
    }

    public function testSheetCreation()
    {
        $excel = new Excel();

        $this->assertEquals(0, $excel->getNumberOfSheets());

        $excel->newSheet();
        $this->assertEquals('sheet-1', $excel->getCurrentSheetTitle());
        $this->assertEquals(1, $excel->getNumberOfSheets());

        $excel->newSheet('test sheet');
        $this->assertEquals('test sheet', $excel->getCurrentSheetTitle());
        $this->assertEquals(2, $excel->getNumberOfSheets());
    }

    public function testReportPath()
    {
        $excel = new Excel();
        $excel->setReportPath(getcwd());
        $this->assertEquals(getcwd(), $excel->getReportPath());
    }

    public function testUnwritableReportPath()
    {
        $excel = new Excel();

        $this->expectException(IOExceptionInterface::class);

        $excel->setReportPath('/this/is/unwritable');
    }

    public function testNonExistentReportPath()
    {
        $excel = new Excel();
        $path = '/this/path/does/not/exist';

        try {
            $excel->setReportPath($path, false);
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Report path does not exist: ' . $path, $ex->getMessage());
        }
    }

    public function testOutputFormat()
    {
        $excel = new Excel();
        $this->assertEquals('Excel2007', $excel->getOutputFormat());

        $excel->setOutputFormat('CSV');
        $this->assertEquals('CSV', $excel->getOutputFormat());
    }

    public function testUnsupportedOutputFormat()
    {

        $excel = new Excel();
        $type = 'NON_EXISTENT_TYPE';

        try {
            $excel->setOutputFormat($type);
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Output format not supported: ' . $type, $ex->getMessage());
        }
    }
}
