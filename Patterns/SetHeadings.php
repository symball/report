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

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\PatternInterface;

/**
 * This pattern draws the column headings for the current set
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class SetHeadings implements PatternInterface
{

    public function run(ReportBuilder &$context)
    {
        $nav = $context->nav();
        $meta = $context->meta();

        $nav
        ->reset('set')
        ->up(2);

        $context->write($context->query()->getTitle());

        // Get the last column of the headings
        $rangeEndColumn = $nav->column()+$meta->columnCount()-1;
        $headingRange = $nav->coord().':'.$nav->coord($rangeEndColumn, false);
        
        $context->style('merge', $headingRange);
        $nav->down();

        // Draw the main headings
        foreach ($meta->getIndex() as $key => $options) {
            // Is it hidden
            if ($options['visible'] !== true) {
                continue;
            }

            /* If auto-width on */
            if ($meta->getOption('column_auto_width') == true) {
                $context->style('width', $nav->column('current', true));
            }
            $context->write($options['title']);
            $context->style('bg', (string) $nav);

            $nav->right();
        }

        // Draw the edge line
        $start = $nav->coord('initial', 'current');
        $end = $nav->coord(($nav->column() - 1), 'current');

        $context->style('border', $start.':'.$end, ['edge' => 'bottom']);

        $nav->reset('set');
    }
}
