<script>
(function($) {
      $("#candidates-proposal-plugin-proposal-form").submit(
            function(event)
            {
          
                  event.preventDefault();

                  $("#candidates-proposal-plugin-form_error").hide();

                  let url = $("#candidates-proposal-plugin-rest_url").val();

                  let data = new FormData($("#candidates-proposal-plugin-proposal-form")[0]);

                  // Display the key/value pairs
                  for (var pair of data.entries()) {
                        console.log(pair[0]+ ', ' + pair[1]); 
                  }

                  
                  $.ajax(
                  {
                        method: 'POST',
                        url: '<?php echo get_rest_url(null, "v1/candidates-proposal-form/submit"); ?>',
                        headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
                        dataType: 'json',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(res)
                        {

                              $("#candidates-proposal-plugin-proposal-form").hide();

                              $("#candidates-proposal-plugin-form-success").html(res).fadeIn();


                        },
                        error: function(jqXHR, exception){

                              $("#candidates-proposal-plugin-form-error").html(jqXHR.responseText).fadeIn();
                        }
                  });
            }
      );

      function candidates_proposal_plugin_vote(url) {
            console.log(url)
            $.getJSON( url, {  }).done(function( data ) {
                  console.log(data);
                  $("#votes").html(data["count"]);      
            });
      }
      
      function candidates_proposal_plugin_votes(url) {
            console.log(url)
            $.getJSON( url, {  }).done(function( data ) {
                  console.log(data);
                  $("#votes").html(data["count"]);       
            });
      }

}, jQuery)
</script>