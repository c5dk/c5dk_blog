<?php
namespace C5dk\Blog;

use Page;
use Request;
use CollectionAttributeKey;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkRoot extends Page
{
	public $entity = null;

	// public $rootID           = null;
	// public $writerGroups     = [];
	// public $editorGroups	 = [];
	// public $pageTypeID       = null;
	// public $tags             = 1;
	// public $thumbnails       = 1;
	// public $topicAttributeHandle = null;
	// public $publishTime = 0;
	// public $unpublishTime = 0;

	public function __construct()
	{
		parent::__construct();
		$this->entity = new C5dkRootEntity;
	}

	public static function getByID($rootID, $version = 'RECENT', $class = 'C5dk\Blog\C5dkRoot')
	{
		// Get the C5dkRoot object and add the permissions fields
		$C5dkRoot = parent::getByID($rootID, $version, $class);
		$C5dkRoot->entity = C5dkRootEntity::getByRootID($rootID);
		// if (is_object($C5dkRoot)) {
		// 	$C5dkRoot->rootID = $rootID;
		// 	$C5dkRoot->writerGroups = $C5dkRoot->entity->getWriterGroups();
		// 	$C5dkRoot->editorGroups = $C5dkRoot->entity->getEditorGroups();
		// 	$C5dkRoot->pageTypeID = $C5dkRoot->entity->getPageTypeID();
		// 	$C5dkRoot->priorityAttributeHandle = $C5dkRoot->entity->getPriorityAttributeHandle();
		// 	$C5dkRoot->needsApproval = $C5dkRoot->entity->getNeedsApproval();
		// 	$C5dkRoot->tags = $C5dkRoot->entity->getTags();
		// 	$C5dkRoot->thumbnails = $C5dkRoot->entity->getThumbnails();
		// 	$C5dkRoot->topicAttributeHandle = $C5dkRoot->entity->getTopicAttributeHandle();
		// 	$C5dkRoot->publishTimeEnabled = $C5dkRoot->entity->getPublishTime();
		// 	$C5dkRoot->unpublishTimeEnabled = $C5dkRoot->entity->getUnpublishTime();
		// }

		return $C5dkRoot;
	}

	public function getRootID()
	{
		return $this->entity->getRootID();
	}

	public function getNeedsApproval()
	{
		return $this->entity->getNeedsApproval();
	}

	public function getWriterGroups()
	{
		return $this->entity->getWriterGroups();
	}
	public function getWriterGroupsArray()
	{
		$writerGroupsArray = [];
		foreach ($this->entity->getWriterGroups() as $group) {
			$writerGroupsArray[] = $group->getGroupID();
		}
		return $writerGroupsArray;
	}

	public function getEditorGroups()
	{
		return $this->entity->getEditorGroups();
	}
	public function getEditorGroupsArray()
	{
		$editorGroupsArray = [];
		foreach ($this->entity->getEditorGroups() as $group) {
			$editorGroupsArray[] = $group->getGroupID();
		}
		return $editorGroupsArray;
	}

	public function getBlogPageTypeID()
	{
		return $this->entity->getPageTypeID();
	}

	public function getPriorityAttributeHandle()
	{
		return $this->entity->getPriorityAttributeHandle();
	}

	public function getTags()
	{
		return $this->entity->getTags();
	}

	public function getThumbnails()
	{
		return $this->entity->getThumbnails();
	}

	public function getTopicAttributeHandle()
	{
		return $this->entity->getTopicAttributeHandle();
	}

	public function getPublishTime()
	{
		return $this->entity->getPublishTime();
	}

	public function getUnpublishTime()
	{
		return $this->entity->getUnpublishTime();
	}

	public static function addRoot($rootID)
	{
		$C5dkRootEntity = new C5dkRootEntity;
		$C5dkRootEntity->setRootID($rootID);
		$C5dkRootEntity->save();
		$C5dkRoot = self::getByID($rootID);
		$C5dkRoot->setAttribute('c5dk_blog_root', 1);

		return $C5dkRoot;
	}

	public static function removeRoot($rootID)
	{
		$C5dkRoot = self::getByID($rootID);
		$C5dkRoot->entity->delete();
		$ak   = CollectionAttributeKey::getByHandle('c5dk_blog_root');
		$C5dkRoot->clearAttribute($ak);

		return true;
	}

	public static function getPageTypes()
	{
		$pageTypes = [];
		foreach ((new self)->entity->findAll() as $root) {
			$pageTypes[] = $root->getPageTypeID();
		}

		return $pageTypes;
	}
}
