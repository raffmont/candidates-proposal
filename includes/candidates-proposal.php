<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}


add_action('wp_enqueue_scripts', 'candidates_proposal_scripts_enqueue');

add_action('rest_api_init', 'candidates_proposal_rest_api_init');

function candidates_proposal_scripts_enqueue()
{
      
      // Enqueue custom css for plugin

      wp_enqueue_style('candidates-proposal-form-plugin', MY_PLUGIN_URL . 'assets/css/candidates-proposal-plugin.css');
      
      wp_deregister_script('jquery');
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, false);
}

function candidates_proposal_rest_api_init() {

    register_rest_route( 'v1/candidates-proposal/votes', 'csv', [
        'method' => 'GET',
        'callback' => 'candidates_proposal_votes_csv',
        'permission_callback' => '__return_true',
        //'permission_callback'   => function () { return current_user_can('administrator'); },
        'args' => array(
            '_wp_nonce' => array(
                'validate_callback' => function( $param, $request, $key ) { return is_string( $param ); }
            )
        )
    ] );

}

function candidates_proposal_serve_text($served, $result ) {
    $is_text   = false;
    $text_data = null;

    // Check the "Content-Type" header to confirm that we really want to return
    // binary image data.
    foreach ( $result->get_headers() as $header => $value ) {
        if ( 'content-type' === strtolower( $header ) ) {
            $is_text   = 0 === strpos( $value, 'text/' );
            $text_data = $result->get_data();
            break;
        }
    }

    if ( $is_text && is_string( $text_data ) ) {
    
        echo $text_data;

        return true;
    }

    return $served;
}

function candidates_proposal_votes_csv($data)
{
    // Get the headers
    $headers = $data->get_headers();

    do_action( 'inspect', [ 'headers', $headers, __FILE__, __LINE__ ] );

    // Get the params
    $params = $data->get_params();

    do_action( 'inspect', [ 'params', $params, __FILE__, __LINE__ ] );

    // Get the params
    $params = $data->get_params();

    // Get the nonce
    $nonce = $params['_wpnonce'];    

    $user_id = get_current_user_id();

    // Check if the nonce is correct
    if (!wp_verify_nonce($nonce, "wp_rest")) 
    {
          // Return an error message
          return new WP_Rest_Response("Access denied", 403);
    }

    global $wpdb;

    // Get the vote table name
    $table_name = $wpdb->base_prefix . "vote_table";

    $csvdata="NAME,ROLE,INSTITUTION,VOTES\n";

    $args = array(  
        'post_type' => 'candidate',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        'orderby' => 'title', 
        'order' => 'ASC'
    );

    $loop = new WP_Query( $args ); 
        
    while ( $loop->have_posts() ) :
        
        $loop->the_post();
        $post_id = get_the_ID();
        
        $role_term_id = get_post_meta($post_id, 'role')[0];
        $institution_term_id = get_post_meta($post_id, 'institution')[0];
        

        // Count the votes for post->ID
        $votes = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");
         
        $csvdata .= get_the_title() . "," . 
            get_cat_name( $role_term_id ) . "," .
            get_cat_name( $institution_term_id ) .
            ",$votes\n";

    endwhile;

    wp_reset_postdata(); 

    $response = new WP_REST_Response;

    if ( $csvdata != "" ) {
        // Image exists, prepare a binary-data response.
        $response->set_data( $csvdata ) ;
        $response->set_headers( [
            'Content-Type'   => 'text/csv',
            'Content-Disposition' => 'attachment; filename="votes.csv"',
            'Content-Length' => strlen($csvdata)
        ] );

        // HERE → This filter will return our binary image!
        add_filter( 'rest_pre_serve_request', 'candidates_proposal_serve_text', 0, 2 );
    } else {
        // Return a simple "not-found" JSON response.
        $response->set_data( 'not-found' );
        $response->set_status( 404 );
    }

    return $response;
}