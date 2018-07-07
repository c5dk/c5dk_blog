<?php

namespace C5dk\Blog;

use Controller;
use Concrete\Core\Multilingual\Page\Section\Section;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogPost extends Controller
{
	// Objects
	public $C5dkConfig;
	public $C5dkUser;
	public $C5dkBlog;

	// Variables
	public $blogID = NULL;
	public $rootList;
	public $topicAttributeID;
	public $topicAttributeIDList;

	// Flags
	public $mode       = NULL;
	public $redirectID = NULL;

	public function create($redirectID, $rootID = FALSE)
	{
		// Setup C5DK objects
		$this->C5dkConfig = new C5dkConfig;
		$this->C5dkUser   = new C5dkUser;
		$this->C5dkBlog   = new C5dkBlog;

		// Check if user can blog?
		if (!$this->C5dkUser->isBlogger) {
			$this->redirect('/');
		}

		// Setup Blog object properties
		$this->mode       = C5DK_BLOG_MODE_CREATE;
		$this->redirectID = $redirectID;
		$this->rootList   = $this->getUserRootList();

		// Set Root ID if set or default to the first root in our list we will show
		$this->C5dkBlog->rootID = (isset($this->rootList[$rootID])) ? $rootID : key($this->rootList);

		// Set the topic attribute id from the blogs root
		$C5dkRoot               = C5dkRoot::getByID($this->C5dkBlog->rootID);
		$this->topicAttributeID = $C5dkRoot->topicAttributeID;

		// Should tags and thumbnails be shown
		$this->tagsEnabled       = $C5dkRoot->tags;
		$this->thumbnailsEnabled = $C5dkRoot->thumbnails;

		return $this;
	}

	public function edit($blogID)
	{
		// Setup C5DK objects
		$this->C5dkConfig = new C5dkConfig;
		$this->C5dkUser   = new C5dkUser;
		$this->C5dkBlog   = C5dkBlog::getByID($blogID);

		// Check if user is owner of blog?
		if ($this->C5dkBlog->authorID && $this->C5dkBlog->authorID == $this->C5dkUser->getUserID()) {
			// Setup Blog object properties
			$this->mode       = C5DK_BLOG_MODE_EDIT;
			$this->blogID     = $blogID;
			$this->redirectID = $blogID;
			$this->rootList   = $this->getUserRootList();

			// Set the topic attribute id from the blogs root
			$this->topicAttributeID = C5dkRoot::getByID($this->C5dkBlog->rootID)->topicAttributeID;
			if ($this->C5dkBlog->topics && !$this->topicAttributeID) {
				$this->C5dkBlog->topics = 0;
			}

			// Should tags and thumbnails be shown
			$C5dkRoot                = C5dkRoot::getByID($this->C5dkBlog->rootID);
			$this->tagsEnabled       = $C5dkRoot->tags;
			$this->thumbnailsEnabled = $C5dkRoot->thumbnails;

			return $this;
		}

		$this->redirect('/');
	}

	private function getUserRootList()
	{
		$sectionList = Section::getList();

		foreach ($this->C5dkUser->getRootList() as $index => $C5dkRoot) {
			$languageText                = count($sectionList) ? ' (' . $C5dkRoot->getSiteTreeObject()->getLocale()->getLanguageText() . ')' : '';
			$rootList[$C5dkRoot->rootID] = $C5dkRoot->getCollectionName() . $languageText;
		}

		return $rootList;
	}
}
