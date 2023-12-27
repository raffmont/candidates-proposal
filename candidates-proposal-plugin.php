<?php

/**
*
* Plugin Name: Candidates Proposal Plugin
* Description: This plugin enable a registered user to propose a candidates
* Author: Federica Izzo, and Raffaele Montella
* Version: 1.0.0
* Text Domain: options-plugin
*
*/

if (!defined('ABSPATH')) {
  die('You cannot be there!');
}


if (!class_exists('CandidatesProposalPlugin')) {
  
	class CandidatesProposalPlugin
	{
    
    public function __contruct()
    {
      
    }

    function initialize() {
      define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
      define('MY_PLUGIN_URL', plugin_dir_url( __FILE__ ));

      //require_once( plugin_dir_path(__FILE__) . 'vendor/autoload.php' );
    
      include_once MY_PLUGIN_PATH . "includes/utilities.php";
      include_once MY_PLUGIN_PATH . "includes/options-page.php";
      include_once MY_PLUGIN_PATH . "includes/candidates-proposal.php";
      include_once MY_PLUGIN_PATH . "includes/candidate-post-type.php";
      include_once MY_PLUGIN_PATH . "includes/candidates-proposal-form.php";

      global $wpdb;

      // set the default character set and collation for the table
      $charset_collate = $wpdb->get_charset_collate();

      // Check that the table does not already exist before continuing
      $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}vote_table` (
            id bigint(50) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY  (post_id) REFERENCES wp_posts(ID)
      ) $charset_collate;";

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta( $sql );
      $is_error = empty( $wpdb->last_error );
      
    }
  }

  $candidatesProposalPlugin = new CandidatesProposalPlugin;
  $candidatesProposalPlugin->initialize();
}
