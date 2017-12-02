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
        $imagePath = $tmpFolder . $tmpFilename;

        $img = str_replace('data:image/png;base64,', '', $thumbnail['croppedImage']);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $success = file_put_contents($imagePath, $data);

        // $formImage = imagecreatefromstring($data);

        // Get image facade and open image
        $imagine = $this->app->make(Image::getFacadeAccessor());
        $image = $imagine->open($imagePath);

        // Resize image
        $image = $image->resize(new Box($C5dkConfig->blog_thumbnail_width, $C5dkConfig->blog_thumbnail_height));

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

    // public function saveThumbnail($C5dkBlog)
    // {
    //     // Get helper objects
    //     $fh = $this->app->make('helper/file');
    //     $fi = new FileImporter();

    //     // Get C5dk Objects
    //     $C5dkConfig = new C5dkConfig;
    //     $C5dkUser   = new C5dkUser;
    //     $uID        = $C5dkUser->getUserID();

    //     $tmpFolder  = $fh->getTemporaryDirectory();
    //     $filename   = (microtime(true) * 10000) . '.jpg';

    //     // Get image facade and open image
    //     $imagine = $this->app->make(Image::getFacadeAccessor());
    //     $image   = $imagine->open($_FILES['croppedImage']['tmp_name']);

    //     // Resize image
    //     $image = $image->resize(new Box($this->thumbnail_width, $this->thumbnail_height));

    //     // Save image as .jpg
    //     $image->save($tmpFolder . $filename, ['jpeg_quality' => 80]);

    //     // Import thumbnail into the File Manager
    //     $fv = $fi->import(
    //         $tmpFolder . $filename,
    //         'C5DK_BLOG_uID-' . $C5dkUser->getUserID() . '_Thumb_cID-' . $C5dkBlog->getCollectionID() . '.jpg',
    //         FileFolder::getNodeByName('Thumbs')
    //     );

    //     if (is_object($fv)) {
    //         // Create and get FileSet if not exist and add file to the set
    //         $fs  = FileSet::createAndGetSet('C5DK_BLOG_uID-' . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
    //         $fsf = $fs->addFileToSet($fv);

    //         // Delete tmp file
    //         $fs = new \Illuminate\Filesystem\Filesystem();
    //         $fs->delete($tmpFolder . $filename);

    //         // Return the File Object
    //         return $fv->getFile();
    //     }
    // }

    // public function save($blogID)
    // {
    //     // Get helper objects
    //     $jh = $this->app->make('helper/json');

    //     // Set C5dk Objects
    //     $C5dkUser = new C5dkUser;

    //     // Get or create the C5dkNews Object
    //     $C5dkBlog = ($this->post('mode') == C5DK_BLOG_MODE_CREATE) ? new C5dkBlog : C5dkBlog::getByID($blogID);

    //     // Setup blog and save it
    //     $C5dkBlog->setPropertiesFromArray([
    //         'rootID' => $this->post('rootID'),
    //         'userID' => $C5dkUser->getUserID(),
    //         'title' => $this->post('title'),
    //         'description' => $this->post('description'),
    //         'content' => $this->post('c5dk_blog_content'),
    //         'topicAttributeID' => $this->post('topicAttributeID')
    //     ]);
    //     $C5dkBlog = $C5dkBlog->save($this->post('mode'));
    //     $C5dkBlog = C5dkBlog::getByID($C5dkBlog->getCollectionID());

    //     $thumbnail = $this->post('thumbnail');

    //     if ($thumbnail['id'] == -1) {
    //         // Remove old thumbnail
    //         $C5dkBlog->deleteThumbnail();
    //     }

    //     if ($thumbnail['id'] > 0 && isset($_FILES['croppedImage'])) {
    //         // Remove old thumbnail
    //         $C5dkBlog->deleteThumbnail();

    //         // Can first save the thumbnail now, because we needed to save the page first.
    //         $thumbnail = $this->saveThumbnail($C5dkBlog);

    //         if (is_object($thumbnail)) {
    //             $cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
    //             $C5dkBlog     = $C5dkBlog->getVersionToModify();
    //             $C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
    //             $C5dkBlog->refreshCache();
    //             $C5dkBlog->getVersionObject()->approve();
    //         }
    //     }

    //     $data = [
    //         'status' => true,
    //         'redirectLink' => $C5dkBlog->getCollectionPath()
    //     ];

    //     header('Content-type: application/json');
    //     echo $jh->encode($data);

    //     exit;
    // }
}