<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Symball\ReportBundle\Tests\Unit;

/* The base PHPUnit test class */
use PHPUnit\Framework\TestCase;

use Symball\ReportBundle\Service\Meta;

/* Extend the default PHPUnit test case */
class MetaTest extends TestCase
{
    /* Test that posts can be instantiated */
    public function testCreation()
    {
        /* Create a post */
        $meta = new Meta();
        /* Check that it is an object type */
        $this->assertEquals(true, is_object($meta));        
    }
    public function testOption() {
        $meta = new Meta();
        $meta->setOption('test_key', 'test_value');
        
        $this->assertEquals('test_value', $meta->getOption('test_key'));
        
        $meta->setOption('test_key', 'a new value');
        $this->assertEquals('a new value', $meta->getOption('test_key'));
    }
    public function testInvalidOptionKey() {
        $meta = new Meta();
        
        try {
            $meta->setOption(['invalid'], 'test_value');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Option key must be an integer or string. array given', $ex->getMessage());
        }        
    }
    
    public function testOptions() {
        $meta = new Meta();
        
        $meta->setOption('test_key', 'test_value');
        $this->assertEquals('test_value', $meta->getOption('test_key'));
        
        $meta->setOptions([
            'second_key' => 'a second value',
            'third_key' => 'a third value']);
        
        $this->assertEquals('a second value', $meta->getOption('second_key'));
        $this->assertEquals('test_value', $meta->getOption('test_key'));
        
        $meta->setOption('test_key', 'a new value for this option');
        $this->assertEquals('a new value for this option', $meta->getOption('test_key'));
                
    }
    
    public function testInvalidOptions() {
        $meta = new Meta();
        
        try {
            $meta->setOptions('invalid');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Options must be an array. string given', $ex->getMessage());
        }        
    }
    
    public function testStringColumn() {
        $meta = new Meta();
        $meta->column('first_column');
        
        $this->assertEquals([
            'value' => '',
            'type' => 'string',
            'visible' => true,
            'title' => 'First Column'], $meta->columnInfo('first_column'));
    }
    
    public function testIntegerColumn() {
        $meta = new Meta();
        $meta->column('first_column', 0);
        
        $this->assertEquals([
            'value' => 0,
            'type' => 'integer',
            'visible' => true,
            'title' => 'First Column'], $meta->columnInfo('first_column'));
        
        $this->assertEquals(1, $meta->columnCount());
    }
    
    public function testMultipleColumns() {
        $meta = new Meta();
        $meta->column('first_column');
        $meta->column('second_column');
        
        $this->assertEquals(2, $meta->columnCount());
    }
    public function testInvisibleColumn() {
        $meta = new Meta();
        $meta->column('first_column', '', ['visible' => false]);
        
        $this->assertEquals([
            'value' => '',
            'type' => 'string',
            'visible' => false], $meta->columnInfo('first_column'));
        
        $this->assertEquals(1, $meta->columnCount());
    }
    public function testColumnWithOptions() {
        $meta = new Meta();
        $meta->column('first_column', '', ['extra_option' => true]);
        
        $this->assertEquals([
            'value' => '',
            'type' => 'string',
            'visible' => true,
            'title' => 'First Column',
            'extra_option' => true], $meta->columnInfo('first_column'));
        
        $this->assertEquals(1, $meta->columnCount());        
    }
    public function testAddDataPoint() {
        $meta = new Meta();
        $meta->addPoint('test');
        
        $this->assertEquals(1, $meta->dataCount());
        
        $meta->addPoint('another');
        $this->assertEquals(2, $meta->dataCount());
    }
    
    public function testSetPoint() {
        $meta = new Meta();
        $meta->setPoint('test');
        $this->assertEquals('test', $meta->getPointKey());        
        
    }
    
    public function testSetPointData() {
        $meta = new Meta();
        $meta
        ->column('first_column')
        ->setPoint('test')
        ->set('first_column', 'test value');
        
        $this->assertEquals(['first_column' => [
            'value' => 'test value',
            'type' => 'string',
            'visible' => true,
            'title' => 'First Column'
        ]], $meta->getPoint());
    }
    
    public function testSetInvalidPointData() {
        $meta = new Meta();
        
        try {
            $meta
            ->setPoint('test')
            ->set('first_column', 'test value');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('first_column has not been defined', $ex->getMessage());
        }  
    }
    
    public function testRetrievePointDataValue() {
        $meta = new Meta();
        $meta
        ->column('first_column')
        ->setPoint('test')
        ->set('first_column', 'test value');
        
        $this->assertEquals('test value', $meta->getPointValue('first_column'));
    }
    
    public function testIncrementPointData() {
        $meta = new Meta();
        $meta
        ->column('first_column', 0)
        ->setPoint('test')
        ->increment('first_column', 1);
        
        $this->assertEquals(1, $meta->getPointValue('first_column'));
        
        $meta->increment('first_column', 100);
        $this->assertEquals(101, $meta->getPointValue('first_column'));
        
    }
    
    public function testIncrementStringPointdata() {
        $meta = new Meta();
        
        try {
            $meta
            ->column('first_column')
            ->setPoint('test')
            ->increment('first_column', 100);
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('first_column is not a numeric column', $ex->getMessage());
        } 
    }
    
    public function testIncrementWithStringPointdata() {
        $meta = new Meta();
        
        try {
            $meta
            ->column('first_column', 0)
            ->setPoint('test')
            ->increment('first_column', 'SOME_STRING');
        } catch (\Exception $ex) {
            // General exception
            $this->assertEquals(0, $ex->getCode());
            $this->assertEquals('Can only increment using a numeric value', $ex->getMessage());
        } 
    }
    
    public function testGetData() {
        $meta = new Meta();
        $meta
        ->column('first_column')
        ->setPoint('test')
        ->set('first_column', 'test value');
        
        $this->assertEquals([
            'test' => [
                'first_column' => [
                    'value' => 'test value',
                    'type' => 'string',
                    'visible' => true,
                    'title' => 'First Column']]], $meta->getDataSet());
        
        $meta
        ->setPoint('second_point')
        ->set('first_column', 'test value');
        
        $this->assertEquals([
            'test' => [
                'first_column' => [
                    'value' => 'test value',
                    'type' => 'string',
                    'visible' => true,
                    'title' => 'First Column']],
            'second_point' => [
                'first_column' => [
                    'value' => 'test value',
                    'type' => 'string',
                    'visible' => true,
                    'title' => 'First Column']]], $meta->getDataSet());
        
        
    }
    
    public function testClear() {
        $meta = new Meta();
        
        $meta->clear();        
        $this->assertEquals([], $meta->getDataSet());
        
        $meta
        ->column('first_column')
        ->setPoint('test')
        ->set('first_column', 'test value')
        ->setPoint('second_point')
        ->set('first_column', 'test value')
        ->clear();
        
        $this->assertEquals([
            'test' => [
                'first_column' => [
                    'value' => '',
                    'type' => 'string',
                    'visible' => true,
                    'title' => 'First Column']],
            'second_point' => [
                'first_column' => [
                    'value' => '',
                    'type' => 'string',
                    'visible' => true,
                    'title' => 'First Column']]], $meta->getDataSet());
        
        
       
    }
}
