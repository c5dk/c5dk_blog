<?php
namespace Concrete\Package\C5dkBlog\Src;

use Core;
use Database;
use Package;

use Page;
use PageList;
use SinglePage;

use BlockTypeSet;
use BlockType;

use AttributeSet;
use UserAttributeKey;
use CollectionAttributeKey;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Core\File\Image\Thumbnail\Type\Type;

// use Events;
// use View;

// use Route;

// use AssetList;
// use Concrete\Core\Editor\Plugin;

// use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;
// use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkAjax as C5dkAjax;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkInstaller {

	private function setupConfig($pkg) {

		$config = $pkg->getConfig();
		if (!$config->get('blog_thumbnail_width')) { $config->save('c5dk_blog.blog_thumbnail_width',			360); }
		if (!$config->get('blog_thumbnail_height')) { $config->save('c5dk_blog.blog_thumbnail_height',			360); }
		if (!$config->get('blog_picture_width')) { $config->save('c5dk_blog.blog_picture_width',				1200); }

		if (!$config->get('blog_headline_size')) { $config->save('c5dk_blog.blog_headline_size',				12); }
		if (!$config->get('blog_headline_color')) { $config->save('c5dk_blog.blog_headline_color',				'#AAAAAA'); }
		if (!$config->get('blog_headline_margin')) { $config->save('c5dk_blog.blog_headline_margin',			'5px 0'); }
		if (!$config->get('blog_headline_icon_color')) { $config->save('c5dk_blog.blog_headline_icon_color',	'#1685D4'); }
	}





	public static function installUserAttribute($type, $options, $pkg = false) {
		$uak = UserAttributeKey::getByHandle($options['akHandle']);
		if (!is_object($uak)) {
			$uak = UserAttributeKey::add($type, $options, $pkg);
		}
		return $uak;
	}

	public static function installSinglePage($path, $name, $description, $pkg = null, $attributes = array()) {
		$page = Page::getByPath($path);
		if (!is_object($page) || $page->isError()) {
			$page = SinglePage::add($path, $pkg);
			$page->update(array('cName' => $name, 'cDescription' => $description));
			foreach ($attributes as $handle => $value) {
				$page->setAttribute($handle, $value);
			}
		}
		return $page;
	}

	public static function installCollectionAttributeSet($handle, $name) {
		$bas = AttributeSet::getByHandle($handle);
		if (!is_object($bas)){
			$cakc = AttributeKeyCategory::getByHandle('collection');
			$cakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);
			$bas = $cakc->addSet($handle, $name);
		}
		return $bas;
	}

	public static function installCollectionAttributeKey($type, $options, $pkg = false, $set = null) {
		$cak = CollectionAttributeKey::getByHandle($options['akHandle']);
		if (!is_object($cak)) {
			$cak = CollectionAttributeKey::add($type, $options, $pkg);
			if ($set) { $cak->setAttributeSet($set); }
		}
		return $cak;
	}

	public static function installUserAttributeKey($type, $handle, $name, $options) {
	}

	public static function installBlockTypeSet($handle, $name, $pkg = false) {
		$bts = BlockTypeSet::getByHandle($handle);
		if (!is_object($bts) || $bts->isError()) {
			return BlockTypeSet::add($handle, $name, $pkg);
		}
	}

	public static function installBlockType($handle, $pkg = false) {
		$bt = BlockType::getByHandle($handle);
		if (!is_object($bt)) {
			return BlockType::installBlockType($handle, $pkg);
		}
	}

	public static function installFileFolder($parentFolder, $name) {

		if ($parentFolder == '-root-') {
			$filesystem = new \Concrete\Core\File\Filesystem();
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

	public static function installThumbnailType($handle, $name, $width, $height = null) {
		$thumbnailType = Type::getByHandle($handle);
		if (!is_object($thumbnailType)) {
			$type = new \Concrete\Core\Entity\File\Image\Thumbnail\Type\Type();
			$type->setHandle($handle);
			$type->setName($name);
			$type->setWidth($width);
			if ($height) { $type->setHeight($height); }
			$type->save();

			return $type;
		}
	}

}