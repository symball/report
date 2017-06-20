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
use Symball\ReportBundle\Interfaces\StyleInterface;

/**
 * The report style service acts as a front-end for predefined style operations
 * using a plug-in architecture
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class ReportStyle
{
    private $styles = array();

    /**
     * Add an extra styler object to the stack for later use
     *
     * @param StyleInterface $style
     * @param string         $alias
     */
    public function addStyle(StyleInterface $style, $alias)
    {
        $this->styles[$alias] = $style;
    }

    /**
     * A front-end for running style operations that are loaded on to the stack
     *
     * @param string        $alias
     * @param ReportBuilder $context
     * @param string        $coordString
     * @param array         $options
     * @return boolean Success condition
     * @throws \Exception when style function not available
     */
    public function run($alias, ReportBuilder &$context, $coordString, $options = array())
    {

        if (isset($this->styles[$alias])) {
            $this->styles[$alias]->run($context, $coordString, $options);

            return true;
        } else {
            throw new \Exception('Report style is not registered: ' . $alias);
        }
    }

    /**
     * Get a simple array list of the currently loaded style objects
     *
     * @return array
     */
    public function getStylesLoaded()
    {
        return array_keys($this->styles);
    }
}
