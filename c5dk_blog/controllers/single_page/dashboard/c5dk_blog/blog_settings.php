<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use Core;
use Package;
use Database;
use Group;
use GroupList;
use \Concrete\Core\Page\Controller\DashboardPageController;

use PermissionKey;
use TaskPermission;
use Concrete\Core\Permission\Access\Access as PermissionAccess;
use Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;

use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");
class BlogSettings extends DashboardPageController {

	public function view(){

		// Set the C5dk object
		$C5dkConfig = new C5dkConfig;
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('sitemapGroups', $this->getSitemapGroups());
		$this->set('pk', PermissionKey::getByHandle('access_sitemap'));

		// Require Assets
		$this->requireAsset('core/app');
		$this->requireAsset('select2');

		// Set helpers
		$this->set('form', $this->app->make('helper/form'));
		$this->set('colorPicker', $this->app->make('helper/form/color'));

		// Set group list
		$this->set('groupList', $this->getAllGroups());
	}

	public function save() {

		$pkg = Package::getByHandle('c5dk_blog');
		$config = $pkg->getConfig();

		// Settings
		$config->save('c5dk_blog.blog_title_editable',	($this->post('blog_title_editable'))? $this->post('blog_title_editable') : 0);
		$config->save('c5dk_blog.blog_form_slidein',	($this->post('blog_form_slidein'))? $this->post('blog_form_slidein') : 0);

		// Images & Thumbnails
		$config->save('c5dk_blog.blog_picture_width',		$this->post('blog_picture_width'));
		$config->save('c5dk_blog.blog_picture_height',		$this->post('blog_picture_height'));
		$config->save('c5dk_blog.blog_thumbnail_width',		$this->post('blog_thumbnail_width'));
		$config->save('c5dk_blog.blog_thumbnail_height',	$this->post('blog_thumbnail_height'));
		$config->save('c5dk_blog.blog_cropper_def_bgcolor',	$this->post('blog_cropper_def_bgcolor'));

		// Styling
		$config->save('c5dk_blog.blog_headline_size',		$this->post('blog_headline_size'));
		$config->save('c5dk_blog.blog_headline_color',		$this->post('blog_headline_color'));
		$config->save('c5dk_blog.blog_headline_margin',		$this->post('blog_headline_margin'));
		$config->save('c5dk_blog.blog_headline_icon_color',	$this->post('blog_headline_icon_color'));

		// Editor
		$config->save('c5dk_blog.blog_plugin_youtube',			$this->post('blog_plugin_youtube'));
		$config->save('c5dk_blog.blog_plugin_sitemap',			$this->post('blog_plugin_sitemap'));
		$config->save('c5dk_blog.blog_format_h1',				$this->post('blog_format_h1'));
		$config->save('c5dk_blog.blog_format_h2',				$this->post('blog_format_h2'));
		$config->save('c5dk_blog.blog_format_h3',				$this->post('blog_format_h3'));
		$config->save('c5dk_blog.blog_format_h4',				$this->post('blog_format_h4'));
		$config->save('c5dk_blog.blog_format_pre',				$this->post('blog_format_pre'));

		// Set Sitemap permissions
		if ($this->post('blog_plugin_sitemap')) {
			$pk = PermissionKey::getByHandle('access_sitemap');
			$paID = $this->post('pkID')[$pk->getPermissionKeyID()];
			$pt = $pk->getPermissionAssignmentObject();
			$pt->clearPermissionAssignment();
			if ($paID > 0) {
				$pa = PermissionAccess::getByID($paID, $pk);
				if (is_object($pa)) {
					$pt->assignPermissionAccess($pa);
				}
			}
		}

		$this->set('message', t('Settings saved.'));

		$this->view();
	}

	public function getSitemapGroups() {

		$groups = array();

		$pk = PermissionKey::getByHandle('access_sitemap');
		$pa = $pk->getPermissionAccessObject();
		$assignments = $pa->getAccessListItems(PermissionKey::ACCESS_TYPE_ALL);
		foreach ($assignments as $assignment) {
			$entity = $assignment->getAccessEntityObject();
			$title = $entity->getAccessEntityLabel();
			$group = Group::getByName($entity->getAccessEntityLabel());
			$groups[] = $group->getGroupID();
		}

		return $groups;
	}

	public function setSitemapGroups($groups) {
		$pk = PermissionKey::getByHandle('access_sitemap');
		$pa = PermissionAccess::create($pk);
		$pt = $pk->getPermissionAssignmentObject();

		foreach ($groups as $groupID) {
			$group = Group::getByID($groupID);
			$groupEntity = GroupPermissionAccessEntity::getOrCreate($group);

			$pa->addListItem($groupEntity);
		}
		$pt->assignPermissionAccess($pa);

		foreach ($pa->getAccessListItems() as $item) {
			\Log::addEntry($item->getAccessEntityObject()->getAccessEntityLabel());
		}
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
}
