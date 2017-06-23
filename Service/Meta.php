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
 * The meta service acts as a data broker in which the column headings, data 
 * points and various options are handled
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class Meta
{

    protected $dataPoints = array();
    protected $currentDataPoint;
    protected $currentDataSet = array();
    protected $dataPointMeta;
    protected $options = array(
        'default_bg_color' => 'e9e9e9',
        'default_line_thickness' => 'thick',
        'positive_color' => 'd8ffb7',
        'negative_color' => 'ffc56c',
    );
    private $columnCount = 0;

    /**
     * Define an option and its value.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this For method chaining
     * @throws \InvalidArgumentException if key type is not a string or integer
     */
    public function setOption($key, $value)
    {
        if (is_int($key) || is_string($key)) {
            $this->options[$key] = $value;
        } else {
            throw new \InvalidArgumentException('Option key must be an integer or string. ' . gettype($key) . ' given');
        }

        return $this;
    }

    /**
     * Set a group of options at the same time
     *
     * @param array $options
     * @return $this For method chaining
     * @throws \InvalidArgumentException If input is not an array
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            throw new \InvalidArgumentException('Options must be an array. ' . gettype($options) . ' given');
        }

        return $this;
    }

    /**
     * Get the value of a single option or return false if it doesn't exist
     *
     * @param string|integer $key
     * @return string|integer|boolean The option value
     */
    public function getOption($key)
    {
        if ($key && isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * Retrieve the full option array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Reset data set values for all data points back to the defaults as in the
     * column definition
     */
    public function clear()
    {
        $this->currentDataSet = array();
        $this->currentDataSet = array_fill_keys(
            $this->dataPoints,
            $this->dataPointMeta
        );
    }

  /**
   * Increment the value for a key that makes up part of the currently focused
   * data object.
   *
   * @param string $key   The index to use
   * @param int    $value The value to increment by
   * @return $this
   * @throws \Exception if there is no data point, column or invalid type
   */
    public function increment($key, $value = 1)
    {
        if (!$this->currentDataPoint) {
            throw new \Exception('No data point in focus');
        }

        if (!isset($this->currentDataSet[$this->currentDataPoint][$key])) {
            throw new \Exception($key . ' has not been defined');
        }

        if ($this->currentDataSet[$this->currentDataPoint][$key]['type'] !== 'integer') {
            throw new \Exception($key . ' is not a numeric column');
        }

        if (!is_int($value)) {
            throw new \Exception('Can only increment using a numeric value');
        }

        $this->currentDataSet[$this->currentDataPoint][$key]['value'] += $value;

        return $this;
    }

  /**
   * Directly set the value for a key that makes up part of the currently focused
   * data object.
   *
   * @param string         $key   The index to use
   * @param string|integer $value The value to set
   * @return $this
   * @throws \Exception
   */
    public function set($key, $value)
    {
        if (!$this->currentDataPoint) {
            throw new \Exception('No data point in focus');
        }

        if (!isset($this->currentDataSet[$this->currentDataPoint][$key])) {
            throw new \Exception($key . ' has not been defined');
        }
        $this->currentDataSet[$this->currentDataPoint][$key]['value'] = $value;

        return $this;
    }

  /**
   * Set a focus for the current data object for which data can be manipulated.
   * If the key doesn't exist, it will be created using default column values.
   *
   * @param string $key The reference key to be used
   * @return $this
   */
    public function setPoint($key)
    {
        if (!isset($this->currentDataSet[$key])) {
            $this->addPoint($key);
        }
        $this->currentDataPoint = $key;

        return $this;
    }

  /**
   * Add a data point which will be shown on the report. This function should be
   * used prior to any kind of data manipulation as it will not setup a data
   * structure for manipulation.
   *
   * @param string $key The index to use
   * @return $this
   */
    public function addPoint($key)
    {
        if (!in_array($key, $this->dataPoints)) {
            $this->dataPoints[] = $key;
            $this->currentDataSet[$key] = $this->dataPointMeta;
        }

        return $this;
    }

  /**
   * Define a type of data that will be presented on the report or metadata that
   * will be used for calculations. In the latter case, within the options array
   * set "visible" to false.
   * At a minimum, the key needs to be set. In this case, the type will be string
   * and the key converted in to a title
   * .
   *
   * @param string $key          A reference key for the piece of data
   * @param string|integer $defaultValue The default also defines type of column
   * @param array  $options      Additional parameters for the column to use
   *
   * @return $this
   */
    public function column(
        $key,
        $defaultValue = '',
        $options = array()
    ) {

        $options['value'] = $defaultValue;

        // Auto guessing the type?
        if (!isset($options['type'])) {
            $options['type'] = gettype($defaultValue);
        }
        // Check whether hidden
        if (!isset($options['visible'])) {
            $options['visible'] = true;
        }

        // Does it have a title
        if (!isset($options['title']) && $options['visible'] == true) {
            $options['title'] = ucwords(str_replace('_', ' ', $key));
        }

        $this->dataPointMeta[$key] = $options;

        if ($options['visible'] == true) {
            $this->columnCount++;
        }

        return $this;
    }

    /**
   * Manually set the data which will be used for the current set. when
   * using this function, it will expect an associative array of arrays where
   * each entry has a further array of values which have a key matching that of
   * the meta data.
   * TODO - This is quite messy, rewrite more efficiently.
   *
   * @param array $inputData A set of data which conforms to the column specification
   * @return $this
   */
    public function setData($inputData)
    {
        // Combine raw data with the meta data
        foreach ($inputData as $key => $values) {
            $data = [];
            foreach ($this->dataPointMeta as $fieldKey => $field) {
                $data[$fieldKey] = array_merge($field, ['value' => $values[$fieldKey]]);
            }
            $this->currentDataSet[$key] = $data;
        }

        return $this;
    }

    /**
     * Return the information array relating to a specific column
     *
     * @param string|integer $key
     * @return array
     * @throws \Exception
     */
    public function columnInfo($key)
    {
        if (isset($this->dataPointMeta[$key])) {
            return $this->dataPointMeta[$key];
        } else {
            throw new \Exception($key . ' column has not been defined');
        }
    }

    /**
     * Retrieve the complete meta information relating to a data point
     *
     * @return array
     * @throws \Exception if the current data set is pointer-less
     */
    public function getPoint()
    {
        if (isset($this->currentDataSet[$this->currentDataPoint])) {
            return $this->currentDataSet[$this->currentDataPoint];
        } else {
            throw new \Exception('No data point to retrieve');
        }
    }

    /**
     * Return a specific piece of data from the current data point
     *
     * @param string|integer $key
     * @return mixed
     */
    public function getPointValue($key)
    {
        $point = $this->getPoint();

        return $point[$key]['value'];
    }

    /**
     * Return the number of data columns that have been defined
     *
     * @return integer
     */
    public function columnCount()
    {
        return count($this->dataPointMeta);
    }

    /**
     * Return the number of data points that have been defined
     *
     * @return integer
     */
    public function dataCount()
    {
        return count($this->currentDataSet);
    }

    /**
     * Return all the column information
     *
     * @return array
     */
    public function getIndex()
    {
        return $this->dataPointMeta;
    }

    /**
     * Return all the data from the current set
     *
     * @return array
     */
    public function getDataSet()
    {
        return $this->currentDataSet;
    }

    /**
     * Return the key for the current data point in focus
     *
     * @return string
     */
    public function getPointKey()
    {
        return $this->currentDataPoint;
    }
}
