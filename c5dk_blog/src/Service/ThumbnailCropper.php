<?php

namespace C5dk\Blog\Service;

use View;
use File;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Entity\File\File as ThumbnailFile;
use Concrete\Core\Controller\Controller;
use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die('Access Denied.');

class ThumbnailCropper extends Controller
{
	protected $app;
	public $config;

	protected $type;
	protected $onSelectCallback = 'c5dk.blog.service.thumbnailCropper.select';
	protected $onSaveCallback   = 'c5dk.blog.service.thumbnailCropper.save';

	protected $thumbnail_width  = 360;
	protected $thumbnail_height = 300;

	protected $thumbnail;
	protected $defaultThumbnail;

	public function __construct($thumbnail = NULL, $defaultThumbnail = NULL, $type = 'post')
	{
		$this->app    = Application::getFacadeApplication();
		$this->config = new C5dkConfig;

		$this->type             = $type;
		$this->thumbnail        = $thumbnail instanceof ThumbnailFile ? $thumbnail : NULL;
		$this->defaultThumbnail = $defaultThumbnail instanceof ThumbnailFile ? $defaultThumbnail : NULL;

		$this->thumbnail_width  = $this->config->blog_thumbnail_width;
		$this->thumbnail_height = $this->config->blog_thumbnail_height;

		$this->requireAsset('javascript', 'cropper');
		$this->requireAsset('javascript', 'thumbnail_cropper/main');
		$this->requireAsset('core/file-manager');
		$this->requireAsset('css', 'cropper');
	}

	public function output()
	{
		return View::element('service/thumbnail_cropper/view', ['Cropper' => $this, 'form' => $this->app->make('helper/form')], 'c5dk_blog');
	}

	public function getThumbnailID()
	{
		return $this->thumbnail instanceof ThumbnailFile ? $this->thumbnail->getFileID() : 0;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setOnSelectCallback($onSelectCallback)
	{
		$this->onSelectCallback = $onSelectCallback;
	}

	public function getOnSelectCallback()
	{
		return $this->onSelectCallback;
	}

	public function setOnSaveCallback($onSaveCallback)
	{
		$this->onSaveCallback = $onSaveCallback;
	}

	public function getOnSaveCallback()
	{
		return $this->onSaveCallback;
	}

	public function hasDefaultThumbnail()
	{
		return is_object($this->defaultThumbnail) ? TRUE : FALSE;
	}

	public function getDefaultThumbnail()
	{
		return $this->defaultThumbnail;
	}
}
