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

/**
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
interface NavInterface
{
    /**
     * Move the pointer to the next data point
     */
    public function next();

    /**
     * Move the pointer along the x axis one or more places
     *
     * @param integer $placesToMove
     */
    public function axisXMove($placesToMove = 1);

    /**
     * Move the pointer along the y axis one or more places
     *
     * @param integer $placesToMove
     */
    public function axisYMove($placesToMove = 1);
}
