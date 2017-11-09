<?php
namespace C5dk\Blog;

use Core;
use Package;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkConfig {

	// public $blog_title_editable;
	// public $blog_form_slidein;

	// public $blog_picture_width;
	// public $blog_picture_height;
	// public $blog_thumbnail_width;
	// public $blog_thumbnail_height;
	// public $blog_cropper_def_bgcolor;

	// public $blog_headline_size;
	// public $blog_headline_color;
	// public $blog_headline_margin;
	// public $blog_headline_icon_color;

	public function __construct() {
		$pkg = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();

		// Settings
		$this->blog_title_editable		= $config->get('c5dk_blog.blog_title_editable');
		$this->blog_form_slidein		= $config->get('c5dk_blog.blog_form_slidein');

		// Images & Thumbnails
		$this->blog_picture_width		= $config->get('c5dk_blog.blog_picture_width');
		$this->blog_picture_height		= $config->get('c5dk_blog.blog_picture_height');
		$this->blog_thumbnail_width		= $config->get('c5dk_blog.blog_thumbnail_width');
		$this->blog_thumbnail_height	= $config->get('c5dk_blog.blog_thumbnail_height');
		$this->blog_cropper_def_bgcolor	= $config->get('c5dk_blog.blog_cropper_def_bgcolor');

		// Styling
		$this->blog_headline_size		= $config->get('c5dk_blog.blog_headline_size');
		$this->blog_headline_color		= $config->get('c5dk_blog.blog_headline_color');
		$this->blog_headline_margin		= $config->get('c5dk_blog.blog_headline_margin');
		$this->blog_headline_icon_color	= $config->get('c5dk_blog.blog_headline_icon_color');

		// Editor
		$this->blog_plugin_youtube			= $config->get('c5dk_blog.blog_plugin_youtube');
		$this->blog_plugin_sitemap			= $config->get('c5dk_blog.blog_plugin_sitemap');

		$this->blog_format_h1		= $config->get('c5dk_blog.blog_format_h1');
		$this->blog_format_h2		= $config->get('c5dk_blog.blog_format_h2');
		$this->blog_format_h3		= $config->get('c5dk_blog.blog_format_h3');
		$this->blog_format_h4		= $config->get('c5dk_blog.blog_format_h4');
		$this->blog_format_pre		= $config->get('c5dk_blog.blog_format_pre');
	}

	public function getFormat() {

		$tags = array('p');
		if ($this->blog_format_h1) { $tags[] = 'h1'; }
		if ($this->blog_format_h2) { $tags[] = 'h2'; }
		if ($this->blog_format_h3) { $tags[] = 'h3'; }
		if ($this->blog_format_h4) { $tags[] = 'h4'; }
		if ($this->blog_format_pre) { $tags[] = 'pre'; }

		return implode(";", $tags);
	}

	public function getPlugins() {

		$plugins = array();
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

}