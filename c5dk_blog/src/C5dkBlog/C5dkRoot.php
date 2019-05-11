<?php
namespace C5dk\Blog;

use Page;
use Database;
use CollectionAttributeKey;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkRoot extends Page
{
	public $entity = null;

	public $rootID           = null;
	public $writerGroups     = [];
	public $editorGroups	 = [];
	public $pageTypeID       = null;
	public $tags             = 1;
	public $thumbnails       = 1;
	public $topicAttributeHandle = null;
	public $publishTimeEnabled = 0;
	public $unpublishTimeEnabled = 0;

	public static function getByID($rootID, $version = 'RECENT', $class = 'C5dk\Blog\C5dkRoot')
	{
		// Get the C5dkRoot object and add the permissions fields
		$C5dkRoot = parent::getByID($rootID, $version, $class);
		$C5dkRoot->entity = C5dkRootEntity::getByRootID($rootID);
// \Log::addEntry(is_null($C5dkRoot->entity)?'yes':'no');
		if (is_object($C5dkRoot)) {
			$C5dkRoot->rootID = $rootID;
			$C5dkRoot->writerGroups = $C5dkRoot->entity->getWriterGroups();
			$C5dkRoot->editorGroups = $C5dkRoot->entity->getEditorGroups();
			$C5dkRoot->pageTypeID = $C5dkRoot->entity->getPageTypeID();
			$C5dkRoot->priorityAttributeHandle = $C5dkRoot->entity->getPriorityAttributeHandle();
			$C5dkRoot->needsApproval = $C5dkRoot->entity->getNeedsApproval();
			$C5dkRoot->tags = $C5dkRoot->entity->getTags();
			$C5dkRoot->thumbnails = $C5dkRoot->entity->getThumbnails();
			$C5dkRoot->topicAttributeHandle = $C5dkRoot->entity->getTopicAttributeHandle();
			$C5dkRoot->publishTimeEnabled = $C5dkRoot->entity->getPublishTimeEnabled();
			$C5dkRoot->unpublishTimeEnabled = $C5dkRoot->entity->getUnpublishTimeEnabled();
		}

		return $C5dkRoot;
	}

	public function getRootID()
	{
		return $this->entity->getRootID();
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
}
