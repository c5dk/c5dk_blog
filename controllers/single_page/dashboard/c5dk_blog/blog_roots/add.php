<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBLog\BlogRoots;

use Core;
use Database;
use Cookie;
use Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkRoot as C5dkRoot;

defined('C5_EXECUTE') or die("Access Denied.");

class Add extends DashboardPageController
{

	public function view()
	{
		// Set helper object
		$this->set('pageSelector', $this->app->make('helper/form/page_selector'));

		// Set cookies
		Cookie::set('includeSystemPages', FALSE);
	}

	public function save()
	{
		$C5dkRoot = C5dkRoot::addRoot($this->post('rootID'));
		$this->redirect('/dashboard/c5dk_blog/blog_roots');
	}
}
