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


$TEST_ROOT = realpath(dirname(dirname(__FILE__)));
require ($TEST_ROOT."/rce-ut-usertaxonomies.php");


class UnitTest extends WP_UnitTestCase
{
    function __construct(){
        $this->tx = new RCE_UT_UserTaxonomies();
    }
    /**
     * Ensure that the plugin has been installed and activated.
     */
    function test_plugin_activated() {
        $this->assertTrue( is_plugin_active( 'rce-user-tags/rce-ut-usertaxonomies.php' ) );
    }

    function test_registered_taxonomy() {
        $usr1 = $this->factory->user->create();
        $foo = $this->tx->user_profile($usr1);
        echo $foo;
    }
}
