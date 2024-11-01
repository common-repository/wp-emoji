<?php
/*
 * Plugin Name: WP-Emoji
 * Plugin URI: http://appdp.com/
 * Description: 在你的博客里显示 Emoji 表情。Display Apple Emoji fonts(SoftBank) in your WordPress blog.
 * Version: 1.2
 * Author: Appdp.com
 * Author URI: http://appdp.com/
 * License: GPLv2 or later
 */

load_plugin_textdomain( 'WPE', false, basename(dirname(__FILE__)).'/languages/' ); 

include_once(dirname(__FILE__).'/emoji.php');

if(!get_option('WPE_where_emoji_support')){
	add_action('admin_notices', 'WPE_warning');
}
function WPE_warning(){
	echo "<div id='akismet-warning' class='updated fade'><p><strong>".__('WP-Emoji is ready.', 'WPE')."</strong> ".sprintf(__('But you also need to do some <a href="%1$s">settings</a> make WP-Emoji work.', 'WPE'), "options-general.php?page=wordpress-emoji")."</p></div>";
}

// emoji 表情 shortcode
add_shortcode( 'emoji', 'emoji_shortcode_handler' );
function emoji_shortcode_handler($attr, $content){
	return '<img src="'.content_url() .'/plugins/' . basename(dirname(__FILE__)) .'/emoji/'.strtolower($content).'.png" class="wp-emoji" />';
}

// 让评论支持 emoji shortcode
if(in_array('comment', get_option('WPE_where_emoji_support')?get_option('WPE_where_emoji_support'):array())){
	add_action('comment_text', 'comment_text_emoji_convertor', 99, 1);
}
function comment_text_emoji_convertor($comment_text){
	$comment_text = do_shortcode($comment_text);
	return $comment_text;
}

// 把评论中的 emoji 字符转换成 shortcode
add_filter('preprocess_comment', 'comment_emoji_convertor', 99, 1);
function comment_emoji_convertor($commentdata){
	$commentdata['comment_content'] = emoji_unified_to_shortcode($commentdata['comment_content']);
	return $commentdata;
}

// 把文章中的 emoji 字符转换成 shortcode
if(in_array('post', get_option('WPE_where_emoji_support')?get_option('WPE_where_emoji_support'):array())){
	add_filter('wp_insert_post_data', 'post_emoji_convertor', 99, 2);
}
function post_emoji_convertor($data, $postarr){
	if(get_option('WPE_emoji_convert_to') == 'shortcode'){
		$data['post_content'] = emoji_unified_to_shortcode($data['post_content']);
	}
	if(get_option('WPE_emoji_convert_to') == 'image'){
		$data['post_content'] = emoji_unified_to_img($data['post_content']);
	}
	return $data;
}

add_action('admin_menu', 'WPE_option_page');
function WPE_option_page() {
	add_options_page(__('WP-Emoji Settings', 'WPE'), __('WP-Emoji Settings', 'WPE'), 'manage_options', 'wordpress-emoji', 'WPE_option_page_content');
}

function WPE_option_page_content(){
	if($_POST){
		check_admin_referer('WPE_setting_save','WPE_setting_save_nonce');
		$where_emoji_support = $_POST['where_emoji_support'];
		$emoji_convert_to = $_POST['emoji_convert_to'];
		update_option('WPE_where_emoji_support', $where_emoji_support);
		update_option('WPE_emoji_convert_to', $emoji_convert_to);
	}
	$where_emoji_support = get_option('WPE_where_emoji_support')?get_option('WPE_where_emoji_support'):array();
	$emoji_convert_to = get_option('WPE_emoji_convert_to');
	echo json_encode(array('商品转换', '店铺转换', '商品搜索', '店铺搜索', '商品ID', '不同ID使用半角逗号分隔，最多支持10个ID', '转换', '商品转换结果'));
	?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div><h2><?php _e('Emoji Settings', 'WPE');?></h2>

<form method="post" action="<?php echo admin_url('options-general.php?page=wordpress-emoji'); ?>" name="form">

<h3><?php _e('Emoji Settings', 'WPE');?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label><?php _e('Where need to convert Emoji Fonts?', 'WPE');?></label>
			</th>
			<td>
				<label><input type="checkbox" value="post" name="where_emoji_support[]"<?php if(in_array('post', $where_emoji_support)) echo ' checked="checked"'; ?>> <?php _e('Posts', 'WPE');?></label>
				<label><input type="checkbox" value="comment" name="where_emoji_support[]"<?php if(in_array('comment', $where_emoji_support)) echo ' checked="checked"'; ?>> <?php _e('Comments', 'WPE');?></label>
				<p><?php _e('Posts include all post types.', 'WPE');?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="emoji_convert_to"><?php _e('Emoji fonts convert to...?', 'WPE');?></label>
			</th>
			<td>
				<select name="emoji_convert_to" id="emoji_convert_to">
					<option value="shortcode"<?php if(get_option('WPE_emoji_convert_to') == 'shortcode') echo ' selected="selected"';?>><?php _e('Shortcode', 'WPE');?></option>
					<option value="image"<?php if(get_option('WPE_emoji_convert_to') == 'image') echo ' selected="selected"';?>><?php _e('Image', 'WPE');?></option>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit"><?php wp_nonce_field('WPE_setting_save','WPE_setting_save_nonce'); ?><input type="submit" value="<?php _e('Save', 'WPE');?>" class="button-primary" id="submit" name="submit"></p> 
</form>

</div>
	<?php
}
