<?php namespace Concrete\Package\C5dkBlog\Controller\SinglePage;

// use Core;
use View;
// use CollectionAttributeKey;
use File;
// use FileImporter;
// use FileSet;
// use Image;
// use Imagine\Image\Box;
// use Concrete\Core\Tree\Node\Type\FileFolder	as FileFolder;
use Concrete\Core\Page\Page;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Controller\PageController;

use C5dk\Blog\C5dkConfig;
use C5dk\Blog\C5dkUser;
use C5dk\Blog\C5dkAjax;
use C5dk\Blog\C5dkBlog;
use C5dk\Blog\C5dkRoot;
use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogPost extends PageController
{
	public function view()
	{
		// Direct access is not allowed.
		$this->redirect('/');
	}

	public function create($blogID, $rootID, $redirectID = null)
	{
		// Setup C5DK objects
		$C5dkConfig = new C5dkConfig;
		$C5dkUser   = new C5dkUser;
		$C5dkBlog   = new C5dkBlog;

		// Check if user can blog?
		if (!$C5dkUser->isBlogger()) {
			$this->redirect('/');
		}

		// Find the root we will set as standard root.
		if (!$rootID) {
			$rootID = $C5dkBlog->getRootID();
			$rootList = $C5dkUser->getRootList();
			$rootID = (isset($rootList[$rootID])) ? $rootID : key($rootList);
		}
		$C5dkRoot   = C5dkRoot::getByID($rootID);

		$this->init($C5dkBlog, $C5dkRoot, $C5dkConfig, $C5dkUser, $redirectID);
	}

	public function edit($blogID, $rootID, $editor = false)
	{
		// Setup C5DK objects
		$C5dkBlog   = C5dkBlog::getByID($blogID);
		$C5dkRoot   = C5DKRoot::getByID($rootID);
		$C5dkConfig = new C5dkConfig;
		$C5dkUser   = new C5dkUser;
		if ($editor) {
			$C5dkEditor = $C5dkUser;
			if ($C5dkEditor->isEditor()) {
				$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
			}
		}

		// Check if user is owner of blog?
		if ($C5dkBlog->getAuthorID() && $C5dkBlog->getAuthorID() != $C5dkUser->getUserID()) {
			$this->redirect('/');
		}

		$this->init($C5dkBlog, $C5dkRoot, $C5dkConfig, $C5dkUser);
	}

	public function init($C5dkBlog, $C5dkRoot, $C5dkConfig, $C5dkUser, $redirectID = null, $C5dkEditor = null)
	{
		// Find language path if on a multilingual site
		$c = Page::getCurrentPage();
		$al = Section::getBySectionOfSite($c);
		$langpath = '';
		if (null !== $al) {
			$langpath = $al->getCollectionHandle();
		}

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('javascript', 'c5dkBlog/main');
		$this->requireAsset('javascript', 'c5dkBlog/modal');
		$this->requireAsset('javascript', 'c5dkckeditor');
		$this->requireAsset('core/topics');
		$this->requireAsset('core/app');

		$this->requireAsset('javascript', 'cropper');
		$this->requireAsset('css', 'cropper');
		$this->requireAsset('javascript', 'validation');
		$this->requireAsset('javascript', 'slide-in-panel/main');
		$this->requireAsset('javascript', 'character-counter/main');
		$this->requireAsset('c5dkFileupload/all');
		$this->requireAsset('xdan/datetimepicker');

		// Set View variables
		$this->set('view', new View);
		$this->set('langpath', $langpath);
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('C5dkUser', $C5dkUser);
		$this->set('C5dkEditor', $C5dkEditor);
		$this->set('C5dkBlog', $C5dkBlog);
		$this->set('C5dkRoot', $C5dkRoot);
		$this->set('redirectID', $redirectID);
		$defaultThumbnailID = $C5dkConfig->blog_default_thumbnail_id;
		$defThumbnail       = $defaultThumbnailID ? File::getByID($defaultThumbnailID) : null;
		$Cropper            = new ThumbnailCropper($C5dkBlog->getThumbnail(), $defThumbnail);
		$Cropper->setOnSelectCallback("c5dk.blog.post.image.showManager('thumbnail')");
		$Cropper->setOnSaveCallback('c5dk.blog.post.blog.save');
		$this->set('ThumbnailCropper', $Cropper);
	}

	public function save($blogID)
	{
		$C5dkBlog = $blogID ? C5dkBlog::getByID($blogID) : new C5dkBlog;
		$C5dkBlog = $C5dkBlog->save($blogID);

		$C5dkUser = new C5dkUser;

		$C5dkAjax = new C5dkAjax;
		$C5dkAjax->saveThumbnail($C5dkBlog, $C5dkUser, $this->post('thumbnail'));

		$this->redirect($C5dkBlog->getCollectionLink());
	}

	// Keep the active login session active
	public function ping()
	{
		$C5dkUser = new C5dkUser;
		$status   = ($C5dkUser->isLoggedIn()) ? true : false;
		$data     = [
			'post' => $this->post(),
			'status' => $status
		];

		$jh = $this->app->make('helper/json');
		echo $jh->encode($data);

		exit;
	}

	// private function getUserRootList()
	// {
	// 	$sectionList = Section::getList();

	// 	foreach ($this->C5dkUser->getRootList('writers') as $rootID => $C5dkRoot) {
	// 		$languageText = count($sectionList) ? ' (' . $C5dkRoot->getSiteTreeObject()->getLocale()->getLanguageText() . ')' : '';
	// 		$rootList[$rootID] = $C5dkRoot->getCollectionName() . $languageText;
	// 	}

	// 	return $rootList;
	// }
}
