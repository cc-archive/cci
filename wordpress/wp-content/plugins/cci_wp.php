<?php
 /*
 Plugin Name: Creative Commons International
 Plugin URI: http://creativecommons.org/worldwide
 Description: Support plugin for ccInternational sites
 Version: 1.0
 Author: Nathan R. Yergler <nathan@creativecommons.org>
 Author URI: http://yergler.net
 */
 
 
/* Template Functions */
 
function cci_name($display=1) {

  if ($display) {
     echo get_option('cci_country_name');
  } else {
     return get_option('cci_country_name');
  }
  
} // cci_name

function cci_code($display=1) {

  if ($display) {
     echo get_option('cci_country_code');
  } else {
     return get_option('cci_country_code');
  }
} // cci_code

/* Admin Interface Functions */

function cci_admin() {
   global $post_msg;
echo '
<div class="wrap">
         <div id="statusmsg">'.$post_msg.'</div>
         <h2>Creative Commons International</h2>

         <div id="license_selection" class="wrap">
            <form name="cci_options" method="post" 
                  action="' . $_SERVER[REQUEST_URI] . '">

            <input name="submitted"    type="hidden" value="cci" />

            <table>
               <tr><th>Country Name:</th>
               	   <td><input name="cci_country_name"
               	              value="'.get_option('cci_country_name').'" /></td>
               </tr>
               <tr><th>Country Code:</th>
               	   <td><input name="cci_country_code"
               	              value="'.get_option('cci_country_code').'" /></td>
               </tr>
               <tr><th>&nbsp;</th>
               	   <td>
               	      <input type="submit" value="save" />
                      <input type="reset"  value="cancel" id="cancel" />
               	   </td>
               </tr>
            </table>
            </form>
         </div>
 </div>
';

} // cci_admin

function cci_post_form() {
    global $post_msg;

    // check if the form was submitted
    if ( (isset($_POST['submitted'])) && ($_POST['submitted'] == 'cci') ) {
        update_option('cci_country_name', $_POST['cci_country_name']);
        update_option('cci_country_code', $_POST['cci_country_code']);
        
        $post_msg .= 'ccInternational settings updated successfully.';
        
    } // update the settings

} // cci_post_form

function cci_addAdminPage() {
	if (function_exists('add_options_page')) {
		add_options_page('ccInternational', 'ccInternational', 9, basename(__FILE__), 'cci_admin');
	} // if we can add a mgmt page
} // cci_addAdminPage

/* Registration */
add_action('admin_menu', 'cci_addAdminPage');
add_action('admin_head', 'cci_post_form');

?>
