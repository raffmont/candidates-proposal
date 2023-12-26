<script>
jQuery(document).ready(function($)
{
      function escapeHtml(unsafe)
      {
      return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&apos;");
      }

      // Fill roles /wp-json/wp/v2/categories?parent=8
      $.getJSON(
        '<?php echo get_rest_url(null, "wp/v2/categories"); ?>',
        {
                parent: 8
        }
        ).done(
            function( data )
            {
                  data.forEach(function(item)
                  {
                        $("#candidates_proposal_form_role").append('<option value="' + item.slug + '">' + escapeHtml(item.name) + '</option>' );     
                  });
            }
      );

      $.getJSON(
        '<?php echo get_rest_url(null, "wp/v2/categories"); ?>',
        {
                parent: 9
        }
        ).done(
            function( data )
            {
                  data.forEach(function(item)
                  {
                        $("#candidates_proposal_form_institution").append('<option value="' + item.slug + '">' + escapeHtml(item.name) + '</option>' );     
                  });
            }
      );


      $("#candidates_proposal_form").submit(
            function(event)
            {
          
                  event.preventDefault();

                  $("#candidates_proposal_form_error").hide();

                  let message = "";
                  let check = true;
                  if ($("#candidates_proposal_form_name").val() == "") {
                        check = false;
                        message += "Name is missing<br/>";
                  }
                  if ($("#candidates_proposal_form_role").val() == "") {
                        check = false;
                        message += "Role is missing<br/>";
                  }
                  if ($("#candidates_proposal_form_institution").val() == "") {
                        check = false;
                        message += "Institution is missing<br/>";
                  }
                  if ($("#candidates_proposal_form_shortbio").val() == "") {
                        check = false;
                        message += "Shortbio is missing<br/>";
                  }
                  if ($("#candidates_proposal_form_website").val() == "") {
                        check = false;
                        message += "Website is missing<br/>";
                  }
                  if ($("#candidates_proposal_form_picture").val() == "") {
                        check = false;
                        message += "Picture is missing<br/>";
                  }

                  if (check)
                  {

                        let data = new FormData($("#candidates_proposal_form")[0]);

                        // Display the key/value pairs
                        //for (var pair of data.entries()) {
                        //      console.log(pair[0]+ ', ' + pair[1]); 
                        //}

                        
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

                                    $("#candidates_proposal_form").hide();

                                    $("#candidates_proposal_form_success").html(res).fadeIn();


                              },
                              error: function(jqXHR, exception){

                                    $("#candidates_proposal_form_error").html(jqXHR.responseText).fadeIn();
                              }
                        });
                  } else 
                  {
                        $("#candidates_proposal_form_error").html(message).fadeIn();
                  }
            }
      );
});
</script>