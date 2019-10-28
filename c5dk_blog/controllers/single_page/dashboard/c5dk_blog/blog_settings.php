<?php

namespace Concrete\Package\C5dkBlog\Controller\SinglePage\Dashboard\C5dkBlog;

use User;
use Core;
use Package;
use Session;
use Group;
use GroupList;
use PermissionKey;
use File;
use FileImporter;
use FileSet;
use Image;
use Imagine\Image\Box;
use Concrete\Core\Tree\Node\Type\FileFolder as FileFolder;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Permission\Access\Access as PermissionAccess;
use C5dk\Blog\C5dkConfig as C5dkConfig;
use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogSettings extends DashboardPageController
{
	private $config;

	public function view()
	{
		// Set the C5dk object
		$C5dkConfig = new C5dkConfig;
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('pk', PermissionKey::getByHandle('access_sitemap'));

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('core/app');
		$this->requireAsset('select2');
		$this->requireAsset('javascript', 'c5dkBlog/modal');

		// Set Service
		$fID          = $C5dkConfig->blog_default_thumbnail_id;
		$defThumbnail = $fID ? File::getByID($fID) : NULL;
		$this->set('ThumbnailCropper', new ThumbnailCropper($defThumbnail, NULL, 'settings'));

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
		$pkg          = Package::getByHandle('c5dk_blog');
		$this->config = $pkg->getConfig();

		// Settings - Others
		$this->config->save('c5dk_blog.blog_title_editable', ($this->post('blog_title_editable')) ? $this->post('blog_title_editable') : 0);
		$this->config->save('c5dk_blog.blog_form_slidein', ($this->post('blog_form_slidein')) ? $this->post('blog_form_slidein') : 0);

		// Settings - Editor Manager
		$this->config->save('c5dk_blog.blog_manager_items_per_page', ($this->post('blog_manager_items_per_page')) ? $this->post('blog_manager_items_per_page') : 10);

		// Images & Thumbnails
		$this->config->save('c5dk_blog.blog_picture_width', ($this->post('blog_picture_width')) ? $this->post('blog_picture_width') : 1200);
		$this->config->save('c5dk_blog.blog_picture_height', ($this->post('blog_picture_height')) ? $this->post('blog_picture_height') : 800);
		$this->config->save('c5dk_blog.blog_thumbnail_width', ($this->post('blog_thumbnail_width')) ? $this->post('blog_thumbnail_width') : 360);
		$this->config->save('c5dk_blog.blog_thumbnail_height', ($this->post('blog_thumbnail_height')) ? $this->post('blog_thumbnail_height') : 360);
		$this->config->save('c5dk_blog.blog_default_thumbnail_id', $this->saveThumbnail($this->post('thumbnail')));
		$this->config->save('c5dk_blog.blog_cropper_def_bgcolor', ($this->post('blog_cropper_def_bgcolor')) ? $this->post('blog_cropper_def_bgcolor') : '#FFFFFF');

		// Styling
		$this->config->save('c5dk_blog.blog_headline_size', ($this->post('blog_headline_size')) ? $this->post('blog_headline_size') : 12);
		$this->config->save('c5dk_blog.blog_headline_color', ($this->post('blog_headline_color')) ? $this->post('blog_headline_color') : '#AAAAAA');
		$this->config->save('c5dk_blog.blog_headline_margin', ($this->post('blog_headline_margin')) ? $this->post('blog_headline_margin') : '5px 0');
		$this->config->save('c5dk_blog.blog_headline_icon_color', ($this->post('blog_headline_icon_color')) ? $this->post('blog_headline_icon_color') : '#1685D4');

		// Editor
		$this->config->save('c5dk_blog.blog_plugin_youtube', ($this->post('blog_plugin_youtube')) ? $this->post('blog_plugin_youtube') : 0);
		$this->config->save('c5dk_blog.blog_plugin_sitemap', ($this->post('blog_plugin_sitemap')) ? $this->post('blog_plugin_sitemap') : 0);
		$this->config->save('c5dk_blog.image_manager_extension', ($this->post('image_manager_extension')) ? $this->post('image_manager_extension') : 'jpg');
		$this->config->save('c5dk_blog.file_manager_extension', ($this->post('file_manager_extension')) ? $this->post('file_manager_extension') : 'txt, pdf');
		$this->config->save('c5dk_blog.blog_format_h1', ($this->post('blog_format_h1')) ? $this->post('blog_format_h1') : 0);
		$this->config->save('c5dk_blog.blog_format_h2', ($this->post('blog_format_h2')) ? $this->post('blog_format_h2') : 0);
		$this->config->save('c5dk_blog.blog_format_h3', ($this->post('blog_format_h3')) ? $this->post('blog_format_h3') : 0);
		$this->config->save('c5dk_blog.blog_format_h4', ($this->post('blog_format_h4')) ? $this->post('blog_format_h4') : 0);
		$this->config->save('c5dk_blog.blog_format_pre', ($this->post('blog_format_pre')) ? $this->post('blog_format_pre') : 0);

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

		$this->redirect('/dashboard/c5dk_blog/blog_settings');
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

	public function saveThumbnail($thumbnail)
	{
		if ($thumbnail['id'] == $this->config->get('c5dk_blog.blog_default_thumbnail_id')) {
			return $thumbnail['id'];
		}

		// Init objects
		$C5dkConfig = new C5dkConfig;

		// Init Helpers
		$fh = $this->app->make('helper/file');
		$fs = new \Illuminate\Filesystem\Filesystem();

		// Init variables
		$uID          = (new User)->getUserID();
		$fileName     = 'C5DK_BLOG_Default_Thumbnail.jpg';
		$tmpFolder    = $fh->getTemporaryDirectory() . '/';
		$tmpImagePath = $tmpFolder . $uID . '_' . $fileName;

		// Get old thumbnail
		$oldThumbnail = $C5dkConfig->blog_default_thumbnail_id ? File::getByID($C5dkConfig->blog_default_thumbnail_id) : 0;

		// Delete old thumbnail before saving the new or if the user removed it altogether
		if ($thumbnail['id'] == -1) {
			$oldThumbnail->delete();
			$this->config->save('c5dk_blog.blog_default_thumbnail_id', 0);
		}

		// If we don't have an id, we don't need to save anything
		if ($thumbnail['id'] < 1) {
			return 0;
		}

		// So now we only need to see if we have a new thumbnail or we keep the old one
		if (strlen($thumbnail['croppedImage'])) {
			$fileservice = \Core::make('helper/file');

			// Get on with saving the new thumbnail
			$img  = str_replace('data:image/png;base64,', '', $thumbnail['croppedImage']);
			$img  = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$fs->put($tmpImagePath, $data);

			// Convert to .jpg
			$image = Image::open($tmpImagePath);
			$image->save($tmpImagePath, ['jpeg_quality' => 80]);

			if ($oldThumbnail) {
				$fv = $oldThumbnail->getVersionToModify(TRUE);
				$fv->updateContents($image->get('jpg'));
			} else {
				// Import thumbnail into the File Manager
				$fi = new FileImporter();
				$fv = $fi->import(
					$tmpImagePath,
					$fileName,
					FileFolder::getNodeByName('Thumbs')
				);
			}

			// Delete tmp file
			$fs->delete($tmpImagePath);

			return $fv->getFileID();
		} else {
			return $thumbnail['id'];
		}
	}

	public function getFileTypes($type, $withFullStop = false)
	{

	}
}
