<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Patterns;

use Symball\ReportBundle\Interfaces\PatternInterface;
use Symball\ReportBundle\Service\ReportBuilder;

/**
 * This pattern draws the data point headings along the x axis
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class DataPointIndex implements PatternInterface
{
    /**
     * The main function for creating the data point index
     *
     * @param ReportBuilder $context
     */
    public function run(ReportBuilder &$context)
    {
        $nav = $context->nav();
        $meta = $context->meta();

        $nav
        ->reset('initial')
        ->left();

        foreach ($meta->getDataSet() as $key => $options) {
            $context->write($key);
            $context->style('bg', (string) $nav);

            $nav->axisXMove();
        }

        // Draw the edge line
        $start = $nav->coord('current', 'initial');
        $end = $nav->coord('current', ($nav->row() - 1));
        $coordString = $start.':'.$end;
        
        $context->style('border', $coordString, ['edge' => 'right']);
        $context->style('align', $coordString, ['edge' => 'right']);

        $nav->reset('set');
    }
}
