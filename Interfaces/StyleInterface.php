<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Interfaces;

use Symball\ReportBundle\Service\ReportBuilder;

interface StyleInterface
{
    /**
     * Perform the style option on to the report
     *
     * @param ReportBuilder $context
     * @param string        $coordString
     * @param array         $options
     */
    public function run(ReportBuilder &$context, $coordString, $options);
}
