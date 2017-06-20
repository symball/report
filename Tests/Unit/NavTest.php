<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\Nav;

/* Extend the default PHPUnit test case */
class NavTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $nav = new Nav();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($nav));
    }
    public function testCoordinates()
    {
        $nav = new Nav(1, 1);

        $this->assertEquals(1, $nav->column());
        $this->assertEquals(1, $nav->row());
    }
    public function testStringification()
    {

        $nav = new Nav(1, 1);

        $this->assertEquals('A1', (string) $nav);
    }
    public function testPointerMovement()
    {

        $nav = new Nav(1, 1);

        $this->assertEquals('A1', (string) $nav);
        $nav->right();
        $this->assertEquals('B1', (string) $nav);
        $nav->right(2);
        $this->assertEquals('D1', (string) $nav);
        $nav->down();
        $this->assertEquals('D2', (string) $nav);
        $nav->down(2);
        $this->assertEquals('D4', (string) $nav);
        $nav->left();
        $this->assertEquals('C4', (string) $nav);
        $nav->left(2);
        $this->assertEquals('A4', (string) $nav);
        $nav->up();
        $this->assertEquals('A3', (string) $nav);
        $nav->up(2);
        $this->assertEquals('A1', (string) $nav);
    }
    public function testPointerError()
    {

        $this->expectException('\Exception');

        $nav = new Nav(1, 1);
        $nav->up();
    }
}
