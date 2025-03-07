<script>
    jQuery(document).ready(function($)
    {
        // Fill roles /wp-json/wp/v2/categories?parent=8
        $.getJSON(
            '<?php echo get_rest_url(null, "v1/candidates/list"); ?>',
            {
                "roles": "<?php echo $roles; ?>",
                "institutions": "<?php echo $institutions; ?>",
            },
        ).done(
            function( data ) {
                let candidates = data["candidates"]

                candidates.sort((a, b) => b.votes - a.votes)

                let htmlBody =  '<?php echo $list_header; ?>'
                candidates.forEach(function(item, index) {
                    let htmlItem = '<?php echo $list_item; ?>';
                    htmlItem = htmlItem.replaceAll("{image}", item.image);
                    htmlItem = htmlItem.replaceAll("{name}", candidates_proposal_escapeHtml(item.name));
                    htmlItem = htmlItem.replaceAll("{role}", candidates_proposal_escapeHtml(item.role));
                    htmlItem = htmlItem.replaceAll("{institution}", candidates_proposal_escapeHtml(item.institution));
                    htmlItem = htmlItem.replaceAll("{votes}", '<span id="candidates_proposal_list_votes_'+item.id+'">' + item.votes + '</span>');
                    htmlItem = htmlItem.replaceAll("{vote}", '<button onclick="candidates_proposal_list_vote('+item.id+')">Vote!</button>');
                    htmlItem = htmlItem.replaceAll("{link}", item.link);

                    htmlBody += htmlItem + "\n";
                });
                htmlBody +=  '<?php echo $list_footer; ?>'
                console.log(htmlBody)
                $("#candidates_proposal_list_body").html(htmlBody);
            }
        );
    });

    function candidates_proposal_escapeHtml(unsafe) {
        return unsafe
            .replaceAll(/&/g, "&amp;")
            .replaceAll(/</g, "&lt;")
            .replaceAll(/>/g, "&gt;")
            .replaceAll(/"/g, "&quot;")
            .replaceAll(/'/g, "&apos;");
    }

    function candidates_proposal_list_vote(id) {
        console.log("candidates_proposal_list_vote: " + id)
        $.ajax(
            {
                method: 'POST',
                url: '<?php echo get_rest_url(null, "v1/candidates-proposal-post/vote"); ?>',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
                data: JSON.stringify({ post: id }),
                contentType: "application/json; charset=utf-8",
                traditional: true,
                success:function(data)
                {
                    console.log(data);
                    if (data["result"] === 1 )
                    {
                        $("#candidates_proposal_list_votes_"+id).html(data["count"]);
                        console.log("Vote valid: " + data["count"])
                    } else if (data["user"] === 0 )
                    {
                        $("#candidates_proposal_list_error").html('<?php echo $register_first; ?>').fadeIn();
                        window.scrollTo(0,0)
                        console.log("Vote not valid: register first!")
                    } else
                    {
                        $("#candidates_proposal_list_error").html('<?php echo $time_between_votes; ?>').fadeIn();
                        window.scrollTo(0,0)
                        console.log("Vote not valid: too much votes!")
                    }
                },
                error: function(jqXHR, exception){
                    $("#candidates_proposal_list_error").html(jqXHR.responseText).fadeIn();
                    window.scrollTo(0,0)
                    console.log("Vote not valid:" + jqXHR.responseText)
                }
            });
    }
</script>