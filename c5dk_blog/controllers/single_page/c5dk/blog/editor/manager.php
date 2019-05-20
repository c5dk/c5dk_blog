<?php namespace Concrete\Package\C5dkBlog\Controller\SinglePage\C5dk\Blog\Editor;

use Core;
use Database;

use Concrete\Core\Page\PageList;
use Concrete\Core\Page\Controller\PageController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkRoot as C5dkRoot;
use C5dk\Blog\C5dkBlog as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class Manager extends PageController
{
	public function view()
	{
		// Set C5DK Objects
		$C5dkUser = new C5dkUser;

		// Do the user have access to this page
		if (!$C5dkUser->isEditor) {
			$this->redirect('/');
		}

		// Get Editors Root List
		$rootList = $C5dkUser->getRootList("editors");

		// Get all the Blog entries from every root
		foreach ($rootList as $rootID => $C5dkRoot) {
			$pl = new PageList;
			$pl->ignorePermissions();
			$pl->filterByParentID($rootID);
			$pl->filterByAttribute('c5dk_blog_author_id', 0, '>');
			foreach (array_reverse($pl->get()) as $page) {
				$entries[$rootID][$page->getCollectionID()] = C5dkBlog::getByID($page->getCollectionID());
			}
		}

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('javascript', 'c5dkBlog/modal');
		$this->requireAsset('jquery/ui');
		$this->requireAsset('select2');
		$this->requireAsset('xdan/datetimepicker');

		// Set Core helper objects
		$this->set('form', Core::make('helper/form'));
		$this->set('dtt', Core::make('helper/form/date_time'));
		$this->set('jh', Core::make('helper/json'));

		// Set our variables/objects
		$this->set('rootList', $rootList);
		$this->set('entries', $entries);
	}

	public function convertValueObject($valueObject)
	{

		$db = Database::get();

		$avID = $valueObject->avID;

		$rs = $db->GetAll("SELECT * FROM atSelectedTopics WHERE avID = ?", [$avID]);
		foreach ($rs as $row) {
			$priorityList[] = $row["TopicNodeID"];
		}
		return $priorityList;
	}

	public function getPrioritiesJSON($priorities) {
		$jh = Core::make('helper/json');
		return h($jh->encode($priorities), ENT_QUOTES, 'UTF-8');
		// $first = 0;
		// $str = '{';
		// foreach ($priorities as $text) {
		// 	if ($first++) { $str .= ', '; }
		// 	$str .= $text;
		// }
		// $str .= '}';
		// return $str;
	}
}
