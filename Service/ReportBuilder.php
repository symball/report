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

use Symball\ReportBundle\Service\ReportPattern;
use Symball\ReportBundle\Service\ReportStyle;
use Symball\ReportBundle\Service\Meta;
use Symball\ReportBundle\Interfaces\NavInterface;
use Symball\ReportBundle\Interfaces\QueryInterface;

/**
 * The report builder service acts as a facade bringing together the various
 * functionality that will be present when creating reports and some shortcut
 * functions
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ReportBuilder
{

    private $meta;
    private $excelService;
    private $sheet;
    private $nav;
    private $query;
    private $status;
    private $style;

    /**
     * @param object       $excelService
     * @param Meta         $meta
     * @param NavInterface $nav
     */
    public function __construct($excelService, Meta $meta, NavInterface $nav, ReportStyle $style)
    {
        $this->excelService = $excelService;
        $this->meta = $meta;
        $this->nav = $nav;
        $this->style = $style;
    }

    /**
     * Set the database query handler
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function setQuery(QueryInterface $query)
    {
        $this->query = $query;
        
        return $this;
    }

    /**
     * Set the navigation handler
     *
     * @param NavInterface $nav
     * @return $this
     */
    public function setNav(NavInterface $nav)
    {
        $this->nav = $nav;
        
        return $this;
    }

    /**
     * Fill in a literal value in the cell at current nav pointer
     *
     * @param type $value
     */
    public function write($value)
    {
        $this->sheet->setCellValue((string) $this->nav, $value);
    }

    /**
     * Fill in a cell with a custom or ready made formula at current nav pointer
     *
     * @param type $value
     * @param type $options
     */
    public function formula($value, $options)
    {

        // TODO Refactor in to a service which detects shortcuts or paste raw
        switch ($value) {
            case 'sum_up_data':
                $start = $this->nav('current', 'initial');
                $end = $this->nav('current', ($this->nav->row() + 1));
                $formula = '=SUM(' . $start . ':' . $end . ')';
                break;

            default:
                $formula = $value;
                break;
        }
        $this->write($formula, $options);
    }

    /**
     * Set or return the current run status of the report builder
     *
     * @param type $newStatus
     * @return $this
     */
    public function status($newStatus = false)
    {
        if ($newStatus) {
            $this->status = $newStatus;
            
            return $this;
        } else {
            
            return $this->status;
        }
    }

    /**
     * Facade function for creating a new spreadsheet within the current excel
     * If it is not the first, the query object will also be reset back to
     * initial status
     *
     * @param type $title
     * @return $this
     */
    public function createSheet($title = '')
    {
        $this->sheet = $this->excelService->newSheet($title);
        if ($this->status() == 'running') {
            $this->query->reset();
        }

        return $this;
    }

    /**
     * Used to prepare the service for an iteration of writing. This should be
     * called prior to any data manipulation as it will reset the data tracking
     * array.
     *
     * @return boolean outcome of operation
     */
    public function newSet()
    {
        // Check whether the report is using query and whether any sets left
        if ($this->query && !$this->query->tick()) {
            return false;
        }

        // Is there cause to move the poingter
        if ($this->status() == 'running') {
            $this->nav->movePointerAlong($this->meta->columnCount());
            $this->meta->clear();
        } else {
            $this->status('running');
        }

        return true;
    }

    /**
     * Facade function for saving the spreadsheet
     *
     * @param string $fileName
     * @param string $path
     * @param string $outputFormat
     * @return File
     */
    public function save($fileName, $path = '', $outputFormat = '')
    {
        return $this->excelService->save($fileName, $path, $outputFormat);
    }

    /**
     * Facade function for applying style to the spreadsheet. Will also merge
     * options with user overrides before calling
     *
     * @param string $reference
     * @param string $coordString
     * @param array  $options
     */
    public function style($reference, $coordString, $options = array())
    {
        // Combine any options from the meta class with function call
        $options = array_merge($this->meta->getOptions(), $options);
        $this->style->run($reference, $this, $coordString, $options);
    }
    /**
     * Return the navigation service object
     *
     * @return NavInterface
     */
    public function nav()
    {
        return $this->nav;
    }

    /**
     * Return the meta data broker service object
     *
     * @return Meta
     */
    public function meta()
    {
        return $this->meta;
    }

    /**
     * Return the current sheet object. This is for low level control
     *
     * @return object
     */
    public function sheet()
    {
        return $this->sheet;
    }

    /**
     * Return the Excel service directly
     *
     * @return Excel
     */
    public function excel()
    {
        return $this->excel;
    }

    /**
     * Return the data handling query service
     *
     * @return QueryInterface
     */
    public function query()
    {
        return $this->query;
    }
}
