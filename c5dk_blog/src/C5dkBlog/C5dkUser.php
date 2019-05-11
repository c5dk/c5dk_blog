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
use C5dk\Blog\C5dkRoot as C5dkRoot;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkUser extends User
{
	public $isBlogger = FALSE;
	public $isEditor  = FALSE;
	public $isOwner   = FALSE;
	public $isAdmin   = FALSE;

	public $fullName = NULL;

	public $rootList;

	public function __construct()
	{
		// Call Parent function
		parent::__construct();

		// Can the current user blog?
		$this->isBlogger = (count($this->getRootList('writers'))) ? TRUE : FALSE;
		$this->isEditor = (count($this->getRootList('editors'))) ? true : false;

		// Is the current user the owner of the current page
		if (($page = Page::getCurrentPage()) instanceof Page) {
			$C5dkBlog = C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID());
			if ($C5dkBlog->authorID == $this->uID && is_numeric($this->uID)) {
				$this->isOwner = TRUE;
			}
		}

		// Is current user an Administrator
		$this->isAdmin = ($this->isSuperUser() || $this->inGroup(Group::getByName('Administrators'))) ? TRUE : FALSE;

		// Get Full Name if exists
		$ui = UserInfo::getByID($this->getUserID());
		if ($ui instanceof UserInfo) {
			$this->fullName = $ui->getAttribute('full_name');
			if ($this->fullName == '') {
				$this->fullName = $this->getUserName();
			}
		}
	}

	public static function getByUserID($uID, $login = FALSE, $cacheItemsOnLogin = TRUE)
	{
		// Return false if no user id is given
		if (!$uID) {
			return FALSE;
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

		// // Add the roots the user is an editor or writer in
		// $db = Database::get();
		// $rs = $db->GetAll('SELECT * FROM C5dkNewsRootPermissions');
		// foreach ($rs as $row) {

		// 	// Get the writer groups for this root and check if the user is in that group and add it to the result if so.
		// 	$rsWriters = $db->GetAll("SELECT * FROM C5dkNewsRootPermissionsWriters WHERE rootID = ?", array($row['rootID']));
		// 	foreach ($rsWriters as $rowWriters) {
		// 		if ($this->inGroup(Group::getByID($rowWriters["groupID"]))) {
		// 			// Add the root information
		// 			$C5dkRoot = C5dkRoot::getByID($rowWriters["rootID"]);
		// 			if ($C5dkRoot instanceof C5dkRoot && $C5dkRoot->isRoot) {
		// 				$this->rootList["all"][$row["rootID"]] = $C5dkRoot;
		// 				$this->rootList["writers"][$row["rootID"]] = $C5dkRoot;
		// 			} else {
		// 				// TODO: Root do not exist anymore, so we should clean up the db
		// 			}
		// 		}
		// 	}

		// 	// Get the editor groups for this root and check if the user is in that group and add it to the result if so.
		// 	$rsEditors = $db->GetAll("SELECT * FROM C5dkNewsRootPermissionsEditors WHERE rootID = ?", array($row['rootID']));
		// 	foreach ($rsEditors as $rowEditors) {
		// 		if ($this->inGroup(Group::getByID($rowEditors["groupID"]))) {
		// 			// Add the root information
		// 			$C5dkRoot = C5dkRoot::getByID($rowEditors["rootID"]);
		// 			if ($C5dkRoot instanceof C5dkRoot && $C5dkRoot->isRoot) {
		// 				$this->rootList["all"][$row["rootID"]] = $C5dkRoot;
		// 				$this->rootList["editors"][$rowEditors["rootID"]] = $C5dkRoot;
		// 			} else {
		// 				// TODO: Root do not exist anymore, so we should clean up the db
		// 			}
		// 		}
		// 	}
		// }

		// // Return the result depending on the mode
		// switch ($mode) {
		// 	case 'writers':
		// 		return $this->rootList["writers"];
		// 		break;
		// 	case "editors":
		// 		return $this->rootList["editors"];
		// 		break;
		// 	default:
		// 		return $this->rootList;
		// 		break;
		// }

		// return false;
	}

	public function getFilesFromUserSet()
	{
		// Get helper objects
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$im  = $app->make('helper/image');

		$files = [];

		if ($this->isLoggedIn()) {
			// Is $fs a FileSet object or a FileSet handle?
			$fs = FileSet::getByName('C5DK_BLOG_uID-' . $this->getUserID());
			if (is_object($fs)) {
				// Get files from FileSet
				$fl = new FileList();
				$fl->filterBySet($fs);
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
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

		ob_start();
		print View::element('image_manager/list_simple', [
			'C5dkUser' => $this,
			'fileList' => $this->getFilesFromUserSet(),
			'image' => $app->make('helper/image'),
			'canDeleteImages' => TRUE
		], 'c5dk_blog');
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
