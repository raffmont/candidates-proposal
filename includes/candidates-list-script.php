<script>
    jQuery(document).ready(function($)
    {
        // Fill roles /wp-json/wp/v2/categories?parent=8
        $.getJSON(
            '<?php echo get_rest_url(null, "v1/candidates/list"); ?>', {}
        ).done(
            function( data ) {
                let candidates = data["candidates"]

                candidates.sort((a, b) => b.votes - a.votes)

                candidates.forEach(function(item, index) {
                    $("#candidates_proposal_list_table_votes > tbody:last-child").append(
                        '<tr>' +
                        '<td><img src="' + item.image + '"/></td>' +
                        '<td>' + candidates_proposal_escapeHtml(item.name) + '</td>' +
                        '<td>' + candidates_proposal_escapeHtml(item.role) + '</td>' +
                        '<td>' + candidates_proposal_escapeHtml(item.institution) + '</td>' +
                        '<td><span id="candidates_proposal_list_votes_'+item.id+'">' + item.votes + '</span></td>' +
                        '<td><button onclick="candidates_proposal_list_vote('+item.id+')">Vote!</button>' +
                        '<td><a href="' + item.link + '">[link]</td>' +
                        '</tr>'
                    );
                });
            }
        );
    });

    function candidates_proposal_escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&apos;");
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
                        console.log("Vote not valid: register first!")
                    } else
                    {
                        $("#candidates_proposal_list_error").html('<?php echo $time_between_votes; ?>').fadeIn();
                        console.log("Vote not valid: too much votes!")
                    }
                },
                error: function(jqXHR, exception){
                    $("#candidates_proposal_list_error").html(jqXHR.responseText).fadeIn();
                    console.log("Vote not valid:" + jqXHR.responseText)
                }
            });
    }
</script>