<?php
namespace C5dk\Blog;

use User;
use UserInfo;
use Page;
use Group;
use Database;
use View;
use File;
use FileList;
use FileSet;
use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Application;

use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkRoot as C5dkRoot;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkUser extends User
{
	private $rootList = [];

	public function __construct()
	{
		// Call Parent class
		parent::__construct();
	}

	public static function getByUserID($uID, $login = false, $cacheItemsOnLogin = true)
	{
		// Return false if no user id is given
		if (!$uID) {
			return false;
		}

		$u = parent::getByUserID($uID, $login, $cacheItemsOnLogin);
		if ($u instanceof User) {
			$u->fullName = UserInfo::getByID($uID)->getAttribute('full_name');
			if (!$u->fullName) {
				$u->fullName = $u->getUserName();
			}
		}

		// Return the User object
		return $u;
	}

	public function isBlogger()
	{
		if (is_null($this->isBlogger)) {
			$this->isBlogger = (count($this->getRootList('writers'))) ? true : false;
		}

		return $this->isBlogger;
	}

	public function isEditor()
	{
		if (is_null($this->isEditor)) {
			$this->isEditor = (count($this->getRootList('editors'))) ? true : false;
		}

		return $this->isEditor;
	}

	public function isOwner()
	{
		if (is_null($this->isOwner)) {
			$C5dkBlog = C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID());
			$this->isOwner = $C5dkBlog->getAuthorID() == $this->uID && is_numeric($this->uID) ? true : false;
		}

		return $this->isOwner;
	}

	public function isAdmin()
	{
		if (is_null($this->isAdmin)) {
			$this->isAdmin = $this->isSuperUser() || $this->inGroup(Group::getByID(ADMIN_GROUP_ID)) ? true : false;
		}
		return $this->isAdmin;
	}

	public function getName()
	{
		$this->fullName = t('Not set');

		$ui = UserInfo::getByID($this->getUserID());
		if (is_object($ui)) {
			$this->fullName = $ui->getAttribute('full_name');
			if ($this->fullName == '') {
				$this->fullName = $this->getUserName();
			}
		}

		return $this->fullName;
	}

	public function getRootList($mode = "writers")
	{
		if (!is_array($this->rootList['writers']) && !is_array($this->rootList['editors'])) {
			$this->rootList = [
				'writers' => [],
				'editors' => [],
				'all' => []
			];

			// Add the roots the user can blog in
			$rootSettings = C5dkRootEntity::findAll();
			foreach ($rootSettings as $rootSetting) {
				// Writer roots
				foreach ($rootSetting->getWriterGroups() as $group) {
					if ($this->inGroup(Group::getByID($group->getGroupID()))) {
						// Add the root's information
						$C5dkRoot = C5dkRoot::getByID($rootSetting->getRootID());
						$this->rootList['writers'][$rootSetting->getRootID()] = $C5dkRoot;
						$this->rootList["all"][$rootSetting->getRootID()] = $C5dkRoot;
					}
				}

				// Editor roots
				foreach ($rootSetting->getEditorGroups() as $group) {
					if ($this->inGroup(Group::getByID($group->getGroupID()))) {
						// Add the root's information
						$C5dkRoot = C5dkRoot::getByID($rootSetting->getRootID());
						$this->rootList['editors'][$rootSetting->getRootID()] = $C5dkRoot;
						$this->rootList["all"][$rootSetting->getRootID()] = $C5dkRoot;
					}
				}
			}
		}

		return $this->rootList[$mode];
	}

	public function getRootListForSelect()
	{
		$rootList = [];
		foreach ($this->getRootList() as $root) {
			$rootList[$root->getRootID()] = $root->getCollectionName() . ' (' . $root->getCollectionPath(). ')';
		}

		return $rootList;
	}

	public function getImagesFromUserSet()
	{
		// Get helper objects
		$app = Application::getFacadeApplication();
		$im  = $app->make('helper/image');
		$C5dkConfig = new C5dkConfig;
		
		$files = [];

		if ($this->isLoggedIn()) {
			// Is $fs a FileSet object or a FileSet handle?
			$fs = FileSet::getByName('C5DK_BLOG_uID-' . $this->getUserID());
			if (is_object($fs)) {
				// Get files from FileSet
				$fl = new FileList();
				$fl->filterBySet($fs);
				$fl->filterByExtension($C5dkConfig->getExtensions('image', false, true));
				$fileList = array_reverse($fl->get());
				foreach ($fileList as $key => $file) {
					$f  = File::getByID($file->getFileID());
					$fv = $f->getRecentVersion();
					$fp = explode('_', $fv->getFileName());
					if ($fp[3] != 'Thumb') {
						$files[$key] = [
							'fObj' => $f,
							'fv' => $fv,
							'fID' => $f->getFIleID(),
							'thumbnail' => $im->getThumbnail($f, 150, 150),
							'picture' => [
								'src' => File::getRelativePathFromID($file->getFileID()),
								'width' => $fv->getAttribute('width'),
								'height' => $fv->getAttribute('height')
							]
						];
					}
				};
			}
		}

		return $files;
	}

	public function getImageListHTML()
	{
		$app = Application::getFacadeApplication();

		ob_start();
		print View::element('image_manager/list_simple', [
			'C5dkUser' => $this,
			'imageList' => $this->getImagesFromUserSet(),
			'image' => $app->make('helper/image'),
			'canDeleteImages' => true
		], 'c5dk_blog');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function getFilesFromUserSet()
	{
		// Get helper objects
		$app = Application::getFacadeApplication();
		$C5dkConfig = new C5dkConfig;

		$files = [];

		if ($this->isLoggedIn()) {
			// Is $fs a FileSet object or a FileSet handle?
			$fs = FileSet::getByName('C5DK_BLOG_uID-' . $this->getUserID());
			if (is_object($fs)) {
				// Get files from FileSet
				$fl = new FileList();
				$fl->filterBySet($fs);
				$fl->filterByExtension($C5dkConfig->getExtensions('file', false, true));
				$fileList = array_reverse($fl->get());
				foreach ($fileList as $key => $file) {
					$f  = File::getByID($file->getFileID());
					$fv = $f->getRecentVersion();
					$files[$key] = [
						'fObj' => $f,
						'fv' => $fv,
						'fID' => $f->getFIleID(),
						'fName' => $fv->getFileName(),
						'fHref' => $fv->getDownloadURL()
					];
				};
			}
		}

		return $files;
	}

	public function getFileListHTML()
	{
		ob_start();
		print View::element('file_manager/list_simple', [
			'C5dkUser' => $this,
			'fileList' => $this->getFilesFromUserSet(),
			'canDeleteFiles' => true
		], 'c5dk_blog');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Is the user an editor of the passed C5dkBlog or C5dkRoot page
	 *
	 * Returns true/false depending on the groups the user is a member of
	 *
	 * @param Type C5dkBlog | C5dkRoot
	 * @return Boolean
	 **/
	public function isEditorOfPage($C5dkBlog)
	{
		if ($C5dkBlog instanceof C5dkBlog || $C5dkBlog instanceof C5dkRoot) {
			$C5dkRoot = $C5dkBlog instanceof C5dkRoot ? $C5dkBlog : $C5dkBlog->getRoot();

			foreach ($C5dkRoot->getEditorGroups() as $entity) {
				if ($this->inGroup($entity->getGroup())) {
					return true;
				}
			}
		}

		return false;
	}
}
