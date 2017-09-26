<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use Core;
use Package;
use Database;
use \Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");
class BlogSettings extends DashboardPageController {

	public function view(){
		// Set the C5dk object
		$C5dkConfig = new C5dkConfig;
		$this->set('C5dkConfig',	$C5dkConfig);

		// Require Assets
		$this->requireAsset('core/app');

		// Set helpers
		$this->set('form', $this->app->make('helper/form'));
		$this->set('colorPicker', $this->app->make('helper/form/color'));
	}

	public function save() {
		$pkg = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();

		// Settings
		$config->save('c5dk_blog.blog_title_editable',	($this->post('blog_title_editable'))? $this->post('blog_title_editable') : 0);
		$config->save('c5dk_blog.blog_form_slidein',	($this->post('blog_form_slidein'))? $this->post('blog_form_slidein') : 0);

		// Images & Thumbnails
		$config->save('c5dk_blog.blog_picture_width',		$this->post('blog_picture_width'));
		$config->save('c5dk_blog.blog_picture_height',		$this->post('blog_picture_height'));
		$config->save('c5dk_blog.blog_thumbnail_width',		$this->post('blog_thumbnail_width'));
		$config->save('c5dk_blog.blog_thumbnail_height',	$this->post('blog_thumbnail_height'));
		$config->save('c5dk_blog.blog_cropper_def_bgcolor',	$this->post('blog_cropper_def_bgcolor'));

		// Styling
		$config->save('c5dk_blog.blog_headline_size',		$this->post('blog_headline_size'));
		$config->save('c5dk_blog.blog_headline_color',		$this->post('blog_headline_color'));
		$config->save('c5dk_blog.blog_headline_margin',		$this->post('blog_headline_margin'));
		$config->save('c5dk_blog.blog_headline_icon_color',	$this->post('blog_headline_icon_color'));

		// Editor
		$config->save('c5dk_blog.blog_plugin_youtube',	$this->post('blog_plugin_youtube'));
		$config->save('c5dk_blog.blog_format_h1',		$this->post('blog_format_h1'));
		$config->save('c5dk_blog.blog_format_h2',		$this->post('blog_format_h2'));
		$config->save('c5dk_blog.blog_format_h3',		$this->post('blog_format_h3'));
		$config->save('c5dk_blog.blog_format_h4',		$this->post('blog_format_h4'));
		$config->save('c5dk_blog.blog_format_pre',		$this->post('blog_format_pre'));

		$this->set('message', t('Settings saved.'));

		$this->view();

	}

}
