<?php

if( !defined('ABSPATH') )
{
      die('You cannot be here');
}

function get_plugin_options($name)
{
      $option=get_option( 'candidates_proposal_plugin_options', array() );
      return $option[$name];
}

  