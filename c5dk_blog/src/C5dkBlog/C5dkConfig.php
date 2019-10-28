<?php
namespace C5dk\Blog;

use Package;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkConfig
{
	// public $blog_title_editable;
	// public $blog_form_slidein;

	// public $blog_manager_items_per_page;

	// public $blog_picture_width;
	// public $blog_picture_height;
	// public $blog_thumbnail_width;
	// public $blog_thumbnail_height;
	// public $blog_default_thumbnail_id;
	// public $blog_cropper_def_bgcolor;

	// public $blog_headline_size;
	// public $blog_headline_color;
	// public $blog_headline_margin;
	// public $blog_headline_icon_color;

	public function __construct()
	{
		$pkg    = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();

		// Settings - Other
		$this->blog_title_editable = $config->get('c5dk_blog.blog_title_editable');
		$this->blog_form_slidein   = $config->get('c5dk_blog.blog_form_slidein');

		// Settings - Editor Manager
		$this->blog_manager_items_per_page = $config->get('c5dk_blog.blog_manager_items_per_page');

		// Images & Thumbnails
		$this->blog_picture_width        = $config->get('c5dk_blog.blog_picture_width');
		$this->blog_picture_height       = $config->get('c5dk_blog.blog_picture_height');
		$this->blog_thumbnail_width      = $config->get('c5dk_blog.blog_thumbnail_width');
		$this->blog_thumbnail_height     = $config->get('c5dk_blog.blog_thumbnail_height');
		$this->blog_default_thumbnail_id = $config->get('c5dk_blog.blog_default_thumbnail_id');
		$this->blog_cropper_def_bgcolor  = $config->get('c5dk_blog.blog_cropper_def_bgcolor');

		// Styling
		$this->blog_headline_size       = $config->get('c5dk_blog.blog_headline_size');
		$this->blog_headline_color      = $config->get('c5dk_blog.blog_headline_color');
		$this->blog_headline_margin     = $config->get('c5dk_blog.blog_headline_margin');
		$this->blog_headline_icon_color = $config->get('c5dk_blog.blog_headline_icon_color');

		// Editor
		$this->blog_plugin_youtube	= $config->get('c5dk_blog.blog_plugin_youtube');
		$this->blog_plugin_sitemap	= $config->get('c5dk_blog.blog_plugin_sitemap');
		$this->image_manager_extension	= $config->get('c5dk_blog.image_manager_extension');
		$this->file_manager_extension	= $config->get('c5dk_blog.file_manager_extension');

		$this->blog_format_h1  = $config->get('c5dk_blog.blog_format_h1');
		$this->blog_format_h2  = $config->get('c5dk_blog.blog_format_h2');
		$this->blog_format_h3  = $config->get('c5dk_blog.blog_format_h3');
		$this->blog_format_h4  = $config->get('c5dk_blog.blog_format_h4');
		$this->blog_format_pre = $config->get('c5dk_blog.blog_format_pre');
	}

	public function getFormat()
	{
		$tags = ['p'];
		if ($this->blog_format_h1) {
			$tags[] = 'h1';
		}

		if ($this->blog_format_h2) {
			$tags[] = 'h2';
		}

		if ($this->blog_format_h3) {
			$tags[] = 'h3';
		}

		if ($this->blog_format_h4) {
			$tags[] = 'h4';
		}

		if ($this->blog_format_pre) {
			$tags[] = 'pre';
		}

		return implode(';', $tags);
	}

	public function getPlugins()
	{
		$plugins = [];
		if ($this->blog_plugin_youtube) {
			$plugins[] = 'youtube';
		}

		if ($this->blog_plugin_sitemap) {
			$plugins[] = 'concrete5link';
		}

		if (count($plugins)) {
			return ',' . implode(',', $plugins);
		} else {
			return '';
		}
	}

	public function getExtensions($type, $withFullStop = false, $asArray = false)
	{
		$pkg    = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();

		$configExtensions = $type == "file" ? $config->get('c5dk_blog.file_manager_extension') : $config->get('c5dk_blog.image_manager_extension');
		$configExtensions = explode(',', $configExtensions);
		$configExtensions = array_map('trim', $configExtensions);

		if ($withFullStop) {
			$configExtensions = array_map(function($value) { return '.'.$value; }, $configExtensions);
		}

		if ($asArray) {
			return $configExtensions;
		} else {
			return implode(',', $configExtensions);
		}
	}
}
