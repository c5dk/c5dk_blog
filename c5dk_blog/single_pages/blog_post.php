<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
print View::element('blog_post', array(
	'BlogPost' => $BlogPost,
	'C5dkConfig' => $C5dkConfig,
	'C5dkUser' => $C5dkUser,
	'C5dkBlog' => $C5dkBlog,
	'ThumbnailCropper' => $ThumbnailCropper,
	'settings' => $settings,
	'token' => Core::make('token'),
	'jh' => Core::make('helper/json'),
	'form' => Core::make('helper/form')
), 'c5dk_blog');

// print View::element('image_manager/main', array(
// 	'C5dkUser' => new \C5dk\Blog\C5dkUser
// ), 'c5dk_blog');
?>
