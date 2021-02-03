<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
print View::element('image_manager/main', ['C5dkConfig' => $C5dkConfig, 'C5dkUser' => $C5dkUser], 'c5dk_blog');
print View::element('file_manager/main', ['C5dkConfig' => $C5dkConfig, 'C5dkUser' => $C5dkUser], 'c5dk_blog');

print View::element('blog_post', [
	'langpath' => $langpath,
	'C5dkConfig' => $C5dkConfig,
	'C5dkUser' => $C5dkUser,
	'C5dkEditor' => $C5dkEditor,
	'C5dkBlog' => $C5dkBlog,
	'C5dkRoot' => $C5dkRoot,
	'redirectID' => $redirectID,
	'ThumbnailCropper' => $ThumbnailCropper,
	'settings' => $settings,
	'token' => Core::make('token'),
	'jh' => Core::make('helper/json'),
	'form' => Core::make('helper/form')
], 'c5dk_blog');
