<?php
namespace C5dk\Blog;

use Page;
use Database;
use CollectionAttributeKey;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkRoot extends Page {

	public $rootID				= null;
	public $groups				= array();
	public $pageTypeID			= null;
	public $topicAttributeID	= null;

	public static function getByID($rootID, $version = 'RECENT', $class = 'Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkRoot\C5dkRoot') {

		// Get the C5dkRoot object and add the permissions fields
		$C5dkRoot = parent::getByID($rootID, $version, $class);

		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$db = $app->make('database')->connection();

		// Get this roots values
		$rs = $db->fetchAll("SELECT rootID, groupID, pageTypeID, topicAttributeID FROM C5dkBlogRootPermissions WHERE rootID = ?", array($rootID));
		foreach ($rs as $row) {
			$C5dkRoot->rootID			= $row["rootID"];
			$C5dkRoot->groups[]			= $row["groupID"];
			$C5dkRoot->pageTypeID		= $row["pageTypeID"];
			$C5dkRoot->topicAttributeID	= $C5dkRoot->getTopicAttributeID($row["topicAttributeID"]);
		}

		return $C5dkRoot;

	}

	public function getTopicAttributeID($topicAttributeID) {

		// Is topics used?
		if ($topicAttributeID) {

			// Do the topic tree still exist?
			$topicAttribute = CollectionAttributeKey::getByID($topicAttributeID);
			if ($topicAttribute) {
				return $topicAttributeID;
			} else {
				// Delete the topic from this root
				$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
				$db = $app->make('database')->connection();
				$rs = $db->Execute("UPDATE C5dkBlogRootPermissions set topicAttributeID = ? WHERE rootID = ?", array(0, $rootID));
				return 0;
			}

		}

		return $topicAttributeID;

	}

}