<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBLog;

use Core;
use Database;
use Session;
use Page;
use PageType;
use GroupList;
use CollectionAttributeKey;
use Concrete\Core\Attribute\Type as attributeType;
use Concrete\Core\Attribute\Key\CollectionKey;

use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkRootList as C5dkRootList;

defined('C5_EXECUTE') or die("Access Denied.");

class BlogRoots extends DashboardPageController
{

	public function view()
	{
		// Set all our view variables
		$C5dkRootList = new C5dkRootList;
		$this->set('user',					new C5dkUser);
		$this->set('rootList',				$C5dkRootList->getResults());

		$this->set('groupList',				$this->getAllGroups());
		$this->set('pageTypeList',			$this->getAllPageTypes());
		$this->set('topicAttributeList',	$this->getTopicsAttributeList());

		// Set helpers
		$this->set('form', $this->app->make('helper/form'));

		// Require Assets
		$this->requireAsset('select2');

		// Should we show a message?
		$message = Session::get('c5dk_blog_message');
		if ($message) {
			Session::set('c5dk_blog_message', '');
			$this->set('message', $message);
		}
	}

	public function save()
	{
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$db  = $app->make('database')->connection();

		// Delete old values in db
		$db->Execute('DELETE FROM C5dkBlogRootPermissions');

		// Save new permission values in db
		$postData = $this->post();
		foreach ($postData as $postKey => $postVal) {
			if (substr($postKey, 0, 12) == "root_groups_" && is_array($postVal)) {
				foreach ($postVal as $groupID) {
					$rootID = substr($postKey, 12);
					$db->Execute('INSERT INTO C5dkBlogRootPermissions (rootID, groupID, pageTypeID, tags, thumbnails, topicAttributeID) VALUES (?, ?, ?, ?, ?, ?)', array(
						$rootID,
						$groupID,
						$postData["pageTypeID_" . $rootID],
						($postData["tags_" . $rootID] ? 1 : 0),
						($postData["thumbnails_" . $rootID] ? 1 : 0),
						$postData["topicAttributeID_" . $rootID]
					));
				}
			}
		}

		Session::set('c5dk_blog_message', t('Root values saved.'));
		$this->redirect('/dashboard/c5dk_blog/blog_roots');
	}

	public function delete($rootID)
	{
		$root = Page::getByID($rootID);
		$ak   = CollectionAttributeKey::getByHandle('c5dk_blog_root');
		$root->clearAttribute($ak);
		$this->set('message', t("Blog Root has been removed"));
		$this->view();
	}

	public function getAllGroups()
	{
		// Get all groups registered in Concrete5
		$gl = new GroupList();
		$gl->sortBy('gID', 'asc');
		$gl->includeAllGroups();

		// Use GroupID as the array key
		foreach ($gl->getResults() as $key => $value) {
			// Remove the Guest group
			if ($value->gID == 1) {
				continue;
			}

			$groups[$value->gID] = t($value->gName);
		}

		asort($groups);

		return $groups;

	}

	public function getAllPageTypes()
	{
		foreach (PageType::getList() as $index => $pageType) {
			$pageTypeList[$pageType->ptID] = $pageType->ptName;
		}

		return $pageTypeList;

	}

	public function getTopicsAttributeList()
	{
		// $tt = new TopicTree;
		foreach (TopicTree::getList() as $tree) {
			$trees[$tree->getRootTreeNodeID()] = $tree->getTreeName();
		}

		$keys            = CollectionKey::getList();
		$attributeKeys[] = t('None');
		foreach ($keys as $ak) {
			if ($ak->getAttributeTypeHandle() == 'topics') {
				$attributeKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
			}
		}

		return $attributeKeys;
	}
}
