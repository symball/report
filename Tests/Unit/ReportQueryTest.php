<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\ReportQuery;
use Symball\ReportBundle\Interfaces\QueryInterface;

/* Extend the default PHPUnit test case */
class QueryTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $query = new ReportQuery();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($query));
    }
    
    public function testAddQuery()
    {
        $reportQuery = new ReportQuery();
        
        $mockQuery = $this->createMock(QueryInterface::class);
        $reportQuery->addQuery($mockQuery, 'an_alias');

        $this->assertEquals(['an_alias'], $reportQuery->getQueriesLoaded());
    }
    
    public function testGetQuery()
    {
        $reportQuery = new ReportQuery();

        $mockQuery = $this->createMock(QueryInterface::class);
//        $mockQuery->method('run')->willReturn(true);
        $reportQuery->addQuery($mockQuery, 'an_alias');

        $queryService = $reportQuery->get('an_alias');

        $this->assertEquals($mockQuery, $queryService);
    }
    public function testGetNonExistentQuery()
    {
        $reportQuery = new ReportQuery();

        try {
            $result = $reportQuery->get('an_alias');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Query type is not registered: an_alias', $ex->getMessage());
        }
    }
    
}
