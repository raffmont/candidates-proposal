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
                parent: <?php echo get_plugin_options('roles')?>
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
                parent: <?php echo get_plugin_options('institutions')?>
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

      $("#candidates_proposal_form_accept").click(
            function(event)
            {
                  if ($("#candidates_proposal_form_accept").is(":checked"))
                  {
                        $("#candidates_proposal_form_submit").show();
                  } else 
                  {
                        $("#candidates_proposal_form_submit").hide();
                  }
            }
      );

      $("#candidates_proposal_form").submit(
            function(event)
            {
          
                  event.preventDefault();

                  $("#candidates_proposal_form_error").hide();
                  $("#candidates_proposal_form_name_error").hide();
                  $("#candidates_proposal_form_roles_error").hide();
                  $("#candidates_proposal_form_institution_error").hide();
                  $("#candidates_proposal_form_shortbio_error").hide();
                  $("#candidates_proposal_form_website_error").hide();
                  $("#candidates_proposal_form_picture_error1").hide();
                  $("#candidates_proposal_form_picture_error2").hide();

                  let check = true;
                  if ($("#candidates_proposal_form_name").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_name_error").fadeIn();
                  }
                  
                  if ($("#candidates_proposal_form_role").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_role_error").fadeIn();
                  }
                  if ($("#candidates_proposal_form_institution").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_institution_error").fadeIn();
                  }
                  if ($("#candidates_proposal_form_shortbio").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_shortbio_error").fadeIn();
                  }
                  if ($("#candidates_proposal_form_website").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_website_error").fadeIn();
                  }
                  if ($("#candidates_proposal_form_picture").val() == "") {
                        check = false;
                        $("#candidates_proposal_form_picture_error1").fadeIn();
                  } else
                  {

                        let upload_size = $('#candidates_proposal_form_picture')[0].files[0].size;
                        
                        if (upload_size >= <?php echo upload_max_size(); ?>)
                        {
                              check = false;
                              $("#candidates_proposal_form_picture_error2").fadeIn();
                        }
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
                  } 
            }
      );
});
</script>