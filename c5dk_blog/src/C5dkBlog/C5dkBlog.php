<?php
namespace C5dk\Blog;

use Core;
use Database;
use Events;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type as PageType;
// use PageType;
use Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;
// use CollectionAttributeKey;
// use Concrete\Core\Page\Type\Composer\OutputControl as PageTypeComposerOutputControl;
// use Concrete\Core\Page\Type\Composer\FormLayoutSetControl as PageTypeComposerFormLayoutSetControl;
// use Block;
// use PageTemplate;
use Concrete\Core\Tree\Type\Topic as TopicTree;
// use Concrete\Core\Tree\Node\Type\Topic as Topic;
// use Concrete\Core\Entity\Attribute\Value\Value\SelectedTopic;
// use Concrete\Core\Entity\Attribute\Value\Value\TopicsValue;
use Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
use Concrete\Core\Permission\Access\Entity\UserEntity as UserPermissionAccessEntity;

use C5dk\Blog\C5dkRoot;
use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkBlog extends Page
{
	public $blogID = 0;

	public static function getByID($blogID, $version = 'RECENT', $class = 'C5dk\Blog\C5dkBlog')
	{
		$blog                = parent::getByID($blogID, $version, $class);
		$blog->blogID        = $blog->getCollectionID();
		// $blog->root          = $blog->getRoot();
		// $blog->rootID        = $blog->getRootID();
		// $blog->title         = $blog->getCollectionName();
		// $blog->description   = $blog->getCollectionDescription();
		// $blog->authorID      = $blog->getAttribute('c5dk_blog_author_id');
		// $blog->content       = $blog->getContent();
		// $blog->thumbnail     = $blog->getAttribute('thumbnail');
		// $blog->tags          = $blog->getAttributeValueObject('tags');
		// $blog->topics        = $blog->getTopics();
		// $blog->priority      = $blog->getAttribute('c5dk_blog_priority');
		// $publishTime         = $blog->getAttribute('c5dk_blog_publish_time');
		// $blog->publishTime   = $publishTime ? $publishTime->format('Y-m-d H:i') : (new \DateTime)->format('Y-m-d H:i');
		// $unpublishTime       = $blog->getAttribute('c5dk_blog_unpublish_time');
		// $blog->unpublishTime = $unpublishTime ? $unpublishTime->format('Y-m-d H:i') : (new \DateTime)->format('Y-m-d H:i');
		// $blog->approved      = $blog->getAttribute('c5dk_blog_approved');

		return $blog;
	}

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

	public function isUnpublished()
	{
		$access = $this->checkGroupPermission('view_page', GUEST_GROUP_ID);
		// Do guests have view permissions
		if ($access) {
			return false;
		} else {
			return true;
		}
	}

	public function getAuthorID()
	{
		return $this->getAttribute('c5dk_blog_author_id');
	}

	public function getTitle()
	{
		return $this->getCollectionName();
	}

	public function getDescription()
	{
		return $this->getCollectionDescription();
	}

	// Get blog content from the first content block in the main area or return empty "" string
	public function getContent()
	{
		// foreach ($this->getBlocks('Main') as $block) {
		// 	if ($block->getBlockTypeHandle() == 'content') {
		// 		$instance = $block->getInstance();

		// 		return $instance->getContent();
		// 	}
		// }
		$contentInstance = $this->getInstance();
		if ($contentInstance) {
			return $contentInstance->getContent();
		}

		return '';
	}

	public function getInstance()
	{
		foreach ($this->getBlocks('Main') as $block) {
			if ($block->getBlockTypeHandle() == 'content') {
				return $block->getInstance();
			}
		}

		return false;
	}

	public function getTags()
	{
		return $this->getAttributeValueObject('tags');
	}

	public function getPriority()
	{
		return $this->getAttribute('c5dk_blog_priority');
	}

	public function getTopics()
	{
		if (!$this->getRootID()) {
			return 0;
		}

		$C5dkRoot = C5dkRoot::getByID($this->getRootID());
		if ($C5dkRoot->getTopicAttributeHandle()) {
			return $this->getAttributeValueObject($C5dkRoot->getTopicAttributeHandle());
		} else {
			return 0;
		}
	}

	public function getThumbnail()
	{
		return $this->getAttribute('thumbnail');
	}

	public function getApproved()
	{
		return $this->getAttribute('c5dk_blog_approved');
	}

	public function getPublishTime($type = "text")
	{
		$publishTime = $this->getAttribute('c5dk_blog_publish_time');

		if (!$publishTime) {
			$publishTime = new \DateTime();
		}

		switch ($type) {
			case 'text':
				$publishTime = $publishTime->format('Y-m-d H:i');
				break;
		}
		return $publishTime;
	}

	public function getUnpublishTime($type = "text")
	{
		$unpublishTime = $this->getAttribute('c5dk_blog_unpublish_time');

		if (!$unpublishTime) {
			$unpublishTime = new \DateTime("2099-01-01 00:00:00");
		}

		switch ($type) {
			case 'text':
				$unpublishTime = $unpublishTime->format('Y-m-d H:i');
				break;
		}

		return $unpublishTime;
	}

	public function save($blogID)
	{
		$request = Request::getInstance();
		$post = $request->post();

		$u = new User;
		switch ($blogID) {
			case 0:
				$C5dkRoot = C5dkRoot::getByID($post['rootID']);
				$pt       = PageType::getByID($C5dkRoot->getBlogPageTypeID());
				$C5dkBlog     = $C5dkRoot->add($pt, [
					'cName' => $post['title'],
					'cHandle' => $this->getUrlSlug($post['title']),
					'cDescription' => $post['description'],
					'cAcquireComposerOutputControls' => true
				]);
				// TODO: Hack until solution have been found for the following bug. https://github.com/concrete5/concrete5/issues/2991
				// make sure we can properly edit out embedded composer blocks
				$pt->savePageTypeComposerForm($C5dkBlog);
				$pt->publish($C5dkBlog);

				// set name and description again, saving from composer seems to clear them
				$C5dkBlog->update([
					'cName' => $post['title'],
					'cDescription' => $post['description']
				]);

				$blogID = $C5dkBlog->getCollectionID();
				// Set Blog Author ID
				$C5dkBlog->setAttribute('c5dk_blog_author_id', $u->getUserID());
				$C5dkBlog = C5dkBlog::getByID($blogID);
				break;

			default:
				$C5dkBlog = C5dkBlog::getByID($post['blogID']);
				$C5dkRoot = $C5dkBlog->getRoot();
				$C5dkBlog->update([
					'cName' => $post['title'],
					'cDescription' => $post['description']
				]);
				break;
		}

		// Set meta attributes
		$C5dkBlog->setAttribute('meta_title', $post['title']);
		$C5dkBlog->setAttribute('meta_description', $post['description']);

		// Save tags to the blog page
		$cakTags    = CollectionAttributeKey::getByHandle('tags');
		$controller = $cakTags->getController();
		$value      = $controller->createAttributeValueFromRequest();
		$C5dkBlog->setAttribute($cakTags, $value);

		// Update the Content Block with the blog text
		if (empty($post['c5dk_blog_content'])) {
			$content = ' ';
		} else {
			$content = $post['c5dk_blog_content'];
		}
		$instance = $C5dkBlog->getInstance();
		$instance->save(['content' => $content]);

		// $C5dkBlog   = $C5dkBlog->getVersionToModify();

		// Add topics to the blog page if topics are in use
		if ($C5dkRoot->getTopicAttributeHandle()) {
			$cakTopics  = CollectionAttributeKey::getByHandle($C5dkRoot->getTopicAttributeHandle());
			$controller = $cakTopics->getController();
			$value = $controller->createAttributeValueFromRequest();
			if (is_object($value)) {
				$C5dkBlog->setAttribute($cakTopics, $value);
			}
		}

		// Set Publish/Unpublish Time
		if ($C5dkRoot->getPublishTime() && $post['publishTime']) {
			$C5dkBlog->setAttribute('c5dk_blog_publish_time', new \datetime($post['publishTime']));
		}
		if ($C5dkRoot->getUnpublishTime() && $post['unpublishTime']) {
			$C5dkBlog->setAttribute('c5dk_blog_unpublish_time', new \datetime($post['unpublishTime']));
		}

		// Set Permissions
		foreach ($C5dkRoot->getEditorGroupsArray() as $groupID) {
			$this::grantPagePermissionByGroup(['view_page'], $C5dkBlog, $groupID);
		}
		$this::grantPagePermissionByUser(['view_page'], $C5dkBlog, $u->getUserInfoObject()->getUserID());

		// Set the Approve page attribute if the root don't require approval
		if (!$C5dkBlog->getRoot()->getNeedsApproval()) {
			$C5dkBlog->setAttribute('c5dk_blog_approved', true);
		} else {
			// If the Blog needs approval we need to remove the guest access
			$this::denyPagePermissionByGroup(['view_page'], $C5dkBlog, GUEST_GROUP_ID);
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

	public function deleteThumbnail($user = null)
	{
		// Remove old thumbnail from filemanager
		$thumbnail = $this->getAttribute('thumbnail');
		if (!is_object($user)) {
			$user = new User;
		}
		if (is_object($thumbnail) && $thumbnail->getRecentVersion()->getFileName() == 'C5DK_BLOG_uID-' . $user->getUserID() . '_Thumb_cID-' . $this->getCollectionID() . '.' . $thumbnail->getRecentVersion()->getExtension()) {
			$thumbnail->delete();
		}

		// Clear the thumbnail attribute
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if ($this instanceof C5dkBlog && is_object($cak)) {
			$this->clearAttribute($cak);
		}
	}

	// Get the specified blogs root ID
	public function getRootID()
	{
		// $page = $this;

		$root = $this->findRoot($this);
		return is_object($root) ? $root->getCollectionID() : null;
	}

	public function getRoot()
	{
		// $page = $this;

		return $this->findRoot($this);
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


	// --- Helper methods ---

	private function getUrlSlug($name)
	{
		$app  = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$name = $app->make('helper/text')->urlify($name);
		$ret  = Events::fire('on_page_urlify', $name);

		return (!$ret) ? $name : $ret;
	}

	public function getPriorityList()
	{
		$tree = TopicTree::getByName('Blog Priorities');
		$node = $tree->getRootTreeNodeObject();
		$node->populateChildren();

		$nodes = [];
		foreach ($node->getChildNodes() as $node) {
			// if ($node instanceof \Concrete\Core\Tree\Node\Type\Topic) {
			$nodes[$node->getTreeNodeDisplayName()] = $node->getTreeNodeDisplayName();
			// }
		}

		return $nodes;
	}

	public function getTopicsArray($topics)
	{
		$topicsList = [];
		if (is_array($topics)) {
			foreach ($topics as $topic) {
				$topicList[] = $topic->getTreeNodeDisplayName();
			}
		}

		return $topicList;
	}

	public function setPriority($values)
	{
		$topics = [];
		foreach ($values as $value) {
			$topics[] = TopicTreeNode::getNodeByName($value)->getTreeNodeDisplayPath();
		}
		if (count($topics)) {
			$this->setAttribute('c5dk_blog_priority', $topics);
		}
	}

	public function publish()
	{
		$this->setAttribute('c5dk_blog_approved', true);
		$this->setAttribute('c5dk_blog_publish_time', new \datetime());

		// TODO: Do we need to look at the unpublish time too???

		return true;
	}

	public static function grantPagePermissionByGroup($permission = ['view_page'], $page, $groupID)
	{
		// enable access by a group
		$g = Group::getByID($groupID);
		if (is_object($g)) {
			$page->setPermissionsToOverride();
			$page->assignPermissions($g, $permission, PermissionKey::ACCESS_TYPE_INCLUDE, false);

			// Advanced way: Not usre if it has a problem
			// $pk = PermissionKey::getByHandle($permission);
			// $pk->setPermissionObject($page);
			// $pa = $pk->getPermissionAccessObject();

			// $pae = GroupPermissionAccessEntity::getOrCreate($g);
			// $pa->addListItem($pae, false, PermissionKey::ACCESS_TYPE_INCLUDE);

			// // Apply the the permissions changes
			// $pa->markAsInUse();
			// \Log::addEntry('GrantGroup: ' . $page->getCollectionID());
		}
	}

	public static function denyPagePermissionByGroup($permission = ['view_page'], $page, $groupID)
	{
		// remove Guest access
		$g = Group::getByID($groupID);
		if (is_object($g)) {
			$page->setPermissionsToOverride();
			$page->removePermissions($g, $permission);

			// Advanced way: Not usre if it has a problem
			// $pk = PermissionKey::getByHandle($permission);
			// $pk->setPermissionObject($page);
			// $pa = $pk->getPermissionAccessObject();
			// $pe = GroupPermissionAccessEntity::getOrCreate($g);
			// $pa->removeListItem($pe);

			// // Apply the the permissions changes
			// $pa->markAsInUse();
			// \Log::addEntry('DenyGroup: ' . $page->getCollectionID());
		}
	}

	// DEPRECATED: Look above for how to set/remove permission
	public static function grantPagePermissionByUser($permission, $page, $userID)
	{
		// enable access by user
		$ui = UserInfo::getByID($userID);
		
		// if (is_object($ui)) {
			// }
			
		if (is_object($ui)) {
			$page-> assignPermissions($ui, $permission, PermissionKey::ACCESS_TYPE_INCLUDE, false);
			// $page->setPermissionsToOverride();
			// $pk = PermissionKey::getByHandle($permission);
			// $pk->setPermissionObject($page);
			// $pa = $pk->getPermissionAccessObject();
			// $pae = UserPermissionAccessEntity::getOrCreate($ui);
			// $pa->addListItem($pae, false, PermissionKey::ACCESS_TYPE_INCLUDE);

			// // Apply the the permissions changes
			// $pa->markAsInUse();
			// \Log::addEntry('GrantUser: ' . $page->getCollectionID());
		}
	}

	// DEPRECATED: Look above for how to set/remove permission (Not used)
		// public static function denyPagePermissionByUser($permission, $page, $userID)
		// {
		// 	// remove user access
		// 	$ui = UserInfo::getByID($userID);
		// 	// if (is_object($ui)) {
		// 		// $page->setPermissionsToOverride();
		// 	// 	// $page->removePermissions($ui, [$permission]);
		// 	// }

		// 	if (is_object($ui)) {
		// 		$pk = PermissionKey::getByHandle($permission);
		// 		$pk->setPermissionObject($page);
		// 		$pa = $pk->getPermissionAccessObject();
		// 		$pe = UserPermissionAccessEntity::getOrCreate(UserInfo::getByID($userID));
		// 		$pa->removeListItem($pe);

		// 		// Apply  the the permissions changes
		// 		$pa->markAsInUse();
		// 		\Log::addEntry('DenyUser: ' . $page->getCollectionID());
		// 	}
		// }

	public function checkGroupPermission($permissionHandle, $groupID)
	{
		$key = PermissionKey::getByHandle($permissionHandle);
		$key->setPermissionObject($this);

		$access = $key->getPermissionAccessObject();
		if (!$access) {
			return false;
		}
		$group = Group::getByID($groupID);
		$entity = GroupPermissionAccessEntity::getOrCreate($group);

		return $access->validateAccessEntities([$entity]);
	}

	// Not used and have some problems that should be fixed ($page, $groupID)
		// public function checkUserPermission($permissionHandle, $userID)
		// {
		// 	$key = PermissionKey::getByHandle($permissionHandle);
		// 	$key->setPermissionObject($page);

		// 	$access = $key->getPermissionAccessObject();
		// 	if (!$access) {
		// 		return false;
		// 	}
		// 			$guestGroup = Group::getByID($groupID);
		// 			$entity = GroupPermissionAccessEntity::getOrCreate($guestGroup);

		// 			return $access->validateAccessEntities([$entity]);
		// }

	// Not used and may have problems. See above.
		// public function checkGroupViewPermission($permissionHandle, $page, $groupID)
		// {
		// 	$key = PermissionKey::getByHandle($permissionHandle);
		// 	$key->setPermissionObject($page);

		// 	$access = $key->getPermissionAccessObject();
		// 	if (!$access) {
		// 		return false;
		// 	}
		// 	$guestGroup = Group::getByID($groupID);
		// 	$entity = GroupPermissionAccessEntity::getOrCreate($guestGroup);

		// 	return $access->validateAccessEntities([$entity]);
		// }
}
