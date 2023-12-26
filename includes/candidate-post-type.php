<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}

add_action('init', 'candidate_post_type_create');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_candidate_posts_columns', 'candidate_post_type_posts_columns');

add_action('manage_candidate_posts_custom_column', 'candidate_post_type_posts_custom_colum', 10, 2);

add_filter('manage_edit-candidate_sortable_columns', 'candidate_post_type_sortable_columns' );

function candidate_post_type_sortable_columns( $columns ) {

	$columns['role'] = 'role';
      $columns['institution'] = 'institution';
      $columns['votes'] = 'votes';
      $columns['website'] = 'website';
	return $columns;
}

add_action('admin_init', 'setup_search');

add_action('rest_api_init', 'candidate_post_type_create_rest_endpoints');

add_filter( 'the_content', 'filter_post_content', 10 );

function filter_post_content($content) 
{
      // Get the post id
      $post_id = get_the_ID();
 
      if ( is_singular( 'candidate' ) )
      {
            
            $role_term_id = get_post_meta($post_id, 'role')[0];
            $institution_term_id = get_post_meta($post_id, 'institution')[0];
            $shortbio = $content;
            $website = get_post_meta($post_id, 'website')[0];

            ob_start();
            include MY_PLUGIN_PATH . '/includes/templates/candidate-post-type-register-first.html';
            $register_first = ob_get_contents();
            ob_end_clean();

            $register_first = str_replace("\n","",$register_first);
            
            ob_start();
            include MY_PLUGIN_PATH . '/includes/candidate-post-type-script.php';
            $script = ob_get_contents();
            ob_end_clean();

            ob_start();
            include MY_PLUGIN_PATH . '/includes/templates/candidate-post-type.html';
            $content = ob_get_contents();
            ob_end_clean();

            $content = str_replace("{id}",$post_id, $content);
            $content = str_replace("{role}",get_cat_name( $role_term_id ),$content);
            $content = str_replace("{institution}",get_cat_name( $institution_term_id ),$content);
            $content = str_replace("{shortbio}",$shortbio, $content);
            $content = str_replace("{website}",$website, $content);

            $content = $script . "\n" . $content;
      }
      return $content;
}

function setup_search()
{

      // Only apply filter to candidates page

      global $typenow;

      if ($typenow === 'candidate') {

            add_filter('posts_search', 'candidate_search_override', 10, 2);
      }
}

function candidate_search_override($search, $query)
{
      // Override the candidates page search to include custom meta data

      global $wpdb;

      if ($query->is_main_query() && !empty($query->query['s']))
      {
            $sql    = "
                  or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','website')
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

function candidate_post_type_posts_custom_colum($column, $post_id)
{
      global $wpdb;

      // Return meta data for individual posts on table

      switch ($column) {

            case 'role':
                  echo esc_html(get_cat_name(get_post_meta($post_id, 'role', true)));
                  break;

            case 'institution':
                  echo esc_html(get_cat_name(get_post_meta($post_id, 'institution', true)));
                  break;

            case 'votes':
      
                  // Get the vote table name
                  $table_name = $wpdb->base_prefix . "vote_table";

                  // Count the votes for post_id
                  $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");


                  echo esc_html($rowcount);
                  break;

            case 'website':
                  echo esc_html(get_post_meta($post_id, 'website', true));
                  break;

            // Just break out of the switch statement for everything else. 
		default :
                  break;
      }
}

function candidate_post_type_posts_columns($columns)
{
      // Edit the columns for the candidate table

      $columns = array(

            'cb' => $columns['cb'],
            'title' => __('Candidate', 'candidates-proposal-plugin'),
            'role' => __('Role', 'candidates-proposal-plugin'),
            'institution' => __('Institution', 'candidates-proposal-plugin'),
            'votes' => __('Votes', 'candidates-proposal-plugin'),
            'website' => __('Website', 'candidates-proposal-plugin'),
            'date' => 'Date',

      );

      return $columns;
}

function create_meta_box()
{
      // Create custom meta box to display candidate

      add_meta_box('custom_candidates_proposal_form', 'Candidate', 'display_candidate', 'candidate');
}

function display_candidate()
{
      // Display individual candidate data on it's page

      // $postmetas = get_post_meta( get_the_ID() );

      // echo '<ul>';

      // foreach($postmetas as $key => $value)
      // {

      //       echo '<li><strong>' . $key . ':</strong> ' . $value[0] . '</li>';

      // }

      // echo '</ul>';

      global $wpdb;

      // Get the post id
      $post_id = get_the_ID();

      // Get the vote table name
      $table_name = $wpdb->base_prefix . "vote_table";

      // Count the votes for post_id
      $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");


      echo '<ul>';

      echo '<li><strong>Role:</strong><br />' . esc_html(get_cat_name(get_post_meta($post_id, 'role', true))) . '</li>';
      echo '<li><strong>Institution:</strong><br />' . esc_html(get_cat_name(get_post_meta($post_id, 'institution', true))) . '</li>';
      echo '<li><strong>Votes:</strong><br /> ' . esc_html($rowcount) . '</li>';
      echo '<li><strong>Website:</strong><br /> ' . esc_html(get_post_meta($post_id, 'website', true)) . '</li>';

      echo '</ul>';
}

function candidate_post_type_create()
{
      // Set up labels
	$labels = array(
            'name' => 'Candidates',
            'singular_name' => 'Candidate',
            'add_new' => 'Add New Candidate',
            'add_new_item' => 'Add New Candidate',
            'edit_item' => 'Edit Candidate',
            'new_item' => 'New Candidate',
            'all_items' => 'All Candidate',
            'view_item' => 'View Candidate',
            'search_items' => 'Search Candidates',
            'not_found' =>  'No Candidates Found',
            'not_found_in_trash' => 'No Candidates found in Trash', 
            'parent_item_colon' => '',
            'menu_name' => 'Candidates'
      );

      // Set up arguments
      $args = [
            'public' => true,
            'has_archive' => true,
            'menu_position' => 30,
            'publicly_queryable' => true,
            'labels' => $labels,
            'supports' => array( 'title', 'editor', 'excerpt', 'custom-fields', 'thumbnail','page-attributes' ),
		'taxonomies' => array( 'post_tag', 'category' ),
            'exclude_from_search' => false,
            'capability_type' => 'post',
            'capabilities' => array('post'),
            'map_meta_cap' => true,
            'rewrite' => array( 'slug' => 'candidates' )
      ];

      // Create the custom post type
      register_post_type('candidate', $args);
}

function candidate_post_type_create_rest_endpoints()
{

      // Create endpoint for voting
      register_rest_route('v1/candidates-proposal-post', 'vote', array(

            'methods' => 'POST',
            'callback' => 'handle_vote'
      ));

      // Create endpoint for get votes
      register_rest_route('v1/candidates-proposal-post', 'votes', array(

            'methods' => 'GET',
            'callback' => 'handle_votes',
            'permission_callback' => '__return_true',
            'args'                => array(
                  'post' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                        }
                  ),
                  '_wp_nonce' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                        return is_string( $param );
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

      // Set the minimum time in days between two votes
      $days = get_plugin_options('candidates_proposal_plugin_days');

      // By default, cast the vote with nol kimits
      $rowcount = 0;

      // Apply limits
      if ($days>0) {

            // One vote per user per candidate
            $sql = "SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id AND user_id = $user_id";
 
            // One user per candidate within a time difference
            //$sql .= " AND datediff('" . current_time( 'mysql' ) . "', 'time') < $days";
            $sql .= " AND DATE($table_name.time) = DATE_SUB(CURDATE(), INTERVAL $days DAY)";

            // Finalize the sql string
            $sql .= ";";

            do_action( 'inspect', [ 'sql', $sql, __FILE__, __LINE__ ] );

            // Count the votes for post_id
            $rowcount = $wpdb->get_var($sql);

            do_action( 'inspect', [ 'rowcount', $rowcount, __FILE__, __LINE__ ] );

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



