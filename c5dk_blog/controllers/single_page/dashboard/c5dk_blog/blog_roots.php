<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBLog;

use Core;
use Database;
use Session;
use Page;
use PageType;
use PageTemplate;
use GroupList;
use CollectionAttributeKey;
use Concrete\Core\Attribute\Type as attributeType;
use Concrete\Core\Attribute\Key\CollectionKey;

use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Page\Controller\DashboardPageController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkRoot as C5dkRoot;
use C5dk\Blog\C5dkRootList as C5dkRootList;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die("Access Denied.");

class BlogRoots extends DashboardPageController
{

	public function view()
	{
		// Set all our view variables
		$C5dkRootList = new C5dkRootList;
		$rootList = $C5dkRootList->getResults();
		$this->set('user', new C5dkUser);
		$this->set('rootList', $rootList);

		$this->set('groupList', $this->getAllGroups());
		// $this->set('editorGroupList', $this->getAllEditorGroups());
		$pageTypes = $this->getAllPageTypes();
		$this->set('pageTypeList', $pageTypes);
		$this->set('topicAttributeList', $this->getTopicsAttributeList());

		// Set helpers
		$this->set('form', $this->app->make('helper/form'));

		// Require Assets
		$this->requireAsset('select2');

		// Should we show a message?
		$message = Session::get('c5dk_blog_message');
		if ($message) {
			Session::set('c5dk_blog_message', '');
			$this->set('success', $message);
		}
	}

	public function save()
	{
		foreach ($this->post('root') as $rootID => $data) {
			$data['rootID'] = $rootID;
			C5dkRootEntity::saveForm($data);
		}

		Session::set('c5dk_blog_message', t('Root values saved.'));
		$this->redirect('/dashboard/c5dk_blog/blog_roots');
	}

	public function delete($rootID)
	{
		C5dkRoot::removeRoot($rootID);
		$this->set('success', t("Blog Root has been removed"));
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
			$pageTypeDefaultTemplateID = $pageType->getPageTypeDefaultPageTemplateID();
			$template = PageTemplate::getByID($pageTypeDefaultTemplateID);
			$c = $pageType->getPageTypePageTemplateDefaultPageObject($template);

			foreach ($c->getBlocks('Main') as $block) {
				$blockType = $block->getBlockTypeObject();
				if ($blockType->getBlockTypeHandle() == "core_page_type_composer_control_output") {
					$pageTypeList[$pageType->getPageTypeID()] = $pageType->getPageTypeDisplayName();
				}
			}
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

	public function topicTreeList()
	{

		// Get the Topics Attribute Type object
		$atTopics = AttributeType::getByHandle('topics');

		// Set our default value
		$topicTreeList = [0 => t("None")];

		if ($atTopics instanceof AttributeType) {
			$atTopicsID = $atTopics->getAttributeTypeID();
			foreach (CollectionAttributeKey::getList() as $attribute) {
				if ($attribute->atID == $atTopicsID) {
					$topicTreeList[$attribute->getAttributeKeyID()] = $attribute->getAttributeKeyName();
				}
			}
		}

		return $topicTreeList;
	}
}
