<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}

add_shortcode('candidates_list', 'candidates_list_shortcode_show');

add_action('rest_api_init', 'candidates_list_create_rest_endpoints');

add_action('wp_footer', 'candidates_list_load_scripts');



function candidates_list_shortcode_show()
{
    $options = get_option( 'candidates_proposal_plugin_options', array() );

    $html_out = "";


    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/candidates-list.html';
    $html_out = ob_get_contents();
    ob_end_clean();


    return $html_out;

}

function candidates_list_create_rest_endpoints()
{

    // Create endpoint for front end to connect to WordPress securely to post form data
    register_rest_route('v1/candidates', '/list', array(

        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'candidates_list'

    ));
}

function candidates_list($data)
{
    // Get the headers
    $headers = $data->get_headers();

    do_action( 'inspect', [ 'headers', $headers, __FILE__, __LINE__ ] );

    // Get the params
    $params = $data->get_params();

    do_action( 'inspect', [ 'params', $params, __FILE__, __LINE__ ] );

    // Get the params
    $params = $data->get_params();


    global $wpdb;

    // Remove unneeded data from paramaters
    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);


    // Get the vote table name
    $table_name = $wpdb->base_prefix . "vote_table";



    $args = array(
        'post_type' => 'candidate',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $loop = new WP_Query( $args );

    $candidates = array();

    while ( $loop->have_posts() ) :

        $loop->the_post();
        $post_id = get_the_ID();

        $role_term_id = get_post_meta($post_id, 'role')[0];
        $institution_term_id = get_post_meta($post_id, 'institution')[0];


        // Count the votes for post->ID
        $votes = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");

        $featured_image_id = get_post_thumbnail_id( $post_id );
        $featured_image_src = wp_get_attachment_image_src( $featured_image_id);

        $link = get_permalink($post_id);

        $candidate = array(
            'id' => $post_id,
            'link' => $link,
            'image' => $featured_image_src[0],
            'name' => get_the_title(),
            'role' => get_cat_name( $role_term_id ),
            'institution' => get_cat_name( $institution_term_id ),
            'votes' => intval($votes)
        );

        array_push($candidates, $candidate);

    endwhile;

    $jsondata = array( 'candidates' => $candidates);

    wp_reset_postdata();

    return rest_ensure_response( $jsondata );
/*
    $response = new WP_REST_Response;


    // Image exists, prepare a binary-data response.
    $response->set_data( json_encode($jsondata) ); ;
    $response->set_headers( [ 'Content-Type'   => 'application/json; charset=utf-8' ] );


    return $response;*/
}

function candidates_list_load_scripts()
{
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('redirectafterloginorregister',$options)) $options['redirectafterloginorregister']='';

    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/candidate-post-type-register-first.html';
    $register_first = ob_get_contents();
    ob_end_clean();

    $permalink = '';
    if ($options['redirectafterloginorregister'] != '')
    {
        $permalink = get_permalink();
    }

    $register_first = str_replace("{login_url}",esc_url(wp_login_url($permalink)) ,$register_first);
    $register_first = str_replace("{registration_url}", esc_url(wp_registration_url()), $register_first);
    $register_first = str_replace("\n","",$register_first);

    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/candidate-post-type-time-between-votes.html';
    $time_between_votes = ob_get_contents();
    ob_end_clean();

    $time_between_votes = str_replace("\n","",$time_between_votes);

    include MY_PLUGIN_PATH . '/includes/candidates-list-script.php';
}

