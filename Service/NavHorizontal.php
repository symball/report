<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Service;

use Symball\ReportBundle\Service\Nav;
use Symball\ReportBundle\Interfaces\NavInterface;

/**
 * Handle the navigation pointer when data sets will go right and data points
 * will go down
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class NavHorizontal extends Nav implements NavInterface
{

    const TYPE = 'horizontal';

    /**
     * Move the pointer to the starting area of the "next" data set.
     *
     * @return $this
     */
    public function next()
    {
        $this->columnCurrent = $this->columnSetStart;
        ++$this->rowCurrent;
        $this->rowSetStart = $this->rowCurrent;

        return $this;
    }

    /**
     * Move pointer along the current X axis. For horizontal nav, this is down
     *
     * @param integer $placesToMove
     * @return $this
     */
    public function axisXMove($placesToMove = 1)
    {
        $this->down($placesToMove);

        return $this;
    }

    /**
     * Move pointer along the current Y axis. For horizontal nav, this is right
     *
     * @param integer $placesToMove
     * @return $this
     */
    public function axisYMove($placesToMove = 1)
    {
        $this->right($placesToMove);

        return $this;
    }
}
