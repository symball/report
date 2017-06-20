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
 * Merge a range of cells in to one
 *
 * @author Simon Ball <simonball at simonball dot me>
 */

namespace Symball\ReportBundle\Styles;

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\StyleInterface;

class Merge implements StyleInterface
{

    public function run(ReportBuilder &$context, $coordString, $options)
    {

        $context->sheet()->mergeCells($coordString);
    }
}
