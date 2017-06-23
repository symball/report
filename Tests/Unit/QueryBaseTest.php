<?php

/*
 * This file is part of
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle\Service;

/**
 * Description of QueryBase
 *
 * @author Simon Ball <simonball at simonball dot me>
 */
class QueryBase {
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
