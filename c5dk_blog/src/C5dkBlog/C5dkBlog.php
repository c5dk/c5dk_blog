<?php
namespace C5dk\Blog;

use Database;
use Events;
use User;
use Page;
use PageType;
use CollectionAttributeKey;
use Concrete\Core\Page\Type\Composer\OutputControl as PageTypeComposerOutputControl;
use Concrete\Core\Page\Type\Composer\FormLayoutSetControl as PageTypeComposerFormLayoutSetControl;
use Block;
use PageTemplate;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Tree\Node\Type\Topic as Topic;

use C5dk\Blog\C5dkRoot;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkBlog extends Page
{
	// Data
	public $blogID      = null;
	public $root        = null;
	public $rootID      = null;
	public $authorID    = null;
	public $thumbnail   = null;
	public $title       = '';
	public $description = '';
	public $content     = '';
	public $tags        = null;
	public $topics      = null;
	public $publishTime = null;
	public $unpublishTime = null;

	public function __construct()
	{
		$this->root = $this->getRoot();
		$this->rootID = $this->getRootID();
	}

	public static function getByID($blogID, $version = 'RECENT', $class = 'C5dk\Blog\C5dkBlog')
	{
		$blog              = parent::getByID($blogID, $version, $class);
		$blog->blogID      = $blogID;
		$blog->root        = $blog->getRoot();
		$blog->rootID      = $blog->getRootID();
		$blog->title       = $blog->getCollectionName();
		$blog->description = $blog->getCollectionDescription();
		$blog->authorID    = $blog->getAttribute('c5dk_blog_author_id');
		$blog->content     = $blog->getContent();
		$blog->thumbnail   = $blog->getAttribute('thumbnail');
		$blog->tags        = $blog->getAttributeValueObject(CollectionAttributeKey::getByHandle('tags'));
		$blog->topics      = $blog->getTopics();
		$publishTime = $blog->getAttribute('c5dk_blog_publish_time');
		$blog->publishTime = $publishTime ? $publishTime->format('Y/m/d H:i:s') : (new \DateTime("now"))->format('Y/m/d H:i:s');
		$unpublishTime = $blog->getAttribute('c5dk_blog_unpublish_time');
		$blog->unpublishTime = $unpublishTime ? $unpublishTime->format('Y/m/d H:i:s') : (new \DateTime)->format('Y/m/d H:i:s');

		return $blog;
	}

	public function save($mode)
	{
		switch ($mode) {
			case C5DK_BLOG_MODE_CREATE:
				$this->root = C5dkRoot::getByID($this->rootID);
				$pt       = PageType::getByID($this->root->getBlogPageTypeID());
				$blog     = $this->root->add($pt, [
					'cName' => $this->title,
					'cHandle' => $this->getUrlSlug($this->title),
					'cDescription' => $this->description,
					'cAcquireComposerOutputControls' => true
				]);

				// TODO: Hack until solution have been found for the following bug. https://github.com/concrete5/concrete5/issues/2991
				// make sure we can properly edit out embedded composer blocks
				$pt->savePageTypeComposerForm($blog);
				$pt->publish($blog);
				// set name and description again, saving from composer seems to clear them
				$blog->update([
					'cName' => $this->title,
					'cDescription' => $this->description
				]);

				// Set Blog Author ID
				$u = new User;
				$blog->setAttribute('c5dk_blog_author_id', $u->getUserID());
				$C5dkBlog = C5dkBlog::getByID($blog->cID);
				break;

			case C5DK_BLOG_MODE_EDIT:
				$C5dkBlog = C5dkBlog::getByID($this->blogID);
				$C5dkBlog->update([
					'cName' => $this->title,
					'cDescription' => $this->description
				]);
				break;

			default:
				return false;
		}

		// Update the Content Block with the blog text
		if (empty($this->content)) {
			$this->content = ' ';
		}
		$instance = $C5dkBlog->getInstance();
		$instance->save(['content' => $this->content]);

		// Save tags to the blog page
		$C5dkBlog   = $C5dkBlog->getVersionToModify();
		$cakTags    = CollectionAttributeKey::getByHandle('tags');
		$controller = $cakTags->getController();
		$value      = $controller->createAttributeValueFromRequest();
		$C5dkBlog->setAttribute($cakTags, $value);

		// Add topics to the blog page if topics are in use
		if ($this->root->getTopicAttributeHandle()) {
			$cakTopics  = CollectionAttributeKey::getByHandle($this->root->getTopicAttributeHandle());
			$controller = $cakTopics->getController();
			$value = $controller->createAttributeValueFromRequest();
			if (is_object($value)) {
				$C5dkBlog->setAttribute($cakTopics, $value);
				// $C5dkBlog->refreshCache();
			}
		}

		// Set Publish/Unpublish Time
		$C5dkBlog->setAttribute('c5dk_blog_publish_time', $this->publishTime);
		$C5dkBlog->setAttribute('c5dk_blog_unpublish_time', $this->unpublishTime);

		// Set meta attributes
		$C5dkBlog->setAttribute('meta_title', $this->title);
		$C5dkBlog->setAttribute('meta_description', $this->description);

		// Set the Approve page attribute if the root don't require approval
		if (!$C5dkBlog->root->needsApproval) {
			$C5dkBlog->setAttribute('c5dk_blog_approved', true);
		}

		$C5dkBlog->refreshCache();

		$C5dkBlog->getVersionObject()->approve();

		return $C5dkBlog;
	}

	public function delete()
	{
		$this->deleteThumbnail();
		parent::delete();
	}

	public function moveToTrash()
	{
		$this->deleteThumbnail();
		parent::moveToTrash();
	}

	public function saveThumbnail()
	{
		// Save Thumbnail
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if (is_object($cak)) {
			$this->setAttribute($cak, $this->thumbnail);
		}

		// if (is_object($thumbnail)) {
		//     $cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
		//     $C5dkBlog = $C5dkBlog->getVersionToModify();
		//     $C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
		//     $C5dkBlog->refreshCache();
		//     $C5dkBlog->getVersionObject()->approve();
		// }
	}

	public function deleteThumbnail()
	{
		// Remove old thumbnail from filemanager
		$thumbnail = $this->getAttribute('thumbnail');
		$u         = new user;
		if (is_object($thumbnail) && $thumbnail->getRecentVersion()->getFileName() == 'C5DK_BLOG_uID-' . $u->getUserID() . '_Thumb_cID-' . $this->blogID . '.' . $thumbnail->getRecentVersion()->getExtension()) {
			$thumbnail->delete();
		}

		// Clear the thumbnail attribute
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if ($C5dkBlog instanceof C5dkBlog && is_object($cak)) {
			$C5dkBlog->clearAttribute($cak);
		}
	}

	// Get the specified blogs root ID
	private function getRootID()
	{
		$page = $this;

		$root = $this->findRoot($page);
		return is_object($root) ? $root->getCollectionID() : null;
	}

	private function getRoot()
	{
		$page = $this;

		return $this->findRoot($page);
	}

	private function findRoot($page)
	{
		$pageID = $page->getCollectionID();
		while ($page->getCollectionID() > 1) {
			if (!$page->getAttribute('c5dk_blog_root')) {
				$page = Page::getByID($page->getCollectionParentID());
				continue;
			}

			// Found the root
			return C5dkRoot::getByID($page->getCollectionID());
		}

		// Didn't find the root
		return null;
	}

	// Get blog content from the first content block in the main area or return empty "" string
	private function getContent()
	{
		foreach ($this->getBlocks('Main') as $block) {
			if ($block->getBlockTypeHandle() == 'content') {
				$instance = $block->getInstance();
				// \Log::addEntry(get_class($instance));
				return $instance->getContent();
			}
		}

		return '';
	}

	private function getTopics()
	{
		if (!$this->rootID) {
			return 0;
		}

		$C5dkRoot = C5dkRoot::getByID($this->rootID);
		if ($C5dkRoot->getTopicAttributeHandle()) {
			return $this->getAttributeValueObject(CollectionAttributeKey::getByHandle($C5dkRoot->getTopicAttributeHandle()));
		} else {
			return 0;
		}
	}

	private function getUrlSlug($name)
	{
		$app  = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$name = $app->make('helper/text')->urlify($name);
		$ret  = Events::fire('on_page_urlify', $name);

		return (!$ret) ? $name : $ret;
	}

	private function getInstance()
	{
		foreach ($this->getBlocks('Main') as $block) {
			if ($block->getBlockTypeHandle() == 'content') {
				return $block->getInstance();
			}
		}

		return
		false;
	}

	public function getPriorityList()
	{
		$tree = TopicTree::getByName('News Priorities');
		$node = $tree->getRootTreeNodeObject();
		$node->populateChildren();

		$nodes = array(t('None'));
		foreach ($node->getChildNodes() as $node) {
			// if ($node instanceof \Concrete\Core\Tree\Node\Type\Topic) {
			$nodes[$node->getTreeNodeID()] = $node->getTreeNodeDisplayName();
			// }
		}

		return $nodes;
	}


	// public function getPriorityList()
	// {
	// 	$db = Database::connection();

	// 	// Get Topic Tree name
	// 	$C5dkRoot      = C5dkRoot::getByID($this->rootID);
	// 	$akTopicTreeID = $db->GetOne("SELECT akTopicTreeID FROM atTopicSettings WHERE akID = ?", array($C5dkRoot->priorityAttributeID));
	// 	$topicTreeName = $db->GetOne("SELECT topicTreeName FROM TopicTrees WHERE treeID = ?", array($akTopicTreeID));

	// 	$tt = new TopicTree();
	// 	/** @var Topic $tree */
	// 	$tree = $tt->getByName($topicTreeName);
	// 	/** @var TopicCategory $node */
	// 	$node = $tree->getRootTreeNodeObject();
	// 	$node->populateChildren();
	// 	$topics = array();

	// 	/** @var Concrete/Core/Tree/Node/Type/Topic $topic */
	// 	foreach ($node->getChildNodes() as $topic) {
	// 		if ($topic instanceof Topic) {
	// 			$topics[$topic->getTreeNodeID()] = $topic->getTreeNodeDisplayName();
	// 		}
	// 	}

	// 	return $topics;
	// }

	public function isEditor($userID)
	{
		// Is this a valid request?
		if (!$userID || !$this->rootID) {
			return false;
		}

		// Set C5dk Objects
		$user     = User::getByUserID($userID);
		$C5dkRoot = C5dkRoot::getByID($this->rootID);

		// Is the user a member of one of the editor groups for this root.
		return (count(array_intersect($user->getUserGroups(), $C5dkRoot->editorGroups))) ? true : false;
	}

	public function setPermissions($readGID, $editGID)
	{
		$this->setPermissionsToManualOverride();

		$pk = PermissionKey::getByHandle('view_page');
		$pk->setPermissionObject($this);
		$pt = $pk->getPermissionAssignmentObject();
		$pt->clearPermissionAssignment();
		$pa = PermissionAccess::create($pk);

		if (is_array($readGID)) {
			foreach($readGID as $gID) {
				$pa->addListItem(GroupPermissionAccessEntity::getOrCreate(Group::getByID($gID)));
			}
		}

		$pt->assignPermissionAccess($pa);

		$editAccessEntities = array();
		if (is_array($editGID)) {
			foreach($editGID as $gID) {
				$editAccessEntities[] = GroupPermissionAccessEntity::getOrCreate(Group::getByID($gID));
			}
		}

		$editPermissions = array(
			'view_page_versions',
			'edit_page_properties',
			'edit_page_contents',
			'edit_page_speed_settings',
			'edit_page_theme',
			'edit_page_page_type',
			'edit_page_template',
			'edit_page_permissions',
			'preview_page_as_user',
			'schedule_page_contents_guest_access',
			'delete_page',
			'delete_page_versions',
			'approve_page_versions',
			'add_subpage',
			'move_or_copy_page',
		);
		foreach($editPermissions as $pkHandle) {
			$pk = PermissionKey::getByHandle($pkHandle);
			$pk->setPermissionObject($this);
			$pt = $pk->getPermissionAssignmentObject();
			$pt->clearPermissionAssignment();
			$pa = PermissionAccess::create($pk);
			foreach($editAccessEntities as $editObj) {
				$pa->addListItem($editObj);
			}

			$pt->assignPermissionAccess($pa);


			$c = $this->getCollectionObj();
			$pxml->guests['canRead'] = false;
			$pxml->registered['canRead'] = false;
			$pxml->group[0]['gID'] = ADMIN_GROUP_ID;
			$pxml->group[0]['canRead'] = true;
			$pxml->group[0]['canWrite'] = true;
			$pxml->group[0]['canApproveVersions'] = true;
			$pxml->group[0]['canDelete'] = true;
			$pxml->group[0]['canAdmin'] = true;
			$pxml->user[0]['uID']=$this->getUID();
			$pxml->user[0]['canRead'] = true;
			$pxml->user[0]['canWrite'] = false;
			$pxml->user[0]['canAdmin'] = false;
			$c->assignPermissionSet($pxml);


		}

		// $this->assignPermissions(Group::getByID(REGISTERED_GROUP_ID), array(
		// 	'view_page',
		// 	'view_page_in_sitemap'
		// ));

		// $this->assignPermissions(Group::getByID(ADMIN_GROUP_ID), array(
		// 	'view_page_versions',
		// 	'edit_page_properties',
		// 	'edit_page_contents',
		// 	'approve_page_versions',
		// 	'move_or_copy_page',
		// 	'preview_page_as_user',
		// 	'add_subpage'
		// ));

		// $this->removePermissions(Group::getByID(GUEST_GROUP_ID), array(
		// 	'view_page',
		// 	'view_page_in_sitemap'
		// ));

	}
}
