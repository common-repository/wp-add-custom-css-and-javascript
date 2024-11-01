<?php
/**
 * Plugin Name: WP Add Custom CSS and JS
* Plugin URI: http://d3logics.com/plugins/wp-add-custom-css-js
 * Description:  Add Custom JS and CSS from wp admin panel
 * Version: 1.0.1
 * Author: d3logics
 * Author URI: http://d3logics.com
 * Text Domain: wp-add-custom-css-js
 * Requires: 3.7 or higher
 * License: GPLv2 or later
 *
 * Copyright 2018 Vinit Sharma iamvinitsharma@gmail.com
 *
 * WP Add Custom Css and Javascript  is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * WP Add Custom Css and Javascript is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

add_action('wp_enqueue_scripts', 'd3_custom_css_js_scripts', 999999);
function d3_custom_css_js_scripts() {
	if (current_user_can('edit_theme_options')) {
		wp_enqueue_script('d3_custom_js', get_site_url(null, '/index.php').'?d3_custom_js_draft=1', array(), time());
		wp_enqueue_style('d3_custom_css', get_site_url(null, '/index.php').'?d3_custom_css_draft=1', array(), time());
	} else {
		$uploadDir = wp_upload_dir();
		if (is_ssl()) {
			$uploadDir['baseurl'] = set_url_scheme($uploadDir['baseurl'], 'https');
		}
		if (file_exists($uploadDir['basedir'].'/d3_custom_css_js/custom.js'))
			wp_enqueue_script('d3_custom_js', $uploadDir['baseurl'].'/d3_custom_css_js/custom.js', array(), get_option('d3_custom_javascript_ver', 1));
		if (file_exists($uploadDir['basedir'].'/d3_custom_css_js/custom.css'))
			wp_enqueue_style('d3_custom_css', $uploadDir['baseurl'].'/d3_custom_css_js/custom.css', array(), get_option('d3_custom_css_ver', 1));
	}
}
add_action('admin_menu', 'd3_custom_css_admin_menu');
function d3_custom_css_admin_menu() {

	add_menu_page('Custom CSS JS', 'Custom CSS JS ', 'manage_options', 'd3_custom_css', 'd3_custom_css_page','dashicons-media-code',15);
	add_submenu_page('d3_custom_css', 'Custom Javascript', 'Custom CSS', 'manage_options', 'd3_custom_css', 'd3_custom_css_page');
}
add_action('admin_menu', 'd3_custom_js_admin_menu');
function d3_custom_js_admin_menu() {
	add_submenu_page('d3_custom_css', 'Custom Javascript', 'Custom JS', 'manage_options', 'd3_custom_js', 'd3_custom_js_page');
}
add_action('admin_enqueue_scripts', 'd3_custom_css_js_admin_scripts');
function d3_custom_css_js_admin_scripts($hook) {

		if ($hook != 'toplevel_page_d3_custom_css' && $hook != 'custom-css-js_page_d3_custom_js')
		return;
	wp_enqueue_script('d3_custom_css_js_codemirror', plugins_url('codemirror/codemirror.js', __FILE__));
	if ($hook == 'toplevel_page_d3_custom_css')
		wp_enqueue_script('d3_custom_css_js_codemirror_mode_css', plugins_url('codemirror/mode/css.js', __FILE__));
	else
	wp_enqueue_script('d3_custom_css_js_codemirror_mode_js', plugins_url('codemirror/mode/javascript.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_dialog', plugins_url('codemirror/addon/dialog/dialog.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_matchbrackets', plugins_url('codemirror/addon/edit/matchbrackets.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_search', plugins_url('codemirror/addon/search/search.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_searchcursor', plugins_url('codemirror/addon/search/searchcursor.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_matchhighlighter', plugins_url('codemirror/addon/search/match-highlighter.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_annotatescrollbar', plugins_url('codemirror/addon/scroll/annotatescrollbar.js', __FILE__));
	wp_enqueue_script('d3_custom_css_js_codemirror_matchesonscrollbar', plugins_url('codemirror/addon/search/matchesonscrollbar.js', __FILE__));
	
	wp_enqueue_style('d3_custom_css_js_codemirror', plugins_url('codemirror/codemirror.css', __FILE__));
	wp_enqueue_style('d3_custom_css_js_codemirror_dialog', plugins_url('codemirror/addon/dialog/dialog.css', __FILE__));
	wp_enqueue_style('d3_custom_css_js_codemirror_matchesonscrollbar', plugins_url('codemirror/addon/search/matchesonscrollbar.css', __FILE__));
	wp_enqueue_script('d3_custom_css_js', plugins_url('js/wp-add-custom-css-js.js', __FILE__));
	wp_enqueue_style('d3_custom_css_js', plugins_url('css/wp-add-custom-css-js.css', __FILE__));
}

add_action('wp_ajax_d3_custom_css_js_save', 'd3_custom_css_js_save');
function d3_custom_css_js_save() {
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']) || !isset($_POST['code']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();
	
	$_POST['code'] = (get_magic_quotes_gpc() ? stripslashes($_POST['code']) : $_POST['code']);
	
	$rev_id = wp_insert_post(array(
		'post_content' => $_POST['code'],
		'post_status' => 'draft',
		'post_type' => 'd3_custom_'.$_POST['mode'],
	));
	if ($rev_id === false)
		wp_send_json_error();
	
	wp_send_json_success($rev_id);
}

add_action('wp_ajax_d3_custom_css_js_publish', 'd3_custom_css_js_publish');
function d3_custom_css_js_publish() {
	$mode =  array_map( 'sanitize_text_field', wp_unslash( $_POST['mode'] ) );
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']) || !isset($_POST['rev']) || !is_numeric($_POST['rev']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();
	
	$post = get_post($_POST['rev']);
	if ($post->post_type != 'd3_custom_'.$_POST['mode'])
		wp_send_json_error();
	
	$uploadDir = wp_upload_dir();
	if (!is_dir($uploadDir['basedir'].'/d3_custom_css_js'))
		mkdir($uploadDir['basedir'].'/d3_custom_css_js') or wp_send_json_error();
	$outputFile = $uploadDir['basedir'].'/d3_custom_css_js/custom.'.($_POST['mode'] == 'css' ? 'css' : 'js');
	if (file_put_contents($outputFile, $post->post_content) === false)
		wp_send_json_error();
	if (empty($_POST['minify'])) {
		
		update_option('d3_custom_'.$$mod.'_minify', false);
	} else {
		update_option('d3_custom_'.$mode.'_minify', true);
		require_once(__DIR__.'/minify/src/Minify.php');
		require_once(__DIR__.'/minify/src/Exception.php');
		if ($_POST['mode'] == 'css') {
			require_once(__DIR__.'/minify/src/CSS.php');
			require_once(__DIR__.'/minify/src/Converter.php');
			$minifier = new MatthiasMullie\Minify\CSS;
		} else {
			require_once(__DIR__.'/minify/src/JS.php');
			$minifier = new MatthiasMullie\Minify\JS;
		}
		$minifier->add($outputFile);
		$minifier->minify($outputFile);
	}
	
	update_option('d3_custom_'.$mode.'_ver', time());
	
	// Unpublish previous revisions
	$wp_query = new WP_Query(array(
		'post_type' => 'd3_custom_'.$_POST['mode'],
		'post_status' => 'publish',
		'fields' => 'ids',
		'nopaging' => true
	));
	$posts = $wp_query->get_posts();
	foreach ($posts as $postId) {
		if (!wp_update_post(array(
		'ID' => $postId,
		'post_status' => 'draft',
		)))
		wp_send_json_error();
	}
	
	if (!wp_update_post(array(
		'ID' => $_POST['rev'],
		'post_status' => 'publish',
		'post_date' => current_time('Y-m-d H:i:s'),
		)))
		wp_send_json_error();
	
	wp_send_json_success();
}

add_action('wp_ajax_d3_custom_css_js_delete_revision', 'd3_custom_css_js_delete_revision');
function d3_custom_css_js_delete_revision() {
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']) || !isset($_POST['rev']) || !is_numeric($_POST['rev']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();
	
	$post = get_post($_POST['rev']);
	if ($post->post_type != 'd3_custom_'.$_POST['mode'] || $post->post_status == 'publish')
		wp_send_json_error();
	
	
	if (!wp_delete_post($post->ID, true))
		wp_send_json_error();
	
	wp_send_json_success();
}

add_action('wp_ajax_d3_custom_css_js_delete_revisions', 'd3_custom_css_js_delete_revisions');
function d3_custom_css_js_delete_revisions() {
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();
	
	$wp_query = new WP_Query(array(
		'post_type' => 'd3_custom_'.$_POST['mode'],
		'post_status' => 'draft',
		'fields' => 'ids',
		'nopaging' => true
	));
	$posts = $wp_query->get_posts();
	foreach ($posts as $postId) {
		if (!wp_delete_post($postId, true))
			wp_send_json_error();
	}
	
	wp_send_json_success();
}


add_action('wp_ajax_d3_custom_css_js_get_revisions', 'd3_custom_css_js_get_revisions');
function d3_custom_css_js_get_revisions() {
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();

	$wp_query = new WP_Query();
	$posts = $wp_query->query(array(
		'post_type' => 'd3_custom_'.$_POST['mode'],
		'post_status' => 'any',
		'nopaging' => true
	));
	
	
	$revisions = array();
	if (empty($posts)) {
		$uploadDir = wp_upload_dir();
		$customFile = $uploadDir['basedir'].'/d3_custom_css_js/custom.'.($_POST['mode'] == 'css' ? 'css' : 'js');
		if (file_exists($customFile)) {
			$contents = file_get_contents($customFile);
			if ($contents === false)
				wp_send_json_error();
			$rev_id = wp_insert_post(array(
				'post_content' => $contents,
				'post_status' => 'publish',
				'post_type' => 'd3_custom_'.$_POST['mode'],
			));
			$revisions[] = array('id' => $rev_id, 'rev_date' => current_time('Y-m-d H:i:s'), 'published' => true);
		}
	} else {
		foreach ($posts as $post) {
			$revisions[] = array('id' => $post->ID, 'rev_date' => $post->post_date, 'published' => ($post->post_status == 'publish'));
		}
	}
	
	wp_send_json_success($revisions);
}

add_action('wp_ajax_d3_custom_css_js_get_revision', 'd3_custom_css_js_get_revision');
function d3_custom_css_js_get_revision() {
	if (!current_user_can('edit_theme_options') || empty($_POST['mode']) || !isset($_POST['rev']) || !is_numeric($_POST['rev']))
		wp_send_json_error();
	$_POST['mode'] = strtolower($_POST['mode']);
	if ($_POST['mode'] != 'css' && $_POST['mode'] != 'javascript')
		wp_send_json_error();
	
	$post = get_post($_POST['rev']);
	if ($post->post_type != 'd3_custom_'.$_POST['mode'])
		wp_send_json_error();
	
	wp_send_json_success(array(
		'id' => $post->ID,
		'content' => $post->post_content
	));
}

add_action('init', 'd3_custom_css_js_init');
function d3_custom_css_js_init() {
	register_post_type('d3_custom_css');
	register_post_type('d3_custom_javascript');
	
	if (!empty($_GET['d3_custom_css_draft'])) {
		$wp_query = new WP_Query(array(
			'post_type' => 'd3_custom_css',
			'post_status' => 'any',
			'posts_per_page' => 1
		));
		$posts = $wp_query->get_posts();
		header('Content-Type: text/css');
		if (isset($posts[0]))
			echo($posts[0]->post_content);
		exit;
	}
	if (!empty($_GET['d3_custom_js_draft'])) {
		$wp_query = new WP_Query(array(
			'post_type' => 'd3_custom_javascript',
			'post_status' => 'any',
			'posts_per_page' => 1
		));
		$posts = $wp_query->get_posts();
		header('Content-Type: text/javascript');
		if (isset($posts[0]))
			echo($posts[0]->post_content);
		exit;
	}
}

function d3_custom_css_page() {
	d3_custom_css_js_page('CSS');
}

function d3_custom_js_page() {
	d3_custom_css_js_page('Javascript');
}

function d3_custom_css_js_page($mode) {
	echo('
		<div class="wrap">
			<h2>Custom '.$mode.'</h2>
			<script>var d3_custom_css_js_mode = "'.$mode.'";</script>
			<div>
				<div id="d3_custom_code_editor" style="margin-top: 15px;">
					<div style="width: 200px; height: 100%; overflow: auto; float: right; padding: 0 20px;">
						<div id="pp_custom_css_js_dev_info">Best Plugin For Adding Custom Js and Css 
						</div>
						<h4 style="margin: 0; margin-bottom: 5px;">Revisions:</h4>
						<button class="button-secondary d3-custom-css-js-delete-revisions-btn">Delete All</button>
						<ul id="d3_custom_css_js_revisions">
						</ul>
					</div>
				</div>
				<div style="float: right; padding-left: 10px; margin-top: 3px; white-space: nowrap; font-style: italic;">
					<a href="https://codemirror.net/" target="_blank">CodeMirror</a> code editor
				</div>
				<button type="button" class="button-primary d3-custom-css-js-save-btn" style="margin-top: 15px;" disabled="disabled">Saved</button>
				<button type="button" class="button-primary d3-custom-css-js-publish-btn" style="margin-top: 15px; margin-right: 10px;" disabled="disabled">Save &amp; Publish</button>
				<label style="margin-top: 15px; white-space: nowrap;">
					<input type="checkbox" class="d3-custom-css-js-minify-cb"'.(get_option('d3_custom_'.strtolower($mode).'_minify', true) ? ' checked="checked"' : '').' /> Minify output
				</label>
			</div>
			<div style="clear: both; margin-bottom: 20px;"></div>
	');
	$potent_slug = 'wp-add-custom-css-js';
	include(__DIR__.'/plugin-credit.php');
	echo('
		</div>
	');
}


?>