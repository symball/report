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
 * This pattern fills in the values for the various data points within the
 * current set
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class DataSet implements PatternInterface
{

    public function run(ReportBuilder &$context)
    {
        $data = $context->meta()->getDataSet();
        $nav = $context->nav();

        foreach ($data as $key => $fields) {
            foreach ($fields as $fieldKey => $options) {
                // TODO Make sanity check and don't have it in the drawing code
                if (!isset($options['visible']) || $options['visible'] !== true) {
                    continue;
                }

                $value = $options['value'];
                $context->write($value);

                // TODO Move this
                if (isset($options['display_options'])) {
                    foreach ($options['display_options'] as $option) {
                        switch ($option) {
                            case 'highlight_positive':
                                if (0 < $value) {
                                    
                                    $color = $context->meta()->getOption('positive_color');
                                    $context->style('bg', (string) $nav, [
                                        'color' => $color
                                    ]);
                                }

                                break;
                            case 'highlight_negative':
                                if (0 > $value) {
                                    $color = $context->meta()->getOption('negative_color');
                                    $context->style('bg', (string) $nav, [
                                        'color' => $color
                                    ]);
                                }
                                break;
                        }
                    }
                }
                $nav->right();
            }
            $nav->next();
        }
        // Draw the edge line
        $start = $nav->coord('current', 'initial');
        $end = $nav->coord('current', ($nav->row() - 1));

        $context->style('border', $start.':'.$end, ['edge' => 'left']);
    }
}
