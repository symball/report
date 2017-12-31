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

/**
 * The navigation service is responsible for handling the position of the
 * spreadsheet pointer
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
abstract class Nav
{

    /**
     * @var int Used to determine the initial column
     */
    protected $columnInitial;

    /**
     * @var int Used to determine the initial row
     */
    protected $rowInitial;

    /**
     * @var int Tracks the column to go to when the user resets
     */
    protected $columnSetStart;

    /**
     * @var int Tracks the row to go to when the user resets
     */
    protected $rowSetStart;

    /**
     * @var int Head column
     */
    protected $columnCurrent;

    /**
     * @var int Head row
     */
    protected $rowCurrent;

    protected $status = 'initial';

    protected $columnCount = 0;

    /**
     * @param int $columnInitial Determine the starting column to use
     * @param int $rowInitial    Determine the starting row to use
     */
    public function __construct($columnInitial = 2, $rowInitial = 3)
    {
        // Set all tracking data to the preset
        $this->columnInitial = $this->columnSetStart = $this->columnCurrent = $columnInitial;
        $this->rowInitial = $this->rowSetStart = $this->rowCurrent = $rowInitial;
    }

    /**
     * Move the cursor position to the starting area for the next data point or
     * later depending on the argument passed
     *
     * @param integer $count
     * @return $this
     */
    public function movePointerAlong($count = 1)
    {

        $this
        ->columnReset('set')
        ->rowReset('initial')
        ->axisYMove($count)
        ->setStartColumn();

        return $this;
    }

    /**
     * @return string Get the current coordinates in an excel friendly manner
     */
    public function __toString()
    {
        return $this->coord();
    }

    /**
     * Move the pointer up x (default 1) amount of places.
     *
     * @param int $placesToMove
     * @return $this
     */
    public function up($placesToMove = 1)
    {
        // Check for validity of movement
        if (0 >= ($this->rowCurrent - $placesToMove)) {
            throw new \Exception('Movement would go out of bounds');
        }
        $this->rowCurrent -= $placesToMove;

        return $this;
    }

    /**
     * Move the pointer right x (default 1) amount of places.
     *
     * @param int $placesToMove
     * @return $this
     */
    public function right($placesToMove = 1)
    {
        $this->columnCurrent += $placesToMove;

        return $this;
    }

    /**
     * Move the pointer down x (default 1) amount of places.
     *
     * @param int $placesToMove
     *
     * @return $this
     */
    public function down($placesToMove = 1)
    {
        $this->rowCurrent += $placesToMove;

        return $this;
    }

    /**
     * Move the pointer left x (default 1) amount of places.
     *
     * @param int $placesToMove
     *
     * @return $this
     */
    public function left($placesToMove = 1)
    {
        if (0 >= ($this->columnCurrent - $placesToMove)) {
            throw new \Exception('Movement would go out of bounds');
        }

        $this->columnCurrent -= $placesToMove;

        return $this;
    }

    /**
     * Reset the row pointer to starting area of either spreadsheet or set
     *
     * @param string $type
     * @return $this
     */
    public function rowReset($type = 'initial')
    {
        switch ($type) {
            case 'initial':
                $this->rowCurrent = $this->rowSetStart = $this->rowInitial;
                break;

            case 'set':
                $this->rowCurrent = $this->rowSetStart;
                break;

            default:
                break;
        }

        return $this;
    }

    /**
     * Reset the column pointer to starting area of either spreadsheet or set
     *
     * @param string $type
     * @return $this
     */
    public function columnReset($type = 'initial')
    {
        switch ($type) {
            case 'initial':
                $this->columnCurrent = $this->columnSetStart = $this->columnInitial;
                break;

            case 'set':
                $this->columnCurrent = $this->columnSetStart;
                break;

            default:
                break;
        }

        return $this;
    }

    /**
     * Reset both the and column pointer to either spreadsheet or set
     *
     * @param string $type The human friendly syntax for what position to use
     * @return $this
     */
    public function reset($type = 'intitial')
    {
        $this->rowReset($type);
        $this->columnReset($type);

        return $this;
    }

    /**
     * Update the current entry for what is considered the starting column.
     *
     * @return $this
     */
    public function setStartColumn()
    {
        $this->columnSetStart = $this->columnCurrent;

        return $this;
    }

    /**
     * Get the spreadsheet object coord from a numeric value
     * If no parameters are used, the context coord will be returned.
     *
     * @param string|integer $column Either integer index or human friendly syntax
     * @param string|integer $row    Either integer index or human friendly syntax
     *
     * @return string|interger [description]
     */
    public function coord($column = false, $row = false)
    {
        if (!$column) {
            $column = $this->columnCurrent;
        }
        if (!$row) {
            $row = $this->rowCurrent;
        }

        $column = $this->parseNavReference($column, 'column');
        $row = $this->parseNavReference($row, 'row');

        return $this->nmbToClm($column) . $row;
    }

    /**
     * Parse a number in to letter format.
     *
     * @param int $num
     *
     * @return string
     */
    public function nmbToClm($num)
    {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->nmbToClm($num2) . $letter;
        } else {
            return $letter;
        }
    }

    /**
     * A helper function for returning numerical coordinates.
     *
     * @param string $input
     * @param string $axis
     *
     * @return integer
     */
    public function parseNavReference($input = '', $axis = 'column')
    {
        switch ($input) {
            case 'initial':
                $propertyName = $axis . 'Initial';
                break;

            case 'set':
                $propertyName = $axis . 'Start';
                break;

            case 'current':
                $propertyName = $axis . 'Current';
                break;

            default:
                if (is_int($input)) {
                    return $input;
                }
                break;
        }

        if(isset($propertyName)) {
          return $this->$propertyName;
        }
    }

    /**
     * Return the column value according to various criteria, defaulting to
     * current.
     *
     * @param string $type
     * @param bool   $asLetter
     *
     * @return string|integer The column pointer as either index or letter
     */
    public function column($type = 'current', $asLetter = false)
    {
        if ($type == false || $type == '') {
            $type = 'current';
        }

        $column = $this->parseNavReference($type);

        if ($asLetter) {
            return $this->nmbToClm($column);
        } else {
            return $column;
        }
    }

    /**
     * Return the row value according to various criteria, defaulting to current.
     *
     * @param string $type
     *
     * @return int The row index
     */
    public function row($type = 'current')
    {
        return $this->parseNavReference($type, 'row');
    }
}
