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

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * The query class is responsible for performing information gathering operations
 * and typically involves a database query
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class Query
{
    protected $numberDataSets = 1;
    protected $currentDataSet = 0;
    protected $modifiers = [];
    protected $queryBase;
    
    /**
     * Stringify the current data set
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }
            
    /**
     * Set the tracking parameters back to their base value
     * @return $this
     */
    public function reset()
    {
        $this->numberDataSets = 1;
        $this->currentDataset = 0;
        
        return $this;
    }

    /**
     * Inform the service how many rounds the report will go through
     *
     * @param integer $count
     * @return $this
     * @throws \InvalidArgumentException if count not an integer, zero or less
     */
    public function setNumberDataSets($count)
    {
        if (!is_int($count)) {
            throw new \InvalidArgumentException('Must be an integer');
        }
        if ($count < 1) {
            throw new \InvalidArgumentException('Cannot have a negative number of sets');
        }
        $this->numberDataSets = $count;
        
        return $this;
    }

    /**
     * Attempt to move the query counter forward and halt operations if at end
     *
     * @return boolean True if can continue
     */
    public function tick()
    {
        ++$this->currentDataSet;
        if ($this->currentDataSet <= $this->numberDataSets) {
            return true;
        }

        // TODO shutdown procedure on query interface?
        return false;
    }

    /**
     * Set the Doctrine query repository to be used
     *
     * @param ObjectRepository $repository
     * @return $this
     */
    public function setRepository(ObjectRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set the Query Builder object which will form the basis of all information
     * gathering
     *
     * @param object $query
     * @return $this
     */
    public function setQueryBase($query)
    {
        // TODO Check if it is really a querybuilder once storage agnostic
        $this->queryBase = $query;

        return $this;
    }

    /**
     * Influence the current query that will be run by adding modifier conditions
     *
     * @param string $field   The database field
     * @param array  $options Options associated with the modifier
     * @return $this
     * @throws \InvalidArgumentException   If minimum options are not set
     */
    public function addModifier($field, $options = [])
    {

        if (!isset($options['type']) || !isset($options['value'])) {
            throw new \InvalidArgumentException('Modifier must have at least type and value present');
        }

        $this->modifiers[$field] = $options;

        return $this;
    }

    /**
     * Using the query base and available modifiers, create the base part of the
     * database query to be run in the current data set
     *
     * @return object QueryBuilder
     * @throws \Exception If an unrecognized query modifier is being used
     */
    public function prepareQuery()
    {

        $query = clone $this->queryBase;
        foreach ($this->getModifiers() as $key => $options) {
            switch (strtoupper($options['type'])) {
                case 'EQUALS':
                    $query->addAnd(
                        $query->expr()
                        ->field($key)
                        ->equals($options['value'])
                    );
                    break;

                case 'REFERENCES':
                    $query->addAnd(
                        $query->expr()
                        ->field($key)
                        ->references($options['value'])
                    );
                    break;

                default:
                    throw new \Exception('Query modifier for: ' . $key . ' not recognised:' . $options['type']);
            }
        }

        return $query;
    }

    /**
     * Attempt to build the actual database operation and return the result
     * @return type
     * @throws \Exception
     */
    public function run()
    {

        if (!$this->repository) {
            throw new \Exception('Set a repository before trying to run query');
        }

        // If no base query has been set, create a default one
        if (!$this->queryBase) {
            $this->queryBase = $this->repository->createQueryBuilder();
        }

        $query = $this->prepareQuery();

        // Empty the modifiers after the query has been run
        $this->modifiers = [];

        return $query->getQuery()->execute();
    }

    /**
     * Return the Doctrine repository object
     * @return object
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Return the current set counter
     *
     * @return integer
     */
    public function getNumberDataSetCurrent()
    {
        return $this->currentDataSet;
    }

    /**
     * Return the total number of data sets to be run
     *
     * @return integer
     */
    public function getNumberDataSetCount()
    {
        return $this->numberDataSets;
    }

    /**
     * Return the current modifiers in their array definition format
     *
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }
    /**
     * Display the set heading
     * 
     * @return string
     */
    public function getTitle()
    {
        return '';
    }
}
