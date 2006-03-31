<?php
define('WP_INSTALLING', true);
if (!file_exists('../wp-config.php')) 
    die("There doesn't seem to be a <code>wp-config.php</code> file. I need this before we can get started. Need more help? <a href='http://wordpress.org/docs/faq/#wp-config'>We got it</a>. You can <a href='setup-config.php'>create a <code>wp-config.php</code> file through a web interface</a>, but this doesn't work for all server setups. The safest way is to manually create the file.");

require_once('../wp-config.php');
require_once('./upgrade-functions.php');

$guessurl = str_replace('/wp-admin/install.php?step=2', '', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) );

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;
header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('WordPress &rsaquo; Installation'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style media="screen" type="text/css">
	<!--
	html {
		background: #eee;
	}
	body {
		background: #fff;
		color: #000;
		font-family: Georgia, "Times New Roman", Times, serif;
		margin-left: 20%;
		margin-right: 20%;
		padding: .2em 2em;
	}
	
	h1 {
		color: #006;
		font-size: 18px;
		font-weight: lighter;
	}
	
	h2 {
		font-size: 16px;
	}
	
	p, li, dt {
		line-height: 140%;
		padding-bottom: 2px;
	}

	ul, ol {
		padding: 5px 5px 5px 20px;
	}
	#logo {
		margin-bottom: 2em;
	}
	.step a, .step input {
		font-size: 2em;
	}
	td input {
		font-size: 1.5em;
	}
	.step, th {
		text-align: right;
	}
	#footer {
		text-align: center; 
		border-top: 1px solid #ccc; 
		padding-top: 1em; 
		font-style: italic;
	}
	-->
	</style>
</head>
<body>
<h1 id="logo"><img alt="WordPress" src="http://static.wordpress.org/logo.png" /></h1>
<?php
// Let's check to make sure WP isn't already installed.
$wpdb->hide_errors();
$installed = $wpdb->get_results("SELECT * FROM $wpdb->users");
if ($installed) die(__('<h1>Already Installed</h1><p>You appear to have already installed WordPress. To reinstall please clear your old database tables first.</p>') . '</body></html>');
$wpdb->show_errors();

switch($step) {

	case 0:
?>
<p><?php printf(__('Welcome to WordPress installation. We&#8217;re now going to go through a few steps to get you up and running with the latest in personal publishing platforms. You may want to peruse the <a href="%s">ReadMe documentation</a> at your leisure.'), '../readme.html'); ?></p>
	<h2 class="step"><a href="install.php?step=1"><?php _e('First Step &raquo;'); ?></a></h2>

<?php
	break;

	case 1:

?>
<h1><?php _e('First Step'); ?></h1>
<p><?php _e("Before we begin we need a little bit of information. Don't worry, you can always change these later."); ?></p>

<form id="setup" method="post" action="install.php?step=2">
<table width="100%">
<tr>
<th width="33%"><?php _e('Admin e-mail:'); ?></th>
	<td><input name="admin_email" type="text" id="admin_email" size="25" /></td>
</tr>
<th><?php _e('Country Name:'); ?></th>
	<td><input name="cci_name" type="text" id="cci_name" size="25" /></td>
</tr>
<th><?php _e('Country Code:'); ?></th>
	<td><input name="cci_code" type="text" id="cci_code" size="25" /></td>
</tr>
</table>
<p><em><?php _e('Double-check that email address before continuing.'); ?></em></p>
<h2 class="step">
<input type="submit" name="Submit" value="<?php _e('Continue to Second Step &raquo;'); ?>" />
</h2>
</form>

<?php
	break;
	case 2:

// Fill in the data we gathered
$weblog_title = "Creative Commons ".$_POST['cci_name'];
$weblog_desc = "A Collaboration between Creative Commons and ".$_POST['cci_name'];
$admin_email = $_POST['admin_email'];
// check e-mail address
if (empty($admin_email)) {
	die (__("<strong>ERROR</strong>: please type your e-mail address"));
} else if (!is_email($admin_email)) {
	die (__("<strong>ERROR</strong>: the e-mail address isn't correct"));
}
	
?>
<h1><?php _e('Second Step'); ?></h1>
<p><?php _e('Now we&#8217;re going to create the database tables and fill them with some default data.'); ?></p>


<?php
flush();

// Set everything up
make_db_current_silent();
populate_options();

$wpdb->query("UPDATE $wpdb->options SET option_value = '$weblog_title' WHERE option_name = 'blogname'");
$wpdb->query("UPDATE $wpdb->options SET option_value = '$weblog_desc' WHERE option_name = 'blogdescription'");

$wpdb->query("UPDATE $wpdb->options SET option_value = '$admin_email' WHERE option_name = 'admin_email'");

// Now drop in some default links
$wpdb->query("INSERT INTO $wpdb->linkcategories (cat_id, cat_name) VALUES (1, '".addslashes(__('Links'))."')");
$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://creativecommons.org/worldwide', 'Creative Commons Worldwide', 1, '');");
$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://creativecommons.org/license', 'Choose a Creative Commons License', 1, '');");

// Default category
$wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename, category_description) VALUES ('0', '".addslashes(__('News'))."', '".sanitize_title(__('News'))."', 'News')");
$wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename, category_description) VALUES ('0', '".addslashes(__('Featured Commoners'))."', '".sanitize_title(__('featured'))."', 'Featured Commoners')");

// Template activation
$wpdb->query("UPDATE $wpdb->options SET option_value = 'cci' WHERE option_name = 'template'");
$wpdb->query("UPDATE $wpdb->options SET option_value = 'cci' WHERE option_name = 'stylesheet'");

// Default plugin activation
//a:8:{i:0;s:25:"breadcrumb-navigation.php";i:1;s:11:"license.php";i:2;s:11:"phpexec.php";i:3;s:16:"post-updated.php";i:4;s:20:"sem-static-front.php";i:5;s:11:"sitemap.php";i:6;s:12:"textile1.php";i:7;s:13:"wpLicense.php";}
$plugins = 'a:2:{i:0;s:13:"wpLicense.php";i:1;s:10:"cci_wp.php";}';
$wpdb->query("UPDATE $wpdb->options SET option_value = '$plugins' WHERE option_name = 'active_plugins'");

// Plugin settings
$country_name = $_POST['cci_name'];
$country_code = $_POST['cci_code'];

$wpdb->query("INSERT INTO $wpdb->options (option_name, option_value) VALUES ('cci_country_name', '$country_name')");
$wpdb->query("INSERT INTO $wpdb->options (option_name, option_value) VALUES ('cci_country_code', '$country_code')");

/*
INSERT INTO options (option_id, blog_id, option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES (67, 0, 'cc_content_license', 'Y', 1, 'Attribution 2.5', 20, 8, '', 1, 'yes');
INSERT INTO options (option_id, blog_id, option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES (68, 0, 'cc_content_license_uri', 'Y', 1, 'http://creativecommons.org/licenses/by/2.5/', 20, 8, '', 1, 'yes');
INSERT INTO options (option_id, blog_id, option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES (69, 0, 'cc_content_license_rdf', 'Y', 1, '<rdf>\r\n <rdf:RDF xmlns:rdf=\\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\\" xmlns=\\"http://web.resource.org/cc/\\" xmlns:dc=\\"http://purl.org/dc/elements/1.1/\\">\r\n  <Work rdf:about=\\"\\">\r\n   <license rdf:resource=\\"http://creativecommons.org/licenses/by/2.5/\\" />\r\n  </Work>\r\n  <License rdf:about=\\"http://creativecommons.org/licenses/by/2.5/\\">\r\n   <permits rdf:resource=\\"http://web.resource.org/cc/Reproduction\\" />\r\n   <permits rdf:resource=\\"http://web.resource.org/cc/Distribution\\" />\r\n   <requires rdf:resource=\\"http://web.resource.org/cc/Notice\\" />\r\n   <requires rdf:resource=\\"http://web.resource.org/cc/Attribution\\" />\r\n   <permits rdf:resource=\\"http://web.resource.org/cc/DerivativeWorks\\" />\r\n  </License>\r\n </rdf:RDF>\r\n</rdf>', 20, 8, '', 1, 'yes');
INSERT INTO options (option_id, blog_id, option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES (70, 0, 'cc_content_license_html', 'Y', 1, '<!--Creative Commons License--><a rel=\\"license\\" href=\\"http://creativecommons.org/licenses/by/2.5/\\"><img alt=\\"Creative Commons License\\" border=\\"0\\" src=\\"http://creativecommons.org/images/public/somerights20.gif\\"/></a><br/>\r\n		This work is licensed under a <a rel=\\"license\\" href=\\"http://creativecommons.org/licenses/by/2.5/\\">Creative Commons License</a>.\r\n		<!--/Creative Commons License--><!-- <rdf:RDF xmlns:rdf=\\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\\" xmlns=\\"http://web.resource.org/cc/\\" xmlns:dc=\\"http://purl.org/dc/elements/1.1/\\"><Work rdf:about=\\"\\"><license rdf:resource=\\"http://creativecommons.org/licenses/by/2.5/\\"/></Work><License rdf:about=\\"http://creativecommons.org/licenses/by/2.5/\\"><permits rdf:resource=\\"http://web.resource.org/cc/Reproduction\\"/><permits rdf:resource=\\"http://web.resource.org/cc/Distribution\\"/><requires rdf:resource=\\"http://web.resource.org/cc/Notice\\"/><requires rdf:resource=\\"http://web.resource.org/cc/Attribution\\"/><permits rdf:resource=\\"http://web.resource.org/cc/DerivativeWorks\\"/></License></rdf:RDF> -->', 20, 8, '', 1, 'yes');
INSERT INTO options (option_id, blog_id, option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES (71, 0, 'cc_include_footer', 'Y', 1, '1', 20, 8, '', 1, 'yes');
*/

$wpdb->query("INSERT INTO $wpdb->options (option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES ('cc_content_license', 'Y', 1, 'Attribution 2.5', 20, 8, '', 1, 'yes');");
$wpdb->query("INSERT INTO $wpdb->options (option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES ('cc_content_license_uri', 'Y', 1, 'http://creativecommons.org/licenses/by/2.5/', 20, 8, '', 1, 'yes');");
$wpdb->query("INSERT INTO $wpdb->options (option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES ('cc_content_license_rdf', 'Y', 1, '<rdf>\r\n <rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns=\"http://web.resource.org/cc/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\r\n  <Work rdf:about=\"\">\r\n   <license rdf:resource=\"http://creativecommons.org/licenses/by/2.5/\" />\r\n  </Work>\r\n  <License rdf:about=\"http://creativecommons.org/licenses/by/2.5/\">\r\n   <permits rdf:resource=\"http://web.resource.org/cc/Reproduction\" />\r\n   <permits rdf:resource=\"http://web.resource.org/cc/Distribution\" />\r\n   <requires rdf:resource=\"http://web.resource.org/cc/Notice\" />\r\n   <requires rdf:resource=\"http://web.resource.org/cc/Attribution\" />\r\n   <permits rdf:resource=\"http://web.resource.org/cc/DerivativeWorks\" />\r\n  </License>\r\n </rdf:RDF>\r\n</rdf>', 20, 8, '', 1, 'yes');");
$wpdb->query("INSERT INTO $wpdb->options (option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES ('cc_content_license_html', 'Y', 1, '<!--Creative Commons License--><a rel=\"license\" href=\"http://creativecommons.org/licenses/by/2.5/\"><img alt=\"Creative Commons License\" border=\"0\" src=\"http://creativecommons.org/images/public/somerights20.gif\"/></a><br/>\r\n		This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by/2.5/\">Creative Commons License</a>.\r\n		<!--/Creative Commons License--><!-- <rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns=\"http://web.resource.org/cc/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\"><Work rdf:about=\"\"><license rdf:resource=\"http://creativecommons.org/licenses/by/2.5/\"/></Work><License rdf:about=\"http://creativecommons.org/licenses/by/2.5/\"><permits rdf:resource=\"http://web.resource.org/cc/Reproduction\"/><permits rdf:resource=\"http://web.resource.org/cc/Distribution\"/><requires rdf:resource=\"http://web.resource.org/cc/Notice\"/><requires rdf:resource=\"http://web.resource.org/cc/Attribution\"/><permits rdf:resource=\"http://web.resource.org/cc/DerivativeWorks\"/></License></rdf:RDF> -->', 20, 8, '', 1, 'yes');");
$wpdb->query("INSERT INTO $wpdb->options (option_name, option_can_override, option_type, option_value, option_width, option_height, option_description, option_admin_level, autoload) VALUES ('cc_include_footer', 'Y', 1, '1', 20, 8, '', 1, 'yes');");

// First post
$now = date('Y-m-d H:i:s');
$now_gmt = gmdate('Y-m-d H:i:s');
//$wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt) VALUES ('1', '$now', '$now_gmt', '".addslashes(__('Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!'))."', '".addslashes(__('Hello world!'))."', '0', '".addslashes(__('hello-world'))."', '$now', '$now_gmt')");

//$wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (1, 1, 1)" );

// Default comment
//$wpdb->query("INSERT INTO $wpdb->comments (comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content) VALUES ('1', '".addslashes(__('Mr WordPress'))."', '', 'http://wordpress.org', '127.0.0.1', '$now', '$now_gmt', '".addslashes(__('Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.'))."')");

// Set up admin user
$random_password = substr(md5(uniqid(microtime())), 0, 6);
$wpdb->query("INSERT INTO $wpdb->users (ID, user_login, user_pass, user_nickname, user_email, user_level, user_idmode, user_registered) VALUES ( '1', 'admin', MD5('$random_password'), '".addslashes(__('Administrator'))."', '$admin_email', '10', 'nickname', NOW() )");

$message_headers = 'From: ' . stripslashes($_POST['weblog_title']) . ' <wordpress@' . $_SERVER['SERVER_NAME'] . '>';
$message = sprintf(__("Your new WordPress blog has been successfully set up at:

%1\$s

You can log in to the administrator account with the following information:

Username: admin
Password: %2\$s

We hope you enjoy your new weblog. Thanks!

--The WordPress Team
http://wordpress.org/
"), $guessurl, $random_password);

@mail($admin_email, __('New WordPress Blog'), $message, $message_headers);

upgrade_all();
?>

<p><em><?php _e('Finished!'); ?></em></p>

<p><?php printf(__('Now you can <a href="%1$s">log in</a> with the <strong>username</strong> "<code>admin</code>" and <strong>password</strong> "<code>%2$s</code>".'), '../wp-login.php', $random_password); ?></p>
<p><?php _e('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you. If you lose it, you will have to delete the tables from the database yourself, and re-install WordPress. So to review:'); ?>
</p>
<dl>
<dt><?php _e('Username'); ?></dt>
<dd><code>admin</code></dd>
<dt><?php _e('Password'); ?></dt>
<dd><code><?php echo $random_password; ?></code></dd>
	<dt><?php _e('Login address'); ?></dt>
<dd><a href="../wp-login.php">wp-login.php</a></dd>
</dl>
<p><?php _e('Were you expecting more steps? Sorry to disappoint. All done! :)'); ?></p>
<?php
	break;
}
?>
<p id="footer"><?php _e('<a href="http://wordpress.org/">WordPress</a>, personal publishing platform.'); ?></p>
</body>
</html>
