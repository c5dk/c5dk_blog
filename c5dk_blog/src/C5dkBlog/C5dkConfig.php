<?php  
namespace Concrete\Package\C5dkBlog\Src\C5dkBlog;

use Core;
use Package;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkConfig {

	public $blog_thumbnail_width;
	public $blog_thumbnail_height;
	public $blog_picture_width;

	public $blog_headline_size;
	public $blog_headline_color;
	public $blog_headline_margin;
	public $blog_headline_icon_color;

	public function __construct() {
		$pkg = Package::getByHandle('c5dk_blog');

		$config = $pkg->getConfig();
		$this->blog_thumbnail_width			= $config->get('c5dk_blog.blog_thumbnail_width');
		$this->blog_thumbnail_height		= $config->get('c5dk_blog.blog_thumbnail_height');
		$this->blog_picture_width				= $config->get('c5dk_blog.blog_picture_width');

		$this->blog_headline_size				= $config->get('c5dk_blog.blog_headline_size');
		$this->blog_headline_color			= $config->get('c5dk_blog.blog_headline_color');
		$this->blog_headline_margin			= $config->get('c5dk_blog.blog_headline_margin');
		$this->blog_headline_icon_color	= $config->get('c5dk_blog.blog_headline_icon_color');

		$this->blog_title_editable			= $config->get('c5dk_blog.blog_title_editable');
	}
	
}