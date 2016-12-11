<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBLog;

use Core;
use Database;
use Page;
use PageType;
use GroupList;
use Concrete\Core\Attribute\Type as attributeType;
use CollectionAttributeKey;

use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkRootList as C5dkRootList;

defined('C5_EXECUTE') or die("Access Denied.");

class BlogRoots extends DashboardPageController {

	public function view() {

		// Set all our view variables
		$C5dkRootList =						new C5dkRootList;
		$this->set('user',					new C5dkUser);
		$this->set('rootList',				$C5dkRootList->getResults());

		$this->set('groupList',				$this->getAllGroups());
		$this->set('pageTypeList',			$this->getAllPageTypes());
		$this->set('topicAttributeList',	$this->getTopicsAttributeList());

		// Set helpers
		$this->set('form', Core::make('helper/form'));

		// Require Assets
		$this->requireAsset('select2');
	}

	public function save(){

		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$db = $app->make('database')->connection();

		// Delete old values in db
		$db->Execute('DELETE FROM C5dkBlogRootPermissions');

		// Save new permission values in db
		$postData = $this->post();
		foreach ($postData as $postKey => $postVal) {
			if(substr($postKey, 0, 12) == "root_groups_" && is_array($postVal)) {
				foreach ($postVal as $groupID) {
					$rootID = substr($postKey, 12);
					$db->Execute('INSERT INTO C5dkBlogRootPermissions (rootID, groupID, pageTypeID, topicAttributeID) VALUES (?, ?, ?, ?)', array(
						$rootID,
						$groupID,
						$postData["pageTypeID_" . $rootID],
						$postData["topicAttributeID_" . $rootID]
					));
				}
			}
		}

		$this->set('message', t('Root values saved.'));
		$this->view();

	}

	public function delete($rootID) {
		$root = Page::getByID($rootID);
		$ak = CollectionAttributeKey::getByHandle('c5dk_blog_root');
		$root->clearAttribute($ak);
		$this->set('message', t("Blog Root has been removed"));
		$this->view();
	}

	public function getAllGroups(){

		// Get all groups registered in Concrete5
		$gl = new GroupList();
		$gl->sortBy('gID', 'asc');
		$gl->includeAllGroups();

		// Use GroupID as the array key
		foreach ($gl->getResults() as $key => $value) {
			// Remove the Guest group
			if($value->gID == 1){ continue; }

			$groups[$value->gID] = t($value->gName);
		}
		asort($groups);

		return $groups;

	}

	public function getAllPageTypes() {

		foreach(PageType::getList() as $index => $pageType) {
			$pageTypeList[$pageType->ptID] = $pageType->ptName;
		}

		return $pageTypeList;

	}

	public function getTopicsAttributeList() {
		$tt = new TopicTree;
		$trees[0] = t('None');
		foreach ($tt->getList() as $tree) {
			$trees[$tree->rootTreeNodeID] = $tree->getTreeName();
		}
		return $trees;
	}
	// 	$atTopicsID = attributeType::getByHandle('topics')->getAttributeTypeID();
	// 	$topicAttributeList = array(0 => t("None"));
	// 	foreach (CollectionAttributeKey::getList() as $attribute) {
	// 		if ($attribute->atID == $atTopicsID) {
	// 			$topicAttributeList[$attribute->getAttributeKeyID()] = $attribute->getAttributeKeyName();
	// 		}
	// 	}

	// 	return $topicAttributeList;
	// }

	// public function getTopicList() {
 //        $tt = new TopicTree();
 //        $defaultTree = $tt->getDefault();
 //        $topicTreeList = $tt->getList();
 //        $tree = $tt->getByID(Core::make('helper/security')->sanitizeInt($this->akTopicTreeID));
 //        if (!$tree) {
 //            $tree = $defaultTree;
 //        }
 //        $this->set('tree', $tree);
 //        $trees = array();
 //        if (is_object($defaultTree)) {
 //            $trees[] = $defaultTree;
 //            foreach ($topicTreeList as $ctree) {
 //                if ($ctree->getTreeID() != $defaultTree->getTreeID()) {
 //                    $trees[] = $ctree;
 //                }
 //            }
 //        }
 //        $this->set('trees', $trees);
 //        $this->set('parentNode', $this->akTopicParentNodeID);
 //    }
}
