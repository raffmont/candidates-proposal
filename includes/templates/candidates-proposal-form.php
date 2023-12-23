<?php  if( get_plugin_options('candidates_proposal_plugin_active') ):?>

<div id="candidates-proposal-plugin-container" style="background-color:gray; color:#000;">
      <div id="candidates-proposal-plugin-form-success" style="background-color:green; color:#fff;"></div>
      <div id="candidates-proposal-plugin-form-error" style="background-color:red; color:#fff;"></div>

      <form id="candidates-proposal-plugin-proposal-form" enctype="multipart/form-data">
            <?php wp_nonce_field('wp_rest');?>
            <input type="hidden" id="candidates-proposal-plugin-rest_url" name="rest_url" value="<?php echo get_rest_url(null, 'v1/candidates-proposal-form/submit');?>">
            <label>Name</label><br />
            <input type="text" id="candidates-proposal-plugin-name" name="name"><br/>
            <label>Role</label><br />
            <select id="candidates-proposal-plugin-role" name="role">
                  <option value="uncategorized" default></option>
                  <option value="full-professor">Full Professor</option>
                  <option value="associate-professor">Associate Professor</option>
                  <option value="researcher">Researcher</option>
                  <option value="technician">Technician</option>
                  <option value="administrative">Administrative</option>
            </select>
            <label>Institution</label><br />
            <select id="candidates-proposal-plugin-institution" name="institution">
                  <option value="uncategorized" default></option>
                  <option value="it-unina">University of Naples "Federico II"</option>
                  <option value="it-uniparthenope">University of Naples "Parthenope"</option>
                  <option value="it-unior">University of Naples "Orientale"</option>
                  <option value="it-unisob">University of Naples "Suor Orsola Benincasa"</option>
            </select>
            <label>Short Bio</label><br />
            <textarea id="candidates-proposal-plugin-shortbio" name="shortbio"></textarea><br />
            <label>Website</label><br />
            <input type="text" id="candidates-proposal-plugin-website" name="website"><br/>
            
            <label>Picture</label><br />
            <input type="file" id="candidates-proposal-plugin-picture" name="picture" accept="image/png, image/jpeg" /><br />
            <button type="submit">Propose</button>
      </form>
      
<?php else:?>

      <p>This form is not active</p>

<?php endif;?>
</div>

