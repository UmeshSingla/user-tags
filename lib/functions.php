<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function ut_taxonomy_name($name){
    if(empty($name)) return;
    $taxonomy_name = str_replace ( '-', '_', str_replace(' ', '_', strtolower($name) ) );
    $taxonomy_slug = 'rcm_user_' . $taxonomy_name;
    return $taxonomy_slug;
}