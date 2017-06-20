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
 * Set the background color for a cell
 *
 * @author Simon Ball <simonball at simonball dot me>
 */

namespace Symball\ReportBundle\Styles;

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\StyleInterface;

class Bg implements StyleInterface
{

    public function run(ReportBuilder &$context, $coordString, $options)
    {
        if (!isset($options['color'])) {
            $options['color'] = $options['default_bg_color'];
        }

        $context->sheet()
        ->getStyle($coordString)
        ->applyFromArray(['fill' => array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => $options['color']), )]);
    }
}
