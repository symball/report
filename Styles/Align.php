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
 * This function aligns the content within a cell
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
namespace Symball\ReportBundle\Styles;

use Symball\ReportBundle\Interfaces\StyleInterface;
use Symball\ReportBundle\Service\ReportBuilder;

class Align implements StyleInterface
{

    public function run(ReportBuilder &$context, $coordString, $options)
    {

        $context->sheet()
        ->getStyle($coordString)
        ->getAlignment()
        ->setHorizontal($options['edge']);
    }
}
