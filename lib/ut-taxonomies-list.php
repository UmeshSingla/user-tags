<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// Put the taxonomies in alphabetical order
$taxonomies	= self::$taxonomies;
ksort($taxonomies);

foreach($taxonomies as $key=>$taxonomy) {
        add_users_page(
                $taxonomy->labels->menu_name, 
                $taxonomy->labels->menu_name, 
                $taxonomy->cap->manage_terms, 
                "edit-tags.php?taxonomy={$key}"
        );
}
