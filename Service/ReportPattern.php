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

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\PatternInterface;

/**
 * The report pattern service acts as a front-end for predefined workflows
 * using a plug-in architecture
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ReportPattern
{


    private $patterns = array();

    /**
     * Add an extra patter object to the stack for later use
     *
     * @param PatternInterface $pattern
     * @param string           $alias
     */
    public function addPattern(PatternInterface $pattern, $alias)
    {
        $this->patterns[$alias] = $pattern;
    }

    /**
     * A front-end for running pattern operations that are loaded on to the stack
     *
     * @param string        $alias
     * @param ReportBuilder $context
     * @return boolean Success condition
     * @throws \Exception when pattern not available
     */
    public function run($alias, ReportBuilder &$context)
    {
        if (isset($this->patterns[$alias])) {
            $this->patterns[$alias]->run($context);
            
            return true;
        } else {
            throw new \Exception('Report pattern is not registered: ' . $alias);
        }
    }

    /**
     * Get a simple array list of the currently loaded pattern objects
     *
     * @return array
     */
    public function getPatternsLoaded()
    {
        return array_keys($this->patterns);
    }
}
