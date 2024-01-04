<?php

add_action( 'admin_menu', 'candidates_proposal_admin_menu_add_settings_page' );

add_action( 'admin_init', 'candidates_proposal_admin_init_register_settings' );

function candidates_proposal_admin_menu_add_settings_page() {
    add_options_page(
        'Candidates Proposal Plugin Settings',
        'Candidates Proposal',
        'manage_options',
        'candidates-proposal-plugin',
        'candidates_proposal_render_settings_page'
    );
}

function candidates_proposal_render_settings_page()
{
?>
<script>
    function escapeHtml(unsafe)
    {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&apos;");
    }
    
    jQuery(document).ready(function($)
    {
        $("#candidates_proposal_plugin_admin_votes_csv").click(function(event) {
            event.preventDefault();  //stop the browser from following
            window.location.href = '<?php echo get_rest_url(null, "v1/candidates-proposal/votes/csv"); ?>?_wpnonce=<?php echo wp_create_nonce("wp_rest"); ?>';
        });
    });
</script>
<h2>Candidates Proposal Plugin Settings</h2>
<form action="options.php" method="post">
    <?php 
    settings_fields( 'candidates_proposal_plugin_options' );
    do_settings_sections( 'candidates_proposal_plugin' ); ?>
    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
</form>
<hr/>
Download as <button id="candidates_proposal_plugin_admin_votes_csv">CSV</button><br/>
<?php
}

function candidates_proposal_admin_init_register_settings() {
    register_setting(
        'candidates_proposal_plugin_options',
        'candidates_proposal_plugin_options',
        'candidates_proposal_options_validate'
    );

    add_settings_section(
        'vote_settings',
        'Vote Settings',
        'candidates_proposal_plugin_section_vote_text',
        'candidates_proposal_plugin'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_secs',
        'Seconds between two votes casted by the same user to the same candidate',
        'candidates_proposal_plugin_setting_secs',
        'candidates_proposal_plugin',
        'vote_settings'
    );

    add_settings_section(
        'proposal_settings',
        'Proposal Settings',
        'candidates_proposal_plugin_section_proposal_text',
        'candidates_proposal_plugin'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_emailasusername',
        'Force email as username',
        'candidates_proposal_plugin_setting_emailasusername',
        'candidates_proposal_plugin',
        'proposal_settings'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_redirectafterloginorregister',
        'Redirect after login or register',
        'candidates_proposal_plugin_setting_redirectafterloginorregister',
        'candidates_proposal_plugin',
        'proposal_settings'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_roles',
        'Roles category',
        'candidates_proposal_plugin_setting_roles',
        'candidates_proposal_plugin',
        'proposal_settings'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_institutions',
        'Institutions category',
        'candidates_proposal_plugin_setting_institutions',
        'candidates_proposal_plugin',
        'proposal_settings'
    );

    add_settings_field(
        'candidates_proposal_plugin_setting_message',
        'Message',
        'candidates_proposal_plugin_setting_message',
        'candidates_proposal_plugin',
        'proposal_settings'
    );
}

function candidates_proposal_options_validate( $input ) {
    $newinput = $input;
    return $newinput;
}

function candidates_proposal_plugin_section_vote_text() {
    echo '<p>Here you can set all the options about the votes</p>';
}

function candidates_proposal_plugin_section_proposal_text() {
    echo '<p>Here you can set all the options about the proposal</p>';
}

function candidates_proposal_plugin_setting_secs() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('secs',$options)) $options['secs']=0;
    echo "<input id='candidates_proposal_plugin_setting_secs' name='candidates_proposal_plugin_options[secs]' type='text' value='" . esc_attr( $options['secs'] ) . "' />";
}

function candidates_proposal_plugin_setting_emailasusername() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('emailasusername',$options)) $options['emailasusername']='';
    echo "<input id='candidates_proposal_plugin_setting_emailasusername' name='candidates_proposal_plugin_options[emailasusername]' type='checkbox' value='1' " . checked( 1, $options['emailasusername'], false ) . " />";
}

function candidates_proposal_plugin_setting_redirectafterloginorregister() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('redirectafterloginorregister',$options)) $options['redirectafterloginorregister']='';
    echo "<input id='candidates_proposal_plugin_setting_redirectafterloginorregister' name='candidates_proposal_plugin_options[redirectafterloginorregister]' type='checkbox' value='1' " . checked( 1, $options['redirectafterloginorregister'], false ) . " />";
}


function candidates_proposal_plugin_setting_roles() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('roles',$options)) $options["roles"] = "";
    ?>
    <select id='candidates_proposal_plugin_setting_roles' name='candidates_proposal_plugin_options[roles]'>
    </select>
    <script>

jQuery(document).ready(function($)
{
    $.getJSON(
        '<?php echo get_rest_url(null, "wp/v2/categories"); ?>',{}
        ).done(
            function( data )
            {
                  data.forEach(function(item)
                  {
                    let selected = ""
                    if (item.id == "<?php echo $options["roles"] ?>") {
                        selected = "selected"
                    }
                        $("#candidates_proposal_plugin_setting_roles").append('<option value="' + item.id + '" ' + selected + '>' + escapeHtml(item.name) + '</option>' );     
                  });
            }
      );
});
      </script>
      <?php
}

function candidates_proposal_plugin_setting_institutions() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('institutions',$options)) $options["institutions"] = "";
    ?>
    <select id='candidates_proposal_plugin_setting_institutions' name='candidates_proposal_plugin_options[institutions]'>
    </select>
    <script>

jQuery(document).ready(function($)
{
    $.getJSON(
        '<?php echo get_rest_url(null, "wp/v2/categories"); ?>',{}
        ).done(
            function( data )
            {
                  data.forEach(function(item)
                  {
                    let selected = ""
                    if (item.id == "<?php echo $options["institutions"] ?>") {
                        selected = "selected"
                    }
                        $("#candidates_proposal_plugin_setting_institutions").append('<option value="' + item.id + '" ' + selected + '>' + escapeHtml(item.name) + '</option>' );     
                  });
            }
      );
});
      </script>
      <?php
}

function candidates_proposal_plugin_setting_message() {
    $options = get_option( 'candidates_proposal_plugin_options', array() );
    if (!array_key_exists('message',$options)) $options["message"] = "";
    wp_editor( esc_attr($options['message']), "candidates_proposal_plugin_options[message]", array() );
}





