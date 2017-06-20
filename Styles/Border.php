<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

/**
 * Create a border along a range of cells
 *
 * @author Simon Ball <simonball at simonball dot me>
 */

namespace Symball\ReportBundle\Styles;

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\StyleInterface; 

class Border implements StyleInterface
{
    /**
     * Take a coordinate range and paint a border along it
     *
     * @param ReportBuilder $context
     * @param string        $coordString
     * @param array         $options
     * @return type
     * @throws \InvalidArgumentException if options array is missing edge
     */
    public function run(ReportBuilder &$context, $coordString, $options)
    {
        if (!isset($options['edge'])) {
            throw new \InvalidArgumentException('Must set edge to use in options array');
        }

        if (!isset($options['thickness'])) {
            $options['thickness'] = $options['default_line_thickness'];
        }
        $context->sheet()
        ->getStyle($coordString)
        ->applyFromArray(
            ['borders' => [
                $options['edge'] => [
            'style' => $options['thickness']]]]
        );
    }
}
