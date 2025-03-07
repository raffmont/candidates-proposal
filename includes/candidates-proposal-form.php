<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}

add_shortcode('candidates_proposal_form', 'candidates_proposal_form_shortcode_show');

add_action('rest_api_init', 'candidates_proposal_form_create_rest_endpoints');

add_action('wp_footer', 'candidates_proposal_form_load_scripts');

function candidates_proposal_form_load_scripts()
{
      include MY_PLUGIN_PATH . '/includes/candidates-proposal-form-script.php';
}

function upload_max_size()
{
      $val = ini_get('upload_max_filesize');
      if (empty($val))
      {
            $val = 0;
      }
      
      $val = trim($val);
      $last = strtolower($val[strlen($val)-1]);
      $val = floatval($val);

      switch($last) {

            case 'g':
                  $val *= 1073741824;
            break;

            case 'm':
                  $val *= 1048576;
            break;
            
            case 'k':
                  $val *= 1024;
            break;
      }

      return $val;
}

function candidates_proposal_form_shortcode_show()
{
      $options = get_option( 'candidates_proposal_plugin_options', array() );
      if (!array_key_exists('redirectafterloginorregister',$options)) $options['redirectafterloginorregister']='';
            
      $html_out = "";

      
      if( is_user_logged_in() ) {

            ob_start();
            include MY_PLUGIN_PATH . '/includes/templates/candidates-proposal-form.html';
            $html_out = ob_get_contents();
            ob_end_clean();

            $html_out = str_replace("{upload_max_size}",upload_max_size()/1024000,$html_out);
      } else {
            ob_start();
            include MY_PLUGIN_PATH . '/includes/templates/candidates-proposal-form-register-first.html';
            $html_out = ob_get_contents();
            ob_end_clean();

            $permalink = '';
            if ($options['redirectafterloginorregister'] != '')
            {
                  $permalink = get_permalink();
            }

            $html_out = str_replace("{login_url}",esc_url(wp_login_url($permalink)) ,$html_out);
            $html_out = str_replace("{registration_url}", esc_url(wp_registration_url()), $html_out);
            
      }
      
      return $html_out;
      
}


function candidates_proposal_form_create_rest_endpoints()
{

      // Create endpoint for front end to connect to WordPress securely to post form data
      register_rest_route('v1/candidates-proposal-form', 'submit', array(

            'methods' => 'POST',
            'callback' => 'candidates_proposal_form_submit'

      ));
}

function candidates_proposal_form_submit($data)
{
      // Get the headers
      $headers = $data->get_headers();

      // Get the nonce
      $nonce = $headers['x_wp_nonce'][0];

      // Check if the nonce is correct
      if (!wp_verify_nonce($nonce, "wp_rest")) 
      {
            // Return an error message
            return new WP_Rest_Response("Access denied", 403);
      }

      // Get all parameters from form
      $params = $data->get_params();


      // Get the current user
      $user = wp_get_current_user();

      
      // Set fields from the form
      $field_name = sanitize_text_field($params['name']);
      $field_role = sanitize_textarea_field($params['role']);
      $field_institution = sanitize_textarea_field($params['institution']);
      $field_shortbio = sanitize_email($params['shortbio']);
      $field_website = sanitize_text_field($params['website']);

      $field_social_facebook = sanitize_text_field($params['social_facebook']);
      $field_social_instagram = sanitize_text_field($params['social_instagram']);
      $field_social_x = sanitize_text_field($params['social_x']);
      $field_social_linkedin = sanitize_text_field($params['social_linkedin']);
      $field_social_tiktok = sanitize_text_field($params['social_tiktok']);

      // Remove unneeded data from paramaters
      unset($params['_wpnonce']);
      unset($params['_wp_http_referer']);

      // Get the term id
      $role_term_id = get_category_by_slug($field_role)->term_id;

      // Get the term id
      $institution_term_id = get_category_by_slug($field_institution)->term_id;
      
      // Prepare the post data
      $post_data = array(
            //'post_type' => 'post',
            'post_type' => 'candidate',
            'post_status' => 'draft',
            'post_title' => $params["name"],
            'post_content' => '',
            'post_category' => array($role_term_id, $institution_term_id),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
      );

      // Add the post
      $new_post_id = wp_insert_post($post_data);

      // Check if the post has been added
      if ($new_post_id == 0) {
            return new WP_Rest_Response("Post not added", 422);
      }

      // Set the content
      $post_content = $params["shortbio"];

      // Prepare the post update
      $post_data["ID"] = $new_post_id;
      $post_data["post_content"] = $post_content;

      // Update the post
      $post_id = wp_insert_post($post_data);

      // Check if the post id doesn't changed
      if ($post_id != $new_post_id) {
            return new WP_Rest_Response("Post not updated", 422);
      }

      // Remove shortbio from paramaters
      unset($params['name']);
      unset($params['shortbio']);

      // Loop through each field posted and sanitize it
      foreach ($params as $label => $value) {

            switch ($label) {

                  case 'role':
                        $value = $role_term_id;
                        break;

                  case 'institution':
                        $value = $institution_term_id;
                        break;

                case 'social_facebook':
                case 'social_instagram':
                case 'social_x':
                case 'social_linkedin':
                case 'social_tiktok':
                case 'website':
                        $value = sanitize_url($value);
                        break;

                default:
                        $value = sanitize_text_field($value);
            }

            add_post_meta($post_id, sanitize_text_field($label), $value);
      }

      // Handle the form data that is posted
      require_once(ABSPATH . "wp-admin" . '/includes/file.php');
      
      // Get the picture object
      $picture = $data->get_file_params()['picture'];
      
      // Get the filename and sanitize it
      $filename = sanitize_file_name($picture['name']);

      // Get the temporary file name
      $file_tmp = $picture['tmp_name'];

      // Set the supported image formats
      $supported_types = array('image/jpeg','image/png');

      // Check image file type
      $wp_filetype = wp_check_filetype( $filename, null );

      // Check if the image is one of the supported ones
      if (!in_array($wp_filetype['type'], $supported_types))
      {
            return new WP_Rest_Response("Uploaded file type not supported", 422);
      }

      // Move the uploaded file in the uploads directory
      $uploaded_file = wp_upload_bits($filename, null, file_get_contents($file_tmp));

      // Set attachment data
      $attachment_data = array (
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_excerpt' => '',
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content' => '',
            'post_mime_type' => $wp_filetype['type'],
            'guid' => $uploaded_file['url']
      );

      // Inserts an attachment.
      $attachment_id = wp_insert_attachment( $attachment_data, $uploaded_file["file"], $post_id );

      // CHeck if the attachment has been created
      if ($attachment_id == 0) {
            return new WP_Rest_Response("Attachment not created", 422);
      }

      // Include image.php
      require_once( ABSPATH . 'wp-admin/includes/image.php' );

      // Generates attachment meta data and create image sub-sizes for images.
      $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded_file["file"] );

      // Get the attachment post
      $post = get_post( $attachment_id );

      // Update the attachment metadata
      $updated_attachment_metadata = apply_filters( 'wp_update_attachment_metadata', $attachment_metadata, $post->ID );
      
      // Check if the metadata has been correctly updated
	if ( $updated_attachment_metadata ) {

            // Update post metadata
		update_post_meta( $post->ID, '_wp_attachment_metadata', $updated_attachment_metadata );
	}
      else
      {
            // Delete post metadata
		delete_post_meta( $post->ID, '_wp_attachment_metadata' );
	}

      // Sets the post thumbnail (featured image) for the given post.
      set_post_thumbnail( $post_id, $attachment_id );

      // Get the message from the plugin setup
      $confirmation_message = get_plugin_options('message');
      if ($confirmation_message) {
            // Perform placeholders replacements
            $confirmation_message = str_replace('{name}', $user->display_name, $confirmation_message);
      }

      // Return a success message
      return new WP_Rest_Response($confirmation_message, 200);
}

