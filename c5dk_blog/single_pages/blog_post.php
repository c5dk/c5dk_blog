<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
print View::element('image_manager/main', ['C5dkUser' => new \C5dk\Blog\C5dkUser], 'c5dk_blog');

print View::element('blog_post', [
	'BlogPost' => $BlogPost,
	'C5dkConfig' => $C5dkConfig,
	'C5dkUser' => $C5dkUser,
	'C5dkBlog' => $C5dkBlog,
	'ThumbnailCropper' => $ThumbnailCropper,
	'settings' => $settings,
	'token' => Core::make('token'),
	'jh' => Core::make('helper/json'),
	'form' => Core::make('helper/form')
], 'c5dk_blog');
?>
