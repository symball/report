<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * The Excel service handles the start and end of spreadsheet creation
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class Excel
{
    /* @var $excelFactoryNameSpace Namespace PHPExcel Factory IO */
    private $excelFactoryNameSpace;

    /* @var $excelObject PHPExcel */
    private $excelObject;


    private $numberOfSheets = 0;
    protected $currentSheetTitle;

    private $sheet;

    /**
     *
     * @param string $defaultReportPath
     * @param string $excelFactoryNameSpace
     * @param string $outputFormat
     */
    public function __construct(
        $defaultReportPath = '',
        $excelFactoryNameSpace = '\PHPExcel_IOFactory',
        $outputFormat = 'Excel2007'
    ) {
        if ($defaultReportPath) {
            $this->setReportPath($defaultReportPath);
        }
        $this->excelFactoryNameSpace = $excelFactoryNameSpace;
        $this->setOutputFormat($outputFormat);
    }

    /**
     * Initiate a PHP Excel object
     *
     * @return $this
     */
    public function createExcelObject()
    {
        $this->excelObject = new \PHPExcel();

        return $this;
    }

    /**
     * Create a new sheet within the excel object and ready the service for it.
     * This in essence resets most things so make sure you have finished working
     * on your sheet before creating another.
     *
     * @param string $title The name of the sheet
     *
     * @return [type] [description]
     */
    public function newSheet($title = '')
    {
        if (!$this->excelObject) {
            $this->createExcelObject();
        }
        ++$this->numberOfSheets;

        // If there is already the default initiated sheet, create new sheet
        if ($this->numberOfSheets > 0) {
            $this->excelObject->createSheet($this->numberOfSheets);
        }

        // Has a title been set
        if (!$title) {
            $title = 'sheet-' . $this->numberOfSheets;
        }
        $this->setCurrentSheetTitle($title);

        $this->sheet = $this->excelObject->setActiveSheetIndex($this->numberOfSheets);
        $this->sheet->setTitle($title);

        return $this->sheet;
    }

    /**
     * Use the PHPExcel factory writer and output the current excel object in to
     * a file
     *
     * @param string $fileName
     * @param string $path
     * @param string $outputFormat
     * @return File
     * @throws \Exception When trying to use on the fly unsupported output format
     */
    public function save($fileName, $path = '', $outputFormat = '')
    {

        // If the user is trying to specify file format themself, check it is
        // usable
        if ($outputFormat) {
            if (!$this->checkOutputFormat($outputFormat)) {
                throw new \Exception('Output format not supported: ' . $outputFormat);
            }
        } else {
            $outputFormat = $this->getOutputFormat();
        }

        $writer = call_user_func(
            $this->excelFactoryNameSpace . '::createWriter',
            $this->excelObject,
            $outputFormat
        );

        if (!$path) {
            $path = $this->reportPath;
        }

        $filePath = $path . '/' . $fileName;

        $writer->save($filePath);
        $file = new File($filePath);

        return $file;
    }

    /**
     * Set a file path where the saved report will be output to.
     *
     * @param string  $path
     * @param boolean $createPath
     * @throws \Exception
     */
    public function setReportPath($path, $createPath = true)
    {
        // TODO Remove reliance on concrete Symfony file class and use interface
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($path)) {
            if ($createPath) {
                $fileSystem->mkdir($path);
            } else {
                throw new \Exception('Report path does not exist: ' . $path);
            }
        }
        $this->reportPath = $path;
    }

    /**
     * Set the "excel type" that will PHPExcel will output
     * Excel2007 / Excel5 / Excel2003XML / SYLK / OOCalc / CSV / HTML.
     *
     * @param string $format [description]
     */
    public function setOutputFormat($format)
    {

        // TODO - After converting to facade, add check supported type function
        // Check that it is one of the accepted types
        if (!$this->checkOutputFormat($format)) {
            throw new \Exception('Output format not supported: ' . $format);
        }

        $this->outputFormat = $format;
    }

    /**
     * @return string
     */
    public function getCurrentSheetTitle()
    {
        return $this->currentSheetTitle;
    }

    /**
     * @return integer
     */
    public function getNumberOfSheets()
    {
        return $this->numberOfSheets;
    }

    /**
     * @return \PHPExcel
     */
    public function getExcelObject()
    {
        return $this->excelObject;
    }

    /**
     * @return string
     */
    public function getReportPath()
    {
        return $this->reportPath;
    }

    /**
     * @return string
     */
    public function getOutputFormat()
    {
        return $this->outputFormat;
    }

    /**
     * Take a given format and check whether it is compatible with what PHPExcel
     * is able to export
     *
     * @param string $format
     * @return boolean|null
     */
    public function checkOutputFormat($format)
    {
        $supportedTypes = [
            'Excel2007',
            'Excel5',
            'Excel2003XML',
            'SYLK',
            'OOCalc',
            'CSV',
            'HTML',
        ];

        if (in_array($format, $supportedTypes)) {
            return true;
        }
    }

    /**
     * @param string $title
     */
    protected function setCurrentSheetTitle($title)
    {
        $this->currentSheetTitle = $title;
    }
}
