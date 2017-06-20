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

interface QueryInterface
{
    /**
     * Stringify the current dataset
     */
    public function getTitle();

    /**
     * Extra actions to perform on the query builder
     */
    public function prepareQuery();
}
