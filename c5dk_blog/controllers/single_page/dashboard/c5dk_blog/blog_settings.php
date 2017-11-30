<?php

namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use Core;
use Package;
use Session;
use Group;
use GroupList;
use PermissionKey;
use File;
use Concrete\Core\Tree\Node\Type\FileFolder as FileFolder;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Permission\Access\Access as PermissionAccess;
use Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
use C5dk\Blog\C5dkConfig as C5dkConfig;
use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogSettings extends DashboardPageController
{
    public function view()
    {
        // Set the C5dk object
        $C5dkConfig = new C5dkConfig;
        $this->set('C5dkConfig', $C5dkConfig);
        // $this->set('sitemapGroups', $this->getSitemapGroups());
        $this->set('pk', PermissionKey::getByHandle('access_sitemap'));

        // Require Assets
        $this->requireAsset('css', 'c5dk_blog_css');
        $this->requireAsset('core/app');
        $this->requireAsset('select2');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('javascript', 'c5dkBlog/modal');

        // Set Service
        $fID              = $C5dkConfig->blog_default_thumbnail_id;
        $defThumbnail     = $fID ? File::getByID($fID) : null;
        $this->set('ThumbnailCropper', new ThumbnailCropper($defThumbnail, null, 'settings'));
        // $test = $this->app->build('C5dk\Blog\Service\Test');

        // Set helpers
        $this->set('form', $this->app->make('helper/form'));
        $this->set('colorPicker', $this->app->make('helper/form/color'));

        // Set group list
        $this->set('groupList', $this->getAllGroups());

        // Should we show a message?
        $message = Session::get('c5dk_blog_message');
        if ($message) {
            Session::set('c5dk_blog_message', '');
            $this->set('message', $message);
        }
    }

    public function save()
    {
        $pkg    = Package::getByHandle('c5dk_blog');
        $config = $pkg->getConfig();

        // Settings
        $config->save('c5dk_blog.blog_title_editable', ($this->post('blog_title_editable')) ? $this->post('blog_title_editable') : 0);
        $config->save('c5dk_blog.blog_form_slidein', ($this->post('blog_form_slidein')) ? $this->post('blog_form_slidein') : 0);

        // Images & Thumbnails
        $config->save('c5dk_blog.blog_picture_width', $this->post('blog_picture_width'));
        $config->save('c5dk_blog.blog_picture_height', $this->post('blog_picture_height'));
        $config->save('c5dk_blog.blog_thumbnail_width', $this->post('blog_thumbnail_width'));
        $config->save('c5dk_blog.blog_thumbnail_height', $this->post('blog_thumbnail_height'));
        $config->save('c5dk_blog.blog_default_thumbnail_id', $this->saveThumbnail($this->post('thumbnail')));
        $config->save('c5dk_blog.blog_cropper_def_bgcolor', $this->post('blog_cropper_def_bgcolor'));

        // Styling
        $config->save('c5dk_blog.blog_headline_size', $this->post('blog_headline_size'));
        $config->save('c5dk_blog.blog_headline_color', $this->post('blog_headline_color'));
        $config->save('c5dk_blog.blog_headline_margin', $this->post('blog_headline_margin'));
        $config->save('c5dk_blog.blog_headline_icon_color', $this->post('blog_headline_icon_color'));

        // Editor
        $config->save('c5dk_blog.blog_plugin_youtube', $this->post('blog_plugin_youtube'));
        $config->save('c5dk_blog.blog_plugin_sitemap', $this->post('blog_plugin_sitemap'));
        $config->save('c5dk_blog.blog_format_h1', $this->post('blog_format_h1'));
        $config->save('c5dk_blog.blog_format_h2', $this->post('blog_format_h2'));
        $config->save('c5dk_blog.blog_format_h3', $this->post('blog_format_h3'));
        $config->save('c5dk_blog.blog_format_h4', $this->post('blog_format_h4'));
        $config->save('c5dk_blog.blog_format_pre', $this->post('blog_format_pre'));

        // Set Sitemap permissions
        if ($this->post('blog_plugin_sitemap')) {
            $pk   = PermissionKey::getByHandle('access_sitemap');
            $paID = $this->post('pkID')[$pk->getPermissionKeyID()];
            $pt   = $pk->getPermissionAssignmentObject();
            $pt->clearPermissionAssignment();
            if ($paID > 0) {
                $pa = PermissionAccess::getByID($paID, $pk);
                if (is_object($pa)) {
                    $pt->assignPermissionAccess($pa);
                }
            }
        }

        Session::set('c5dk_blog_message', t('Settings saved.'));

        // Send ok status back to browser
        $jh = $this->app->make('helper/json');
        echo $jh->encode((object) array(
            'status' => true,
            'type' => 'admin_form_company',
            'post' => $this->post(),
            'html' => array(
                'admin_form_company' => $admin_form_company
            )
        ));
        exit;
        // $this->redirect('/dashboard/c5dk_blog/blog_settings');
    }

    // public function getSitemapGroups() {

    // 	$groups = array();

    // 	$pk = PermissionKey::getByHandle('access_sitemap');
    // 	$pa = $pk->getPermissionAccessObject();
    // 	$assignments = $pa->getAccessListItems(PermissionKey::ACCESS_TYPE_ALL);
    // 	foreach ($assignments as $assignment) {
    // 		$entity = $assignment->getAccessEntityObject();
    // 		$title = $entity->getAccessEntityLabel();
    // 		$group = Group::getByName($entity->getAccessEntityLabel());
    // 		$groups[] = $group->getGroupID();
    // 	}

    // 	return $groups;
    // }

    // public function setSitemapGroups($groups)
    // {
    //     $pk = PermissionKey::getByHandle('access_sitemap');
    //     $pa = PermissionAccess::create($pk);
    //     $pt = $pk->getPermissionAssignmentObject();

    //     foreach ($groups as $groupID) {
    //         $group       = Group::getByID($groupID);
    //         $groupEntity = GroupPermissionAccessEntity::getOrCreate($group);

    //         $pa->addListItem($groupEntity);
    //     }
    //     $pt->assignPermissionAccess($pa);

    //     foreach ($pa->getAccessListItems() as $item) {
    //         \Log::addEntry($item->getAccessEntityObject()->getAccessEntityLabel());
    //     }
    // }

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

    public function saveThumbnail($thumbnail)
    {
        if (isset($_FILES['croppedImage'])) {
            
            // Delete old thumbnail before saving the new
            $C5dkConfig       = new C5dkConfig;
            if ($id = $C5dkConfig->blog_default_thumbnail_id) {
                $oldThumbnail     = File::getByID($C5dkConfig->blog_default_thumbnail_id);
                $oldThumbnail->delete();
            }

            // Get on with saving the new thumbnail
            $fileName         = 'C5DK_BLOG_Default_Thumbnail.jpg';
            $fileFolder       = FileFolder::getNodeByName('Thumbs');

            $ThumbnailCropper =  new ThumbnailCropper;

            return $ThumbnailCropper->saveForm($thumbnail, $fileName, $fileFolder, true)->getFileID();
        } else {
            return 0;
        }
    }
}
