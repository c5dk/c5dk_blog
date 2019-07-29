<?php
namespace C5dk\Blog\Entity;

use Database;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use C5dk\Blog\Entity\C5dkRootEditorGroup as C5dkRootEditorGroup;
use C5dk\Blog\Entity\C5dkRootWriterGroup as C5dkRootWriterGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="C5dkRoot", uniqueConstraints={@ORM\UniqueConstraint(name="search_idx", columns={"rootID"})})
 */
class C5dkRoot
{
	/**
	 * @ORM\Id @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $rootID;
	/** @ORM\Column(type="integer") */
	protected $pageTypeID = 0;
	/** @ORM\Column(type="text") */
	protected $priorityAttributeHandle = "";
	/** @ORM\Column(type="boolean") */
	protected $needsApproval = 0;
	/** @ORM\Column(type="boolean") */
	protected $tags = 0;
	/** @ORM\Column(type="boolean") */
	protected $thumbnails = 0;
	/** @ORM\Column(type="text") */
	protected $topicAttributeHandle = "";
	/** @ORM\Column(type="boolean") */
	protected $publishTime = 0;
	/** @ORM\Column(type="boolean") */
	protected $unpublishTime = 0;

	/**
	 * @ORM\OneToMany(targetEntity="C5dk\Blog\Entity\C5dkRootWriterGroup", mappedBy="root", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $writerGroups;
	/**
	 * @ORM\OneToMany(targetEntity="C5dk\Blog\Entity\C5dkRootEditorGroup", mappedBy="root", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $editorGroups;

	public function __construct()
	{
		$this->writerGroups = new ArrayCollection();
		$this->editorGroups = new ArrayCollection();
	}

	// ID get function
	public function getID()
	{
		return $this->id;
	}

	// RootSetting get/set functions
	public function setRootID($rootID)
	{
		$this->rootID = $rootID;
	}
	public function getRootID()
	{
		return $this->rootID;
	}
	public function setPageTypeID($pageTypeID)
	{
		$this->pageTypeID = $pageTypeID;
	}
	public function getPageTypeID()
	{
		return $this->pageTypeID;
	}
	public function setPriorityAttributeHandle($priorityAttributeHandle)
	{
		$this->priorityAttributeHandle = $priorityAttributeHandle;
	}
	public function getPriorityAttributeHandle()
	{
		return $this->priorityAttributeHandle;
	}
	public function setNeedsApproval($needsApproval)
	{
		$this->needsApproval = $needsApproval;
	}
	public function getNeedsApproval()
	{
		return $this->needsApproval;
	}
	public function setTags($tags)
	{
		$this->tags = $tags;
	}
	public function getTags()
	{
		return $this->tags;
	}
	public function setThumbnails($thumbnails)
	{
		$this->thumbnails = $thumbnails;
	}
	public function getThumbnails()
	{
		return $this->thumbnails;
	}
	public function setTopicAttributeHandle($topicAttributeHandle)
	{
		$this->topicAttributeHandle = $topicAttributeHandle;
	}
	public function getTopicAttributeHandle()
	{
		return $this->topicAttributeHandle;
	}
	public function setPublishTime($publishTime)
	{
		$this->publishTime = $publishTime;
	}
	public function getPublishTime()
	{
		return $this->publishTime;
	}
	public function setUnpublishTime($unpublishTime)
	{
		$this->unpublishTime = $unpublishTime;
	}
	public function getUnpublishTime()
	{
		return $this->unpublishTime;
	}

	public function getWriterGroups()
	{
		return $this->writerGroups;
	}
	public function getEditorGroups()
	{
		return $this->editorGroups;
	}

	public static function findBy($criteria = [], $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
	{
		$db = Database::connection();
		$em = $db->getEntityManager();

		return $em->getRepository(get_class())->findBy($criteria, $orderBy, $limit, $offset);
	}

	public static function getByID($id)
	{
		$db = Database::connection();
		$em = $db->getEntityManager();

		return $em->find(get_class(), $id);
	}

	public static function getByRootID($rootID)
	{
		$db = Database::connection();
		$em = $db->getEntityManager();

		return $em->getRepository(get_class())->findOneBy(['rootID' => $rootID]);
	}

	public static function findAll()
	{
		$db = Database::connection();
		$em = $db->getEntityManager();

		return $em->getRepository(get_class())->findAll();
	}

	public static function saveForm($request)
	{
		if ($request['id']) {
			$root = self::getByID($request['id']);
		} elseif ($request['rootID']) {
			$root = self::getByRootID($request['rootID']);
		}

		if (!$root) {
			$root = new self();
		}

		$root->setRootID($request['rootID']);
		$root->setPageTypeID($request['pageTypeID']);
		$root->setPriorityAttributeHandle($request['priorityAttributeHandle']);
		$root->setNeedsApproval(isset($request['needsApproval']) ? 1 : 0);
		$root->setTags(isset($request['tags']) ? 1 : 0);
		$root->setThumbnails(isset($request['thumbnails']) ? 1 : 0);
		$root->setTopicAttributeHandle($request['topicAttributeHandle']);
		$root->setPublishTime(isset($request['publishTime']));
		$root->setUnpublishTime(isset($request['unpublishTime']));
		$root->save();

		if (count($request['editorGroups'])) {
			self::setEditorGroups($root, count($request['editorGroups']) ? $request['editorGroups'] : []);
		}
		if (count($request['writerGroups'])) {
			self::setWriterGroups($root, count($request['writerGroups']) ? $request['writerGroups'] : []);
		}

		return $root;
	}

	public static function setEditorGroups($root, $groups)
	{
		$currentGroups = [];

		$editorGroups = $root->getEditorGroups();
		foreach ($editorGroups as $editorGroup) {
			if (is_object($editorGroup)) {
				$currentGroups[] = $editorGroup->getGroupID();
			} else {
				$currentGroups[] = $editorGroup;
			}
		}
		$groupsToRemove = array_diff($currentGroups, $groups);
		$toRemove = C5dkRootEditorGroup::findBy(['root' => $root, 'groupID' => $groupsToRemove]);
		foreach ($toRemove as $rootSetting) {
			$rootSetting->delete();
		}

		$groupsToAdd = array_diff($groups, $currentGroups);
		foreach ($groupsToAdd as $groupID) {
			$rootEditorGroup = new C5dkRootEditorGroup;
			$rootEditorGroup->setRoot($root);
			$rootEditorGroup->setGroupID($groupID);
			$rootEditorGroup->save();
		}
	}

	public static function setWriterGroups($root, $groups)
	{
		$currentGroups = [];

		$writerGroups = $root->getWriterGroups();
		foreach ($writerGroups as $writerGroup) {
			if (is_object($writerGroup)) {
				$currentGroups[] = $writerGroup->getGroupID();
			} else {
				$currentGroups[] = $writerGroup;
			}
		}

		$groupsToRemove = array_diff($currentGroups, $groups);
		$toRemove = C5dkRootWriterGroup::findBy(['root' => $root, 'groupID' => $groupsToRemove]);
		foreach ($toRemove as $rootSetting) {
			$rootSetting->delete();
		}

		$groupsToAdd = array_diff($groups, $currentGroups);
		foreach ($groupsToAdd as $groupID) {
			$rootWriterGroup = new C5dkRootWriterGroup;
			$rootWriterGroup->setRoot($root);
			$rootWriterGroup->setGroupID($groupID);
			$rootWriterGroup->save();
		}
	}

	public function save()
	{
		$em = Database::connection()->getEntityManager();
		$em->persist($this);
		$em->flush();
	}

	public function delete()
	{
		$em = Database::connection()->getEntityManager();
		$em->remove($this);
		$em->flush();
	}
}
