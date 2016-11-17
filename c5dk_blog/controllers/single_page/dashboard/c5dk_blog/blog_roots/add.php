<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBLog\BlogRoots;

use Core;
use Database;
use Cookie;
use Concrete\Core\Page\Controller\DashboardPageController;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkRoot\C5dkRoot as C5dkRoot;

defined('C5_EXECUTE') or die("Access Denied.");

class Add extends DashboardPageController {

	public function view() {

		// Set helper object
		$this->set('pageSelector', Core::make('helper/form/page_selector'));

		// Set cookies
		Cookie::set('includeSystemPages', false);
	}

	public function save() {
		$C5dkRoot = C5dkRoot::getByID($this->post('root'));
		$C5dkRoot->setAttribute('c5dk_blog_root', 1);
		$this->redirect('/dashboard/c5dk_blog/blog_roots');
	}

}
