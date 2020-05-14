<?php namespace Concrete\Package\C5dkBlog\Controller\SinglePage\C5dk\Blog\Editor;

use Core;
use Database;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\PageList;
use Concrete\Core\Page\Controller\PageController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkRoot as C5dkRoot;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");

class Manager extends PageController
{
	public function view()
	{
		// Set C5DK Objects
		$C5dkUser = new C5dkUser;

		// Do the user have access to this page
		if (!$C5dkUser->isEditor()) {
			$this->redirect('/');
		}

		// Find language path if on a multilingual site
		$c = Page::getCurrentPage();
		$al = Section::getBySectionOfSite($c);
		$langpath = '';
		if (null !== $al) {
			$langpath = $al->getCollectionHandle();
		}

		// Get Editors Root List
		$rootList = $C5dkUser->getRootList("editors");

		// // Get all the Blog entries from every root
		// foreach ($rootList as $rootID => $C5dkRoot) {
		// 	$pl = new PageList;
		// 	$pl->ignorePermissions();
		// 	$pl->filterByParentID($rootID);
		// 	$pl->filterByAttribute('c5dk_blog_author_id', 0, '>');
		// 	$pl->sortByPublicDateDescending();
		// 	foreach ($pl->get() as $page) {
		// 		$entries[$rootID][$page->getCollectionID()] = C5dkBlog::getByID($page->getCollectionID());
		// 	}
		// }

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('javascript', 'c5dkBlog/modal');
		$this->requireAsset('jquery/ui');
		$this->requireAsset('select2');
		// $this->requireAsset('xdan/datetimepicker');
		$this->requireAsset('css', 'datetimepicker/css');
		$this->requireAsset('javascript', 'datetimepicker/plugin');

		// Set Core helper objects
		$this->set('form', Core::make('helper/form'));
		$this->set('dtt', Core::make('helper/form/date_time'));
		$this->set('jh', Core::make('helper/json'));

		// Set our variables/objects
		$this->set('langpath', $langpath);
		$this->set('rootList', $rootList);
		// $this->set('entries', $entries);
	}

	public function root($rootID)
	{
		// Set C5DK Objects
		$C5dkUser	= new C5dkUser;
		$C5dkConfig	= new C5dkConfig;
		$C5dkRoot	= C5dkRoot::getByID($rootID);

		// Find language path if on a multilingual site
		$al = Section::getBySectionOfSite(Page::getCurrentPage());
		$langpath = '';
		if (null !== $al) {
			$langpath = $al->getCollectionHandle();
		}

		// Do the user have access to this page
		if (!$C5dkUser->isEditor()) {
			$this->redirect('/');
		}

		// Get all the Blog entries from every root
		$showPagination = false;
		$itemsPerPage = $C5dkConfig->blog_manager_items_per_page;
		$pl = new PageList();
		$pl->ignorePermissions();
		$pl->filterByParentID($rootID);
		$pl->filterByAttribute('c5dk_blog_author_id', 0, '>');
		$pl->sortByPublicDateDescending();
		$pl->setItemsPerPage($itemsPerPage);
		$pagination = $pl->getPagination();
		foreach ($pagination->getCurrentPageResults() as $page) {
			$pages[$page->getCollectionID()] = C5dkBlog::getByID($page->getCollectionID());
		}
		if ($pagination->haveToPaginate()) {
			$showPagination = true;
			// $pagination = $pagination->renderDefaultView();
			$this->set('pagination', $pagination);
		}

		if ($showPagination) {
			$this->requireAsset('css', 'core/frontend/pagination');
		}
		$this->set('C5dkRoot', $C5dkRoot);
		$this->set('pagination', $pagination);
		$this->set('list', $pl);
		$this->set('showPagination', $showPagination);

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('javascript', 'c5dkBlog/modal');
		$this->requireAsset('jquery/ui');
		$this->requireAsset('select2');
		// $this->requireAsset('xdan/datetimepicker');
		$this->requireAsset('css', 'datetimepicker/css');
		$this->requireAsset('javascript', 'datetimepicker/plugin');

		// Set Core helper objects
		$this->set('form', Core::make('helper/form'));
		$this->set('dtt', Core::make('helper/form/date_time'));
		$this->set('jh', Core::make('helper/json'));

		// Set our variables/objects
		$this->set('langpath', $langpath);
		$this->set('pages', $pages);

		$this->render('c5dk/blog/editor/manager/root');
	}
}
