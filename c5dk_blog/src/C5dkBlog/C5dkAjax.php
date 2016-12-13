<?php
namespace C5dk\Blog;

use Core;
use User;
use Page;
use Controller;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkAjax extends Controller {

	public function blog($method, $blogID) {

		// What should we do?
		switch ($method) {

			case "delete":
				$this->delete($blogID);
				break;

		}
	}

	public function delete($blogID) {

		// Set C5DK Objects
		$C5dkUser = new C5dkUser();
		$C5dkBlog = C5dkBlog::getByID($blogID);

		// Delete the blog if the current user is the owner
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {
			$jh = Core::make('helper/json');
			echo $jh->encode(array(
				'url' => Page::getByID($C5dkBlog->rootID)->getCollectionLink(),
				'result' => $C5dkBlog->moveToTrash()
			));
		}
	}

	public function link($link) {
		$this->redirect($link);
	}

}