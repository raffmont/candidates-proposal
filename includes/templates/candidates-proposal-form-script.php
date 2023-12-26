<script>
jQuery(document).ready(function($)
{
      // Fill roles /wp-json/wp/v2/categories?parent=8
      // <?php echo get_rest_url(null, "v2/categories"); ?>

      // Fill institutions /wp-json/wp/v2/categories?parent=9


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
                        headers: { 'x-wp-nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
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
});
</script>