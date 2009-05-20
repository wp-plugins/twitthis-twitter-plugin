<?php
/*
Plugin Name: TwitThis Posts Button
Plugin URI: http://twitthis.com/tools/
Description: Adds the TwitThis button into your posts and RSS feed and auto-tweets new posts.
Version: 2.0
Author: Jeremy Schoemaker
Author URI: shoemoney.com
*/

function tt_options() {
    add_options_page('Twitthis Settings', 'Twitthis', 8, __FILE__, 'twitthis_options_page');
}

function tt_update($content) {
    
    if (get_option('tt_display_page') == null) {
        if (is_page()) {
            return $content;
        }
    }
    
    if (get_option('tt_display_feed') == null) {
        if (is_feed()) {
            return $content;
        }
    }
    
    $button = '<div id="twitthis_button" style="' . get_option('tt_style') . '">

    <a href="http://twitthis.com/twit?url=' . get_permalink() . '&title='. urlencode(the_title('','',FALSE)) .'" ><img src="http://s3.chuug.com/chuug.twitthis.resources/twitthis_grey_72x22.gif"></a></div>';
                        
                    
    // Before and After code added by http://www.jimyaghi.com
    if (get_option('tt_where') == 'beforeandafter') {
        return $button . $content . $button;
    }
    else if (get_option('tt_where') == 'before') {
        return $button . $content;
    } else {
        return $content . $button;
    }

}


function tt_remove_filter($content) {
    remove_action('the_content', 'tt_update');
    return $content;
}

function tt_ping($post_id) {
	global $wpdb;

	$row = mysql_fetch_array(mysql_query("SELECT post_date,post_modified FROM $wpdb->posts WHERE id=$post_id"));

	if($row["post_date"] == $row["post_modified"]) {
		if(get_option('tt_username') && get_option('tt_password')) {
			 $url = get_permalink($post_id);
			 $title = get_the_title($post_id);

			$host = "twitthis.com";
			$path = "/ping_api.php";
			$request_content = "u=" . get_option('tt_username') . "&p=" . get_option('tt_password')."&url=" . urlencode($url) . "&title=". urlencode($title) ."";
			$length = strlen($request_content);
			$r = "\r\n";
			$request  = "POST $path HTTP/1.0$r";
			$request .= "Host: $host$r";
			$request .= "Content-Type: application/x-www-form-urlencoded$r";
			$request .= "User-Agent: twitthis wordpress plugin$r";
			$request .= "Content-length: {$length}$r$r";
			$request .= $request_content;
 
 
			$fp = @fsockopen($host, 80, $errno, $errstr, 30);
			if (!$fp) {
				$error = "an error occured connecting";
				return false;
			}
			fputs($fp, $request);
			fclose($fp);
		}
	}
}

function twitthis_options_page() {
?>
    <div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div><h2>Settings for Twitthis Integration for your blog</h2>
    <p>This plugin will install the twitthis button. 
    It can be easily styles in your blog posts and is referenced by the id <code>twitthis_button</code>. 
    </p>
    <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?> 
        <table class="form-table">
             <tr>
                <th scope="row">
                    Twitter Info:
                </th>
                <td>
                    <p>
                     <label for="username">Twitter Username:</label>
                    <input class="text" name="tt_username" type="text" group="ttaccount" value="<?php echo get_option('tt_username'); ?>" >
			</p>
                    <p>
                     <label for="password">Twitter Password:</label>
                    <input class="text" name="tt_password" type="text" group="ttaccount" value="<?php echo get_option('tt_password'); ?>" >
</p>
                </td>
            </tr>
 <tr>
                <th scope="row">
                    Position
                </th>
                <td>
                    <p>
                        <input type="radio" value="before" <?php if (get_option('tt_where') == 'before') echo 'checked="checked"'; ?> name="tt_where" group="tt_where"/> 
                        <label for="tt_where">Before the content of your post</label>     
                    </p>
                    <p>
                        <input type="radio" value="after" <?php if (get_option('tt_where') == 'after') echo 'checked="checked"'; ?> name="tt_where" group="tt_where" />
                        <label for="tt_where">After the content of your post</label>    
                    </p>
                    <p>
                        <input type="radio" value="beforeandafter" <?php if (get_option('tt_where') == 'beforeandafter') echo 'checked="checked"'; ?> name="tt_where" group="tt_where"/> 
                        <label for="tt_where">Before AND After the content of your post</label>     
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    Display
                </th>
                <td>
                    <p>
                        <input type="checkbox" value="1" <?php if (get_option('tt_display_page') == '1') echo 'checked="checked"'; ?> name="tt_display_page" group="tt_display"/> 
                        <label for="tt_display_page">Display the button on pages</label>     
                    </p>
                    <p>
                        <input type="checkbox" value="1" <?php if (get_option('tt_display_feed') == '1') echo 'checked="checked"'; ?> name="tt_display_feed" group="tt_display" />
                        <label for="tt_display_feed">Display the button on your feed</label>    
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tt_style">Styling</label></th>
                <td>
                    <input type="text" value="<?php echo get_option('tt_style'); ?>" name="tt_style" />
                    <span class="setting-description">Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code></span>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="tt_username,tt_password,tt_where,tt_style,tt_version,tt_display_page,tt_display_feed,tt_source" />
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    </div>
<?php
}

add_action('admin_menu', 'tt_options');
 
add_filter('the_content', 'tt_update');
add_filter('get_the_excerpt', 'tt_remove_filter', 9); 


add_option('tt_username');
add_option('tt_password');

add_option('tt_where');

add_option('tt_style', 'float: left; margin-right: 10px;');
  
add_option('tt_version', 'large'); 
add_option('tt_display_page', '1');
add_option('tt_display_feed', '1');


add_action('publish_post', 'tt_ping', 9);

?>
