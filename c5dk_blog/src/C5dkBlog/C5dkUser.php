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

use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkRoot as C5dkRoot;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkUser extends User {

	public $isBlogger = false;
	public $isOwner = false;
	public $isAdmin = false;

	public $fullName = null;

	public $rootList = array();

	public function __construct() {

		// Call Parent function
		parent::__construct();

		// Can the current user blog?
		$this->isBlogger = (count($this->getRootList()))? true : false;

		// Is the current user the owner of the current page
		if (($page = Page::getCurrentPage()) instanceof Page) {
			$C5dkBlog = C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID());
			if ($C5dkBlog->authorID == $this->uID && is_numeric($this->uID)) {
				$this->isOwner = true;
			}
		}

		// Get Full Name if exists
		$ui = UserInfo::getByID($this->getUserID());
		if ($ui instanceof UserInfo){
			$this->fullName = $ui->getAttribute("full_name");
			if ($this->fullName == "") { $this->fullName = $this->getUserName(); }
		}

		// Is current user an Administrator
		$this->isAdmin = ($this->superUser || $this->inGroup(Group::getByName("Administrators")))? true : false;
	}

	public static function getByUserID($uID, $login = false, $cacheItemsOnLogin = true) {

		// Return false if no user id is given
		if (!$uID){
			return false;
		}

		$u = parent::getByUserID($uID, $login, $cacheItemsOnLogin);
		If ($u instanceof User) {
			$u->fullName = UserInfo::getByID($uID)->getAttribute('full_name');
			if (!$u->fullName) { $u->fullName = $u->getUserName(); }
		}

		// Return the User object
		return $u;
	}

	public function getRootList() {

		// Add the roots the user can blog in
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$db = $app->make('database')->connection();
		$rs = $db->executeQuery('SELECT * FROM C5dkBlogRootPermissions');
		while ($row = $rs->fetchRow()) {
			if ($this->inGroup(Group::getByID($row["groupID"]))) {
				// Add the root's information
				$this->rootList[$row["rootID"]] = C5dkRoot::getByID($row["rootID"]);
			}
		}

		return $this->rootList;
	}

	public function getFilesFromUserSet() {

		// Get helper objects
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$im = $app->make('helper/image');

		$files = array();

		if($this->isLoggedIn()){


			// Is $fs a FileSet object or a FileSet handle?
			$fs = FileSet::getByName("C5DK_BLOG_uID-" . $this->getUserID());
			if (is_object($fs)) {


				// Get files from FileSet
				$fl = new FileList();
				$fl->filterBySet($fs);

				foreach ($fl->get() as $key => $file) {
					$f = File::getByID($file->getFileID());
					$fv = $f->getRecentVersion();
					$fp = explode("_", $fv->getFileName());
					if ($fp[3] != "Thumb") {
						$files[$key] = array(
							'fObj' => $f,
							'fv' => $fv,
							"fID" => $f->getFIleID(),
							"thumbnail" => $im->getThumbnail($f, 150, 150),
							"picture"		=> array(
								"src"		=> File::getRelativePathFromID($file->getFileID()),
								"width"		=> $fv->getAttribute("width"),
								"height"	=> $fv->getAttribute("height")
							)
						);
					}

				};
			}

		}

		return $files;
	}

	public function getImageListHTML() {

		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

		ob_start();
		print View::element('image_manager/list_simple', array(
			'C5dkUser'			=> $this,
			'fileList'			=> $this->getFilesFromUserSet(),
			'image'				=> $app->make('helper/image'),
			'canDeleteImages'	=> false
		), 'c5dk_blog');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

}
