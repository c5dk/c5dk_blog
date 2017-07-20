<?php
namespace C5dk\Blog;

use Core;
use User;
use Page;
use View;
use Database;
use Package;
use AssetList;
use CollectionAttributeKey;
use Concrete\Core\Multilingual\Page\Section\Section;

use Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

use File;
use FileList;
use FileImporter;
use FileSet;
use Concrete\Core\Tree\Node\Type\FileFolder		as FileFolder;
use Concrete\Core\Tree\Type\Topic				as TopicTree;
use Concrete\Core\Utility\Service\Identifier	as Identifier;
use Concrete\Core\Html\Service\Navigation		as Navigation;
use Concrete\Core\File\StorageLocation\StorageLocation;

use Concrete\Core\Editor\Plugin;

use Concrete\Core\Page\Controller\PageController;

use C5dk\Blog\C5dkConfig	as C5dkConfig;
use C5dk\Blog\C5dkUser		as C5dkUser;
use C5dk\Blog\C5dkRoot		as C5dkRoot;
use C5dk\Blog\C5dkBlog		as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class BlogPost {

	// Objects
	public $C5dkConfig;
	public $C5dkUser;
	public $C5dkBlog;

	// Variables
	public $blogID = null;
	public $rootList;
	public $topicAttributeID;
	public $topicAttributeIDList;

	// Flags
	public $mode = null;
	public $redirectID = null;

	public function create($redirectID, $rootID = false) {

		// Setup C5DK objects
		$this->C5dkConfig = new C5dkConfig;
		$this->C5dkUser	= new C5dkUser;
		$this->C5dkBlog = new C5dkBlog;

		// Setup Blog object properties
		$this->mode = C5DK_BLOG_MODE_CREATE;
		$this->redirectID = $redirectID;
		$this->rootList = $this->getUserRootList();

		// Set Root ID if set or default to the first root in our list we will show
		$this->C5dkBlog->rootID = (isset($this->rootList[$rootID]))? $rootID : key($this->rootList);

		// Set the topic attribute id from the blogs root
		$C5dkRoot = C5dkRoot::getByID($this->C5dkBlog->rootID);
		$this->topicAttributeID = $C5dkRoot->topicAttributeID;

		// Show tags and thumbnails be shown
		$this->tagsEnabled = $C5dkRoot->tags;
		$this->thumbnailsEnabled = $C5dkRoot->thumbnails;

		return $this;
	}

	public function edit($blogID) {

		// Setup C5DK objects
		$this->C5dkConfig = new C5dkConfig;
		$this->C5dkUser	= new C5dkUser;
		$this->C5dkBlog	= C5dkBlog::getByID($blogID);

		// Setup Blog object properties
		$this->mode = C5DK_BLOG_MODE_EDIT;
		$this->blogID = $blogID;
		$this->redirectID = $blogID;
		$this->rootList = $this->getUserRootList();

		// Set the topic attribute id from the blogs root
		$this->topicAttributeID = C5dkRoot::getByID($this->C5dkBlog->rootID)->topicAttributeID;
		if ($this->C5dkBlog->topics && !$this->topicAttributeID) {
			$this->C5dkBlog->topics = 0;
		}

		return $this;
	}

	private function getUserRootList() {

		$sectionList = Section::getList();

		foreach($this->C5dkUser->getRootList() as $index => $C5dkRoot) {
			$languageText = count($sectionList)? ' ('. $C5dkRoot->getSiteTreeObject()->getLocale()->getLanguageText() . ')' : '';
			$rootList[$C5dkRoot->rootID] = $C5dkRoot->getCollectionName() . $languageText;
		}

		return $rootList;
	}
}
