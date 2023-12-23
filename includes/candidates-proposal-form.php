<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}


add_shortcode('candidates_proposal_form', 'show_candidates_proposal_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

add_action('wp_footer', 'load_scripts');

function show_candidates_proposal_form() {
      if( is_user_logged_in() ) {
            ob_start();
            include MY_PLUGIN_PATH . '/includes/templates/candidates-proposal-form.php';
            $html_out = ob_get_contents();
            ob_end_clean();
            return $html_out;
      } else {
            return "";
      }
      
}

function enqueue_custom_scripts()
{
      
      // Enqueue custom css for plugin

      wp_enqueue_style('candidates-proposal-form-plugin', MY_PLUGIN_URL . 'assets/css/candidates-proposal-plugin.css');
      
      //wp_deregister_script('jquery');
	//wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);
}

function load_scripts()
{
      include MY_PLUGIN_PATH . '/includes/templates/candidates-proposal-script.php';
}

function setup_search()
{

      // Only apply filter to submissions page

      global $typenow;

      if ($typenow === 'submission') {

            add_filter('posts_search', 'submission_search_override', 10, 2);
      }
}

function submission_search_override($search, $query)
{
      // Override the submissions page search to include custom meta data

      global $wpdb;

      if ($query->is_main_query() && !empty($query->query['s'])) {
            $sql    = "
              or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','shortbio','website')
                  and meta_value like %s
              )
          ";
            $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
            $search = preg_replace(
                  "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                  $wpdb->prepare($sql, $like),
                  $search
            );
      }

      return $search;
}

function fill_submission_columns($column, $post_id)
{
      // Return meta data for individual posts on table

      switch ($column) {

            case 'name':
                  echo esc_html(get_post_meta($post_id, 'name', true));
                  break;

            case 'shortbio':
                  echo esc_html(get_post_meta($post_id, 'shortbio', true));
                  break;

            case 'website':
                  echo esc_html(get_post_meta($post_id, 'website', true));
                  break;
      }
}

function custom_submission_columns($columns)
{
      // Edit the columns for the submission table

      $columns = array(

            'cb' => $columns['cb'],
            'name' => __('Name', 'candidates-proposal-plugin'),
            'shortbio' => __('Short Bio', 'candidates-proposal-plugin'),
            'website' => __('Website', 'candidates-proposal-plugin'),
            'date' => 'Date',

      );

      return $columns;
}

function create_meta_box()
{
      // Create custom meta box to display submission

      add_meta_box('custom_candidates_proposal_form', 'Submission', 'display_submission', 'submission');
}

function display_submission()
{
      // Display individual submission data on it's page

      // $postmetas = get_post_meta( get_the_ID() );

      // echo '<ul>';

      // foreach($postmetas as $key => $value)
      // {

      //       echo '<li><strong>' . $key . ':</strong> ' . $value[0] . '</li>';

      // }

      // echo '</ul>';


      echo '<ul>';

      echo '<li><strong>Name:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'name', true)) . '</li>';
      echo '<li><strong>Shortbio:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'shortbio', true)) . '</li>';
      echo '<li><strong>Website:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'website', true)) . '</li>';
      
      echo '</ul>';
}

function create_submissions_page()
{

      // Create the submissions post type to store form submissions

      $args = [

            'public' => true,
            'has_archive' => true,
            'menu_position' => 30,
            'publicly_queryable' => false,
            'labels' => [

                  'name' => 'Submissions',
                  'singular_name' => 'Submission',
                  'edit_item' => 'View Submission'

            ],
            'supports' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                  'create_posts' => false,
            ),
            'map_meta_cap' => true
      ];

      register_post_type('submission', $args);
}



function create_rest_endpoint()
{

      // Create endpoint for front end to connect to WordPress securely to post form data
      register_rest_route('v1/candidates-proposal-form', 'submit', array(

            'methods' => 'POST',
            'callback' => 'handle_proposal'

      ));

      // Create endpoint for get votes
      register_rest_route('v1/candidates-proposal-form', '/votes', array(

            'methods' => 'GET',
            'callback' => 'handle_votes',
            'permission_callback' => '__return_true',
            'args'                => array(
                  'post' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                        }
                  )
            )
      ));

      // Create endpoint for voting
      register_rest_route('v1/candidates-proposal-form', '/vote', array(

            'methods' => 'GET',
            'callback' => 'handle_vote',
            'permission_callback' => '__return_true',
            'args'                => array(
                  'post' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                        }
                  )
            )
      ));
}

function handle_votes($data)
{
      global $wpdb;

      // Get all parameters from form
      $params = $data->get_params();

      // Get the voted post id
      $post_id = $params["post"];

      // Get the vote table name
      $table_name = $wpdb->base_prefix . "vote_table";

      // Count the votes for post_id
      $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");

      // Return a success message
      return new WP_Rest_Response(["count" => intval($rowcount)], 200);
}

function handle_vote($data)
{
      global $wpdb;

      // Set the efault result
      $result = 0;

      // Get the current user id
      $user_id = wp_get_current_user()->ID;

      // Get all parameters from form
      $params = $data->get_params();

      // Get the voted post id
      $post_id = $params["post"];

      // Get the vote table name
      $table_name = $wpdb->base_prefix . "vote_table";

      // By default, cast the vote with nol kimits
      $rowcount = 0;

      // Apply limits
      if (1 == 0) {

            // One vote per user per candidate
            $sql = "SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id AND user_id = $user_id";

            // Set the minimum time between two votes
            $time_difference = 0;

            // One user per candidate within a time difference
            $sql .= "AND datediff('" . current_time( 'mysql' ) . "', 'time') < $time_difference";

            // Finalize the sql string
            $sql .= ";";

            // Count the votes for post_id
            $rowcount = $wpdb->get_var($sql);

      }

      // Check if no vote has been casted by the same user in the time difference
      if (is_user_logged_in() && $rowcount == 0)
      {
            // Add a row to the table
            $result = $wpdb->query(
                  $wpdb->prepare(
                  "INSERT INTO $table_name (post_id, user_id, time) VALUES ( %d, %d, %s )",
                  $post_id,
                  $user_id,
                  current_time( 'mysql' )
                  )
                  
            );
      } 

      // Count the votes for post_id
      $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");

      return new WP_Rest_Response([
             "user" => $user_id,
             "post" => $post_id,
             "result" => $result,
             "count" => intval($rowcount)], 200);
}


function handle_proposal($data)
{
      $headers = $data->get_headers();
      $nonce = $headers['x-wp-nonce'][0];

      if (!wp_verify_nonce($nonce, "wp_rest")) 
      {
            return new WP_Rest_Response("Access denied", 403);
      }

      // Get the current user
      $user = wp_get_current_user();

      // Get all parameters from form
      $params = $data->get_params();

      // Set fields from the form
      $field_name = sanitize_text_field($params['name']);
      $field_role = sanitize_textarea_field($params['role']);
      $field_institution = sanitize_textarea_field($params['institution']);
      $field_shortbio = sanitize_email($params['shortbio']);
      $field_website = sanitize_text_field($params['website']);

      // Check if nonce is valid, if not, respond back with error
      if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {

            return new WP_Rest_Response("Form not processed", 422);
      }

      // Remove unneeded data from paramaters
      unset($params['_wpnonce']);
      unset($params['_wp_http_referer']);

      // Get the term id
      $role_term_id = get_category_by_slug($field_role)->term_id;

      // Get the term id
      $instutution_term_id = get_category_by_slug($field_institution)->term_id;
      
      // Prepare the post data
      $post_data = array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_title' => $params['name'],
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
      $post_content = get_cat_name( $role_term_id ) . '<br/>' .
            get_cat_name( $institution_term_id ) . '<br/>' .
            $params['shortbio'] . '<br/>' .
            '<a href="' . $params['website'] . '" target="_new">[website]</a><br/><br/>' .
            '<span id="votes"></span><br/>' .
            '<button onclick="candidates_proposal_plugin_vote(\'' . get_rest_url(null, 'v1/candidates-proposal-form/vote?post='). $new_post_id . '\')">Vote me!</button>' . 
            '<script>candidates_proposal_plugin_votes(\'' . get_rest_url(null, 'v1/candidates-proposal-form/votes?post='). $new_post_id . '\');</script>';
            

      // Prepare the post update
      $post_data["ID"] = $new_post_id;
      $post_data["post_content"] = $post_content;

      // Update the post
      $post_id = wp_insert_post($post_data);

      // Check if the post id doesn't changed
      if ($post_id != $new_post_id) {
            return new WP_Rest_Response("Post not updated", 422);
      }
            
      // Loop through each field posted and sanitize it
      foreach ($params as $label => $value) {

            switch ($label) {

                  case 'shortbio':

                        $value = sanitize_textarea_field($value);
                        break;

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
      $confirmation_message = get_plugin_options('candidates_proposal_plugin_message');
      if ($confirmation_message) {
            // Perform placeholders replacements
            $confirmation_message = str_replace('{name}', $user->display_name, $confirmation_message);
      }

      // Return a success message
      return new WP_Rest_Response($confirmation_message, 200);
}
