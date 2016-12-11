<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkBlog extends DashboardPageController {

	public function view(){
		$this->redirect('/dashboard/c5dk_blog/blog_roots');
	}

}
