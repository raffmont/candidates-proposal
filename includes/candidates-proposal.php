<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}


add_action('wp_enqueue_scripts', 'candidates_proposal_scripts_enqueue');

function candidates_proposal_scripts_enqueue()
{
      
      // Enqueue custom css for plugin

      wp_enqueue_style('candidates-proposal-form-plugin', MY_PLUGIN_URL . 'assets/css/candidates-proposal-plugin.css');
      
      wp_deregister_script('jquery');
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, false);
}

