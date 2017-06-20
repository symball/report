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
 * Set the width of a given cell
 *
 * @author Simon Ball <simonball at simonball dot me>
 */

namespace Symball\ReportBundle\Styles;

use Symball\ReportBundle\Service\ReportBuilder;
use Symball\ReportBundle\Interfaces\StyleInterface;

class Width implements StyleInterface
{
    
    public function run(ReportBuilder &$context, $coordString, $options) {
        
        switch ($options['type']) {
        case 'auto':
          $context->sheet()
          ->getColumnDimension($coordString)
          ->setAutoSize(true);
          break;

        default:
          throw new \Exception('Unrecognised style width option: '.$options['type']);
          break;
      }
    }
}
