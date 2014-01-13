<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase {

  function test_ut_taxonomy_name(){
      $result = ut_taxonomy_name();
      $this->assertTrue($result == '');
  }
  function test_get_custom_taxonomy_template(){
      $result = get_custom_taxonomy_template();
      $this->assertTrue($result == '');
  }
  function test_rceut_usertaxonomies(){
      $object = new RCEUtUserTaxonomies();
      $return_nothing = $object->ut_register_taxonomies();
      $ut_update_taxonomy_list = $object->ut_update_taxonomy_list();
      $parent_menu = $object->parent_menu();

      $this->assertTrue($return_nothing=='');
      //should return false as $_POST is empty
      $this->assertTrue($ut_update_taxonomy_list=='');
      //should return nothing as $pagenow is not set
      $this->assertTrue($parent_menu=='');
  }
  
}
