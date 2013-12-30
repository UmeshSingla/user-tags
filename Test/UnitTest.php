<?php
/***********************************
 *
 * unitTest.php - Unit tests for current plugin
 *
 * Read http://phpunit.de/manual/3.7/en/index.html for documentation
 * on how to write tests for phpunit.
 *
 * From: http://en.wikipedia.org/wiki/Unit_testing 
 * 
 * one can view a unit as the smallest testable part of an application.
 * In procedural programming, a unit could be an entire module, but is more commonly
 * an individual function or procedure. 
 * In object-oriented programming, a unit is often an entire interface, such as a 
 * class, but could be an individual method.
 *
 *
 ***********************************/

#namespace sw_contacts\Test;

#require_once 'Phactory/lib/Phactory.php';

define('TEST_ROOT',realpath(dirname(dirname(__FILE__))));
#require (TEST_ROOT."/main.php");


class UnitTest extends \PHPUnit_Framework_TestCase
{
    public function testTrue()
    {
       $this->assertTrue(true); 
    }
    public function testFalse()
    {
       $this->assertFalse(false); 
    }
}
