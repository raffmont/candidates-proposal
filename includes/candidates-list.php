<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}

add_shortcode('candidates_list', 'candidates_list_shortcode_show');

add_action('rest_api_init', 'candidates_list_create_rest_endpoints');

add_action('wp_footer', 'candidates_list_load_scripts');



function candidates_list_shortcode_show($atts = [], $content = null, $tag = '')
{
    $options = get_option( 'candidates_proposal_plugin_options', array() );

    // normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

    // override default attributes with user attributes
    $candidates_list_atts = shortcode_atts(
        array(
            'roles' => '',
            'institutions' => '',
        ), $atts, $tag
    );

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

    $roles = "";
    if (array_key_exists("roles",$params))
    {
        $roles = $params["roles"];
    }

    $institutions = "";
    if (array_key_exists("institutions",$params))
    {
        $institutions = $params["institutions"];
    }




    global $wpdb;

    // Remove unneeded data from parameters
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

        $role_term = get_term( $role_term_id );
        $institution_term = get_term( $institution_term_id );

        $add_item = true;
        if (!empty($roles) && strpos($roles, $role_term->slug) === false) {
            $add_item = false;
        }
        if (!empty($institutions) && strpos($institutions, $institution_term->slug) === false) {
            $add_item = false;
        }

        if ($add_item) {
            // Count the votes for post->ID
            $votes = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE post_id = $post_id;");

            $featured_image_id = get_post_thumbnail_id($post_id);
            $featured_image_src = wp_get_attachment_image_src($featured_image_id);

            $link = get_permalink($post_id);

            $mode = get_plugin_options('mode');
            if ($mode === "2") {
                $votes=-1;
            }

            $candidate = array(
                'id' => $post_id,
                'link' => $link,
                'image' => $featured_image_src[0],
                'name' => get_the_title(),
                'role' => get_cat_name($role_term_id),
                'institution' => get_cat_name($institution_term_id),
                'votes' => intval($votes)
            );

            array_push($candidates, $candidate);
        }

    endwhile;

    $jsondata = array( 'candidates' => $candidates);

    wp_reset_postdata();

    return rest_ensure_response( $jsondata );

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

    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/candidates-list-header.html';
    $list_header = ob_get_contents();
    ob_end_clean();

    $mode = get_plugin_options('mode');
    if ($mode === "1") {
        ob_start();
        include MY_PLUGIN_PATH . '/includes/templates/candidates-list-item.html';
        $list_item = ob_get_contents();
        ob_end_clean();
    } elseif ($mode === "2") {
        ob_start();
        include MY_PLUGIN_PATH . '/includes/templates/candidates-list-item-2.html';
        $list_item = ob_get_contents();
        ob_end_clean();
    }

    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/candidates-list-footer.html';
    $list_footer = ob_get_contents();
    ob_end_clean();

    $list_header = str_replace("\n", "", $list_header);
    $list_item = str_replace("\n", "", $list_item);
    $list_footer = str_replace("\n", "", $list_footer);

    $roles="";
    $institutions="";

    if ( get_query_var('roles') ) {
        $roles = get_query_var('roles');
    }

    if ( get_query_var('institutions') ) {
        $institutions = get_query_var('institutions');
    }

    include MY_PLUGIN_PATH . '/includes/candidates-list-script.php';
}

