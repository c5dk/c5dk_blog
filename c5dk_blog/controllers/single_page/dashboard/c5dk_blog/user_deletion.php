<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use Core;
use Package;
use UserInfo;
Use PageList;
use Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");

class UserDeletion extends DashboardPageController {

	public function view($uID){

		// Set helpers
		$this->set('form', $this->app->make('helper/form'));

		$this->set('uID', $uID);
	}

	public function transfer($uID) {
		$tID = $this->post('tID');

		$list = $this->getList($uID);
		if (count($list)) {
			foreach ($list as $page) {
				$page->setAttribute('c5dk_blog_author_id', $tID);
			}
		}

		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$ui  = $app->make(\Concrete\Core\User\UserInfoFactory::class)->getByID($uID);
		$ui->delete();

		$this->redirect('/dashboard/users');

	}

	public function delete($uID) {

		$list = $this->getList($uID);
		if (count($list)) {
			foreach ($list as $page) {
				$page->moveToTrash();
			}
		}

		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$ui  = $app->make(\Concrete\Core\User\UserInfoFactory::class)->getByID($uID);
		$ui->delete();

		$this->redirect('/dashboard/users');

	}

	public function cancel() {

		$this->redirect('/dashboard/users');

	}

	public function getList($uID) {

		$pl = new PageList;
		$pl->filterByC5dkBlogAuthorId($uID);

		return $pl->getResults();
	}

}
