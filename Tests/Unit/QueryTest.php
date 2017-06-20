<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\Query;
use Doctrine\Common\Persistence\ObjectRepository;

/* Extend the default PHPUnit test case */
class QueryTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $query = new Query();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($query));
    }
    public function testSetNumberDataSets()
    {
        $query = new Query();
        $query->setNumberDataSets(2);

        $this->assertEquals(2, $query->getNumberDataSetCount());
    }
    public function testSetInvalidNumberDataSets()
    {
        $query = new Query();
        $this->expectException(\InvalidArgumentException::class);
        $query->setNumberDataSets(0);
    }
    public function testSetErroneousNumberDataSets()
    {
        $query = new Query();
        $this->expectException(\InvalidArgumentException::class);
        $query->setNumberDataSets('this is not a number!');
    }

    public function testSetRepository()
    {
        $query = new Query();

        $mockRepository = $this->createMock(ObjectRepository::class);
        $query->setRepository($mockRepository);

        $this->assertEquals($mockRepository, $query->getRepository());
    }
    public function testAddModifier()
    {
        $query = new Query();

        $options = ['value' => 'test_value', 'type' => 'equals'];
        $query->addModifier('test_field', $options);

        $this->assertEquals(['test_field' => $options], $query->getModifiers());
    }
    public function testAddInvalidModifier()
    {
        $query = new Query();
        $this->expectException(\InvalidArgumentException::class);
        $query->addModifier('test_field');
    }
}
