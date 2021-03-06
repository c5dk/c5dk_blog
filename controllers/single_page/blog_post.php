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
use GuzzleHttp\RedirectMiddleware;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogPost extends PageController
{
	public function view()
	{
		// Direct access is not allowed.
		$this->redirect('/');
	}

	public function create($blogID, $rootID, $redirectID)
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

	public function edit($blogID, $rootID, $redirectID)
	{
		// Setup C5DK objects
		$C5dkBlog   = C5dkBlog::getByID($blogID);
		$C5dkRoot   = C5DKRoot::getByID($rootID);
		$C5dkConfig = new C5dkConfig;
		$C5dkUser   = new C5dkUser;
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
		}

		// Check if user is owner of blog?
		if ($C5dkBlog->getAuthorID() && $C5dkBlog->getAuthorID() != $C5dkUser->getUserID()) {
			$this->redirect('/');
		}

		$this->init($C5dkBlog, $C5dkRoot, $C5dkConfig, $C5dkUser, $redirectID);
	}

	public function init($C5dkBlog, $C5dkRoot, $C5dkConfig, $C5dkUser, $redirectID)
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
		//$this->requireAsset('xdan/datetimepicker');
		$this->requireAsset('css', 'datetimepicker/css');
		$this->requireAsset('javascript', 'datetimepicker/plugin');
		//$this->requireAsset('c5dkFileupload/all');
		$this->requireAsset('javascript', 'c5dkFileupload/loadImage');
		$this->requireAsset('javascript', 'c5dkFileupload/canvastoblob');
		$this->requireAsset('javascript', 'c5dkFileupload/iframeTransport');
		$this->requireAsset('javascript', 'c5dkFileupload/fileupload');
		$this->requireAsset('javascript', 'c5dkFileupload/fileuploadProcess');
		$this->requireAsset('javascript', 'c5dkFileupload/fileuploadImage');

		// Set View variables
		$this->set('view', new View);
		$this->set('langpath', $langpath);
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('C5dkUser', $C5dkUser);
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
		$C5dkAjax = new C5dkAjax;

		if ($blogID) {
			// Edit
			$C5dkBlog = C5dkBlog::getByID($blogID);
			$C5dkRoot = $C5dkBlog->getRoot();
			$C5dkUser = new C5dkUser;
			if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkRoot)) {
				$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
			}
			$C5dkBlog = $C5dkBlog->save($blogID);
			$C5dkAjax->saveThumbnail($C5dkBlog, $C5dkUser, $this->post('thumbnail'));
		} else {
			// Create
			$C5dkBlog =  new C5dkBlog;
			$C5dkRoot = C5dkRoot::getByID($this->post('rootID'));
			$C5dkUser = new C5dkUser;
			if ($C5dkUser->isBlogger()) {
				$C5dkBlog = $C5dkBlog->save($blogID);
				$C5dkAjax->saveThumbnail($C5dkBlog, $C5dkUser, $this->post('thumbnail'));
			}
		}

		$this->redirect($C5dkBlog->getCollectionLink());
	}

	// --- Ajax calls from this point. Done to not use routes ---

	// Keep the active login session active
	public function ping()
	{
		$ajax = new C5dkAjax;
		$ajax->ping();
	}

	public function approve($blogID)
	{
		$ajax = new C5dkAjax;
		$ajax->approve($blogID);

		exit;
	}

	public function unapprove($blogID)
	{
		$ajax = new C5dkAjax;
		$ajax->unapprove($blogID);

		exit;
	}

	public function getForm($blogID, $rootID, $redirectID)
	{
		$ajax = new C5dkAjax;
		$ajax->getForm($blogID, $rootID, $redirectID);

		exit;
	}

	public function getManagerSlideIns($blogID)
	{
		$ajax = new C5dkAjax;
		$ajax->getManagerSlideIns($blogID);

		exit;
	}

	public function delete($blogID)
	{
		$ajax = new C5dkAjax;
		$ajax->delete($blogID);

		exit;
	}

	public function publish()
	{
		$ajax = new C5dkAjax;
		$ajax->publish();

		exit;
	}

	public function image($type)
	{
		$ajax = new C5dkAjax;
		if ($type == "upload") {
			$ajax->imageUpload();
		}

		if ($type == "delete") {
			$ajax->imageDelete();
		}

		exit;
	}

	public function imagedelete()
	{
		$ajax = new C5dkAjax;
		$ajax->fileUpload();

		exit;
	}

	public function fileupload()
	{
		$ajax = new C5dkAjax;
		$ajax->fileUpload();

		exit;
	}

	public function filedelete()
	{
		$ajax = new C5dkAjax;
		$ajax->fileDelete();

		exit;
	}

	public function editor($method, $field, $blogID)
	{
		$ajax = new C5dkAjax;
		$ajax->editor($method, $field, $blogID);

		exit;
	}
}
