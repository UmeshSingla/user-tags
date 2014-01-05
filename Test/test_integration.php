<?php
/***********************************
 *
 * IntegrationTest.php - Integration tests for main.php
 *
 * Read http://phpunit.de/manual/3.7/en/index.html for documentation
 * on how to write tests for phpunit.
 *
 * From: http://en.wikipedia.org/wiki/Unit_testing 
 * 
 * Integration testing (sometimes called integration and testing, abbreviated I&T) 
 * is the phase in software testing in which individual software modules are 
 * combined and tested as a group. It occurs after unit testing and before 
 * validation testing. Integration testing takes as its input modules that have been
 * unit tested, groups them in larger aggregates, applies tests defined in an 
 * integration test plan to those aggregates, and delivers as its output the 
 * integrated system ready for system testing.
 *
 *
 ***********************************/

#namespace sw_contacts\Test;

#require_once 'Phactory/lib/Phactory.php';

$TEST_ROOT = realpath(dirname(dirname(__FILE__)));
#require (TEST_ROOT."/main.php");


class IntegrationTest extends WP_UnitTestCase
{
	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'rcm_user_tags/user-taxonomies.php' ) );
	}

    public function testTrue()
    {
       $this->assertTrue(true); 
    }

    public function testFalse()
    {
       $this->assertFalse(false); 
    }
}
