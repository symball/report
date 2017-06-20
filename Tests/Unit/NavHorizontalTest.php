<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\NavHorizontal;

/* Extend the default PHPUnit test case */
class NavHorizontalTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $nav = new NavHorizontal();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($nav));
    }
    public function testNext()
    {
        $nav = new NavHorizontal(1, 1);
        $nav->next();

        $this->assertEquals(1, $nav->column());
        $this->assertEquals(2, $nav->row());
    }
    public function testAxisXMove()
    {
        $nav = new NavHorizontal(1, 1);
        $nav->axisXMove();

        $this->assertEquals(1, $nav->column());
        $this->assertEquals(2, $nav->row());
    }
    public function testAxisYMove()
    {
        $nav = new NavHorizontal(1, 1);
        $nav->axisYMove();

        $this->assertEquals(2, $nav->column());
        $this->assertEquals(1, $nav->row());
    }
}
