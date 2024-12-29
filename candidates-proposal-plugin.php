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
      include_once MY_PLUGIN_PATH . "includes/candidates-list.php";

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
            FOREIGN KEY  (post_id) REFERENCES {$wpdb->base_prefix}posts(ID)
      ) $charset_collate;";

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta( $sql );
      $is_error = empty( $wpdb->last_error );

      $options = get_option( 'candidates_proposal_plugin_options', array() );

      if (!array_key_exists('emailasusername',$options)) $options['emailasusername']='';

      if ($options['emailasusername'] != '')
      {
        // Remove Username textfield
        add_action('login_head', function()
        {
          ?>
              <style>
                  #registerform > p:first-child{
                      display:none;
                  }
              </style>
          
              <script type="text/javascript" src="<?php echo site_url('/wp-includes/js/jquery/jquery.js'); ?>"></script>
              <script type="text/javascript">
                  jQuery(document).ready(function($)
                  {
                      $('#registerform > p:first-child').css('display', 'none');
                  });
              </script>
          <?php
        });

        //Remove error for username, only show error for email only.
        add_filter('registration_errors', function($wp_error, $sanitized_user_login, $user_email){
          if(isset($wp_error->errors['empty_username']))
          {
              unset($wp_error->errors['empty_username']);
          }

          if(isset($wp_error->errors['username_exists']))
          {
              unset($wp_error->errors['username_exists']);
          }
          return $wp_error;
        }, 10, 3);

        // Manipulate Background Registration Functionality.
        add_action('login_form_register', function()
        {
          if(isset($_POST['user_login']) && isset($_POST['user_email']) && !empty($_POST['user_email']))
          {
              $_POST['user_login'] = $_POST['user_email'];
          }
        });
        
      }
    }
  }

  $candidatesProposalPlugin = new CandidatesProposalPlugin;
  $candidatesProposalPlugin->initialize();
}
