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

use Symball\ReportBundle\Interfaces\QueryInterface;

/**
 * The report query service acts as a front-end for fetching query types
 * using a plug-in architecture
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ReportQuery
{


    private $queries = array();

    /**
     * Add an extra patter object to the stack for later use
     *
     * @param QueryInterface $query
     * @param string           $alias
     */
    public function addQuery(QueryInterface $query, $alias)
    {
        $this->queries[$alias] = $query;
    }

    /**
     * A front-end for running pattern operations that are loaded on to the stack
     *
     * @param string        $alias
     * @param ReportBuilder $context
     * @return boolean Success condition
     * @throws \Exception when pattern not available
     */
    public function get($alias)
    {
        if (isset($this->queries[$alias])) {
            
            return $this->queries[$alias];
        } else {
            throw new \Exception('Query type is not registered: ' . $alias);
        }
    }

    /**
     * Get a simple array list of the currently loaded pattern objects
     *
     * @return array
     */
    public function getQueriesLoaded()
    {
        return array_keys($this->queries);
    }
}
