<script>

function candidates_proposal_plugin_post_get_votes(post_id)
{
    $.getJSON(
        '<?php echo get_rest_url(null, "v1/candidates-proposal-post/votes"); ?>',
        {
                _wp_nonce: '<?php echo wp_create_nonce("wp_rest"); ?>',
                post: post_id
        }
        ).done(
            function( data )
            {
                console.log(data);
                $("#candidates_proposal_plugin_post_votes").html(data["count"]);      
            }
    );
}

function candidates_proposal_plugin_post_post_vote(post_id)
{
    $.ajax(
    {
        method: 'POST',
        url: '<?php echo get_rest_url(null, "v1/candidates-proposal-post/vote"); ?>',
        headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
        data: JSON.stringify({ post: post_id }),
        contentType: "application/json; charset=utf-8",
        traditional: true,
        success:function(data)
        {
            console.log(data);
            if (data["result"] == 1 ) 
            {
                $("#candidates_proposal_plugin_post_votes").html(data["count"]);  
            } else if (data["user"] == 0 )
            {
                $("#candidates_proposal_plugin_post_error").
                html('<?php echo $register_first; ?>').fadeIn();
            } else
            {
                $("#candidates_proposal_plugin_post_error").html('<?php echo $time_between_votes; ?>').fadeIn();
            }
        },
        error: function(jqXHR, exception){
            $("#candidates_proposal_plugin_post_error").html(jqXHR.responseText).fadeIn();
        }
    });
    
}

candidates_proposal_plugin_post_get_votes( <?php echo get_the_ID(); ?>);

   
    
</script>