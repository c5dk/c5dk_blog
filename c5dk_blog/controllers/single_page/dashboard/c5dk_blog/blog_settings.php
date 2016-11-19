<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use Core;
use Package;
use Database;
use \Concrete\Core\Page\Controller\DashboardPageController;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");
class BlogSettings extends DashboardPageController {

	public function view(){
		// Set the C5dk object
		$C5dkConfig = new C5dkConfig;
		$this->set('C5dkConfig',	$C5dkConfig);

		// Set helpers
		$this->set('form', Core::make('helper/form'));
	}

	public function save() {
		$pkg = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();
		$config->save('c5dk_blog.blog_thumbnail_width',		$this->post('blog_thumbnail_width'));
		$config->save('c5dk_blog.blog_thumbnail_height',	$this->post('blog_thumbnail_height'));
		$config->save('c5dk_blog.blog_picture_width',			$this->post('blog_picture_width'));

		$config->save('c5dk_blog.blog_headline_size',				$this->post('blog_headline_size'));
		$config->save('c5dk_blog.blog_headline_color',			$this->post('blog_headline_color'));
		$config->save('c5dk_blog.blog_headline_margin',			$this->post('blog_headline_margin'));
		$config->save('c5dk_blog.blog_headline_icon_color',	$this->post('blog_headline_icon_color'));

		$config->save('c5dk_blog.blog_title_editable', ($this->post('blog_title_editable'))? $this->post('blog_title_editable') : 0);

		$this->set('message', t('Settings saved.'));

		$this->view();

	}

}