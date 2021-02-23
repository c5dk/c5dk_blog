<?php
namespace C5dk\Blog;

use Page;
use SinglePage;
use BlockTypeSet;
use BlockType;
use UserAttributeKey;
use CollectionAttributeKey;
use AttributeSet;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Entity\Attribute\Key\PageKey;
use Concrete\Core\Entity\Attribute\Key\Settings\TopicsSettings;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Tree\Node\Node as TreeNode;
use Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Core\File\Image\Thumbnail\Type\Type;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkInstaller
{
	public static function installConfigKey($handle, $value, $pkg = false, $override = false)
	{
		if (is_object($pkg)) {
			$config = $pkg->getConfig();
			if (!$config->has($pkg->getPackageHandle() . '.' . $handle) || $override) {
				$config->save($pkg->getPackageHandle() . '.' . $handle, $value);
			}
		} else {
			// TODO: Get the config from $app and use that instead.
		}
	}

	public static function installUserAttributeKey($type, $options, $pkg = false)
	{
		$uak = UserAttributeKey::getByHandle($options['akHandle']);
		if (!is_object($uak)) {
			$uak = UserAttributeKey::add($type, $options, $pkg);
		}

		return $uak;
	}

	public static function installSinglePage($path, $name, $description, $pkg = NULL, $attributes = [])
	{
		$page = Page::getByPath($path);
		if (!is_object($page) || $page->isError()) {
			$page = SinglePage::add($path, $pkg);
			$page->update(['cName' => $name, 'cDescription' => $description]);
			foreach ($attributes as $handle => $value) {
				$page->setAttribute($handle, $value);
			}
		}

		return $page;
	}

	public static function installCollectionAttributeSet($handle, $name)
	{
		$bas = AttributeSet::getByHandle($handle);
		if (!is_object($bas)) {
			$cakc = AttributeKeyCategory::getByHandle('collection');
			$cakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);
			$bas = $cakc->addSet($handle, $name);
		}

		return $bas;
	}

	public static function installCollectionAttributeKey($type, $options, $pkg = false, $set = NULL)
	{
		// $cak = CollectionAttributeKey::getByHandle($options['akHandle']);
		// if (!is_object($cak)) {
		// 	$cak = CollectionAttributeKey::add($type, $options, $pkg);
		// 	if ($set) {
		// 		$cak->setAttributeSet($set);
		// 	}
		// }

		$app = Application::getFacadeApplication();
		$service = $app->make('Concrete\Core\Attribute\Category\CategoryService');
		$categoryEntity = $service->getByHandle('collection');
		$category = $categoryEntity->getController();

		$cak = $category->getByHandle($options['akHandle']);
		if (!is_object($cak)) {
			$cak = new PageKey();
			$cak->setAttributeKeyHandle($options['akHandle']);
			$cak->setAttributeKeyName($options['akName']);
			$cak = $category->add($type, $cak, null, $pkg);

			if ($set) {
				$cak->setAttributeSet($set);
			}
		}


		return $cak;
	}

	public static function installCollectionAttributeKeyTopic($handle, $name, $topicTree, $allowMultiple = false, $set = null) {
		// Add
		$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$service        = $app->make('Concrete\Core\Attribute\Category\CategoryService');
		$categoryEntity = $service->getByHandle('collection');
		$category       = $categoryEntity->getController();

		$pageKey = $category->getByHandle($handle);
		if (!is_object($pageKey)) {
			$pageKey = new PageKey();
			$pageKey->setAttributeKeyHandle($handle);
			$pageKey->setAttributeKeyName($name);
			if ($set) {
				$pageKey->setAttributeSet($set);
			}
			// $pageKey->setIsAttributeKeySearchable(false); // Default: True
			// $pageKey->setIsAttributeKeyContentIndexed(TRUE); // Default: False

			$settings = new TopicsSettings();
			$settings->setTopicTreeID($topicTree->getRootTreeNodeObject()->getTreeID());
			$settings->setParentNodeID($topicTree->getRootTreeNodeObject()->getTreeNodeID());
			$settings->setAllowMultipleValues($allowMultiple);

			$pageKey = $category->add('topics', $pageKey, $settings);
		}

		return $pageKey;
	}

	public static function installBlockTypeSet($handle, $name, $pkg = false)
	{
		$bts = BlockTypeSet::getByHandle($handle);
		if (!is_object($bts) || $bts->isError()) {
			return BlockTypeSet::add($handle, $name, $pkg);
		}
	}

	public static function installBlockType($handle, $pkg = false)
	{
		$bt = BlockType::getByHandle($handle);
		if (!is_object($bt)) {
			return BlockType::installBlockType($handle, $pkg);
		}
	}

	public static function installFileFolder($parentFolder, $name)
	{
		if ($parentFolder == '-root-') {
			$filesystem   = new \Concrete\Core\File\Filesystem();
			$parentFolder = $filesystem->getRootFolder();
		}

		$folder = \Concrete\Core\Tree\Node\Type\FileFolder::getNodeByName($name);
		if (!is_object($folder)) {
			$filesystem = new \Concrete\Core\File\Filesystem();
			return $filesystem->addFolder($parentFolder, $name);
		} else {
			$folder;
		}
	}

	public static function installThumbnailType($handle, $name, $width, $height = NULL)
	{
		$thumbnailType = Type::getByHandle($handle);
		if (!is_object($thumbnailType)) {
			$type = new \Concrete\Core\Entity\File\Image\Thumbnail\Type\Type();
			$type->setHandle($handle);
			$type->setName($name);
			$type->setWidth($width);
			if ($height) {
				$type->setHeight($height);
			}

			$type->save();

			return $type;
		}
	}

	public static function installTopicTree($name, $topics = [])
	{
		// Make Topic Tree if not exist
		$topicTree = TopicTree::getByName($name);
		if (!is_object($topicTree)) {
			$topicTree     = TopicTree::add($name);
			$topicCategory = TreeNode::getByID($topicTree->getRootTreeNodeObject()->getTreeNodeID());
			if (count($topics)) {
				foreach ($topics as $topicName) {
					TopicTreeNode::add($topicName, $topicCategory);
				}
			}
		}

		return $topicTree;
	}
}
