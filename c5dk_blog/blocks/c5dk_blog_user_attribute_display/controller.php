<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogUserAttributeDisplay;

use Core;
use Page;
use User;
use File;
use UserInfo;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use Stacks;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController
{
	protected $btTable            = 'btC5dkBlogUserAttributeDisplay';
	protected $btDefaultSet       = 'c5dk_blog';
	protected $btCacheBlockRecord = FALSE;

	public $dateFormat   = "Y-m-d H:i";
	public $avatarHeight = 100;
	public $avatarWidth  = 100;

	public function view()
	{
		$templateHandle = $this->getTemplateHandle();
		if (in_array($templateHandle, array('date_time', 'boolean'))) {
			$this->render('templates/' . $templateHandle);
		}
	}

	public function getBlockTypeName() {
		return t("C5DK BLog User Attribute Display");
	}

	public function getBlockTypeDescription() {
		return t("Displays the value of an user attribute.");
	}

	public function getContent() {

		$c       = Page::getCurrentPage();
		$content = "";

		$uID = $c->getAttribute('c5dk_blog_author_id');
		if ($uID) {
			$u = User::getByUserID($uID);
		} else {
			$u = User::getByUserID($c->getCollectionUserID());
		}

		if (is_object($u) || $u->uID) {

			$ui = UserInfo::getByID($u->getUserID());

			if (is_object($ui)) {
				switch ($this->attributeHandle) {
					case "userName":
						$content = h($u->getUserName());
						break;
					case "userEmail":
						$content = h($ui->getUserEmail());
						break;
					case "userAvatar":
						$content = $ui->getUserAvatar();
						$content = "<img src=\"{$content->getPath()}\" width=\"{$this->avatarWidth}\" height=\"{$this->avatarHeight}\" alt=\"\" />";
						break;
					default:
						$content = $ui->getAttribute($this->attributeHandle);
						if (is_object($content) && $content instanceof File) {
							$im      = Core::make('helper/image');
							$thumb   = $im->getThumbnail(
								$content,
								$this->avatarWidth,
								$this->avatarHeight
							);
							$content = "<img src=\"{$thumb->src}\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
						} else {
							if (!is_scalar($content) && (!is_object($content) || !method_exists($content, '__toString'))) {
								$content = $ui->getAttribute($this->attributeHandle, 'displaySanitized');
							}
						}
						break;
				}
			}
		}

		$is_stack = $c->getController() instanceof Stacks;
		if (!strlen(trim(strip_tags($content))) && ($c->isMasterCollection() || $is_stack)) {
			$content = $this->getPlaceHolderText($this->attributeHandle);
		}

		return $content;
	}

	public function getPlaceHolderText($handle) {
		$userValues = $this->getAvailableUserValues();
		if (in_array($handle, array_keys($userValues))) {
			$placeHolder = $userValues[$handle];
		} else {
			$attributeKey = UserAttributeKey::getByHandle($handle);
			if (is_object($attributeKey)) {
				$placeHolder = $attributeKey->getAttributeKeyName();
			}
		}

		return "[" . $placeHolder . "]";
	}

	public function getTitle() {
		return (strlen($this->attributeTitleText) ? $this->attributeTitleText . " " : "");
	}

	public function getAvailableUserValues() {
		return array(
			'userName' => t('User Name'),
			'userEmail' => t('User Email'),
			'userAvatar' => t('User Avatar')
		);
	}

	public function getAvailableAttributes() {
		return UserAttributeKey::getList();
	}

	protected function getTemplateHandle() {
		if (in_array($this->attributeHandle, array_keys($this->getAvailableUserValues()))) {
			switch ($this->attributeHandle) {
				case "rpv_pageDateCreated":
				case 'rpv_pageDateLastModified':
				case "rpv_pageDatePublic":
					$templateHandle = 'date_time';
					break;
			}
		} else {
			$attributeKey = UserAttributeKey::getByHandle($this->attributeHandle);
			if (is_object($attributeKey)) {
				$attributeType  = $attributeKey->getAttributeType();
				$templateHandle = $attributeType->getAttributeTypeHandle();
			}
		}

		return $templateHandle;
	}

	public function getOpenTag() {
		$tag = "";
		if (strlen($this->displayTag)) {
			$tag = "<" . $this->displayTag . " class=\"ccm-block-page-attribute-display-wrapper\">";
		}

		return $tag;
	}

	public function getCloseTag() {
		$tag = "";
		if (strlen($this->displayTag)) {
			$tag = "</" . $this->displayTag . ">";
		}

		return $tag;
	}
}
