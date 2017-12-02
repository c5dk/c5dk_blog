<?php

namespace C5dk\Blog\Service;

use View;
use Concrete\Core\Support\Facade\Application;
use File;
use FileImporter;
use FileSet;
use Concrete\Core\Tree\Node\Type\FileFolder as FileFolder;
use Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Imagine\Filter\Basic\Autorotate;
use Imagine\Filter\Transformation;
use Imagine\Image\Metadata\ExifMetadataReader;
use Concrete\Core\Entity\File\File as ThumbnailFile;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\Controller\Controller;
use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die('Access Denied.');

class ThumbnailCropper extends Controller
{
    protected $app;

    public $config;
    protected $type;
    protected $thumbnail_width  = 360;
    protected $thumbnail_height = 300;

    protected $thumbnail;
    protected $defaultThumbnail;

    public function __construct($thumbnail = null, $defaultThumbnail = null, $type = 'post')
    {
        $this->app    = Application::getFacadeApplication();
        $this->config = new C5dkConfig;

        $this->type             = $type;
        $this->thumbnail        = $thumbnail instanceof ThumbnailFile ? $thumbnail : null;
        $this->defaultThumbnail = $defaultThumbnail instanceof ThumbnailFile ? $defaultThumbnail : null;

        $this->thumbnail_width  = $this->config->blog_thumbnail_width;
        $this->thumbnail_height = $this->config->blog_thumbnail_height;

        $this->requireAsset('javascript', 'cropper');
        $this->requireAsset('javascript', 'thumbnail_cropper/main');
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

    public function getOnSaveCallback()
    {
        if ($this->type == 'post') {
            return 'c5dk.blog.post.blog.save';
        } else {
            return 'c5dk.blog.service.thumbnailCropper.save';
        }
    }

    public function saveForm($thumbnail, $fileName, $fileFolder, $fileSet = null)
    {
        // Get helper objects
        $fh = $this->app->make('helper/file');
        $fi = new FileImporter();

        // Get C5dk Objects
        $C5dkConfig = $this->config ? $this->config : new C5dkConfig;

        $tmpFolder   = $fh->getTemporaryDirectory() . '/';
        $tmpFilename = (microtime(true) * 10000) . '.jpg';
        $imagePath   = $tmpFolder . $tmpFilename;

        $img     = str_replace('data:image/png;base64,', '', $thumbnail['croppedImage']);
        $img     = str_replace(' ', '+', $img);
        $data    = base64_decode($img);
        $success = file_put_contents($imagePath, $data);

        // Get image facade and open image
        $imagine = $this->app->make(Image::getFacadeAccessor());
        $image   = $imagine->open($imagePath);

        // Resize image (Chg: we now do it in the browser, but needs testing)
        // $image = $image->resize(new Box($C5dkConfig->blog_thumbnail_width, $C5dkConfig->blog_thumbnail_height));

        // Save image as .jpg
        $image->save($imagePath, ['jpeg_quality' => 80]);

        // Import thumbnail into the File Manager
        $fv = $fi->import(
            $imagePath,
            $fileName,
            $fileFolder
        );

        if (is_object($fv) && $fileSet instanceof FileSet) {
            $fileSet->addFileToSet($fv);
        }

        // Delete tmp file
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->delete($imagePath);

        // Return the File Object
        return $fv->getFile();
    }
}
