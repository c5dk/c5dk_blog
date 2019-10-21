<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php
// $form = Core::make('helper/form');
$ush = Core::make('helper/form/user_selector');

print Core::make('helper/concrete/ui')->tabs([
	['add', t('Add'), TRUE],
	['options', t('Options')],
]);
?>

<!-- Tab: Add -->
<div id="ccm-tab-content-add" class="ccm-tab-content">
	<div class="form-group">
		<label class="control-label"><?= t('Property to Display:')?></label>

		<select name="attributeHandle" class="form-control">
		<optgroup label="<?= t('User Values');?>">
		<?php
		$corePageValues = $this->controller->getAvailableUserValues();
		foreach (array_keys($corePageValues) as $cpv) {
			echo "<option value=\"".$cpv."\" ".($cpv == $this->controller->attributeHandle ? "selected=\"selected\"" : "").">".
			$corePageValues[$cpv]."</option>\n";
		}
		?>
		</optgroup>
		<optgroup label="<?= t('User Attributes');?>">
		<?php
		$aks = $this->controller->getAvailableAttributes();
		foreach ($aks as $ak) {
			echo "<option value=\"".$ak->getAttributeKeyHandle()."\" ".($ak->getAttributeKeyHandle() == $this->controller->attributeHandle ? "selected=\"selected\"" : "").">".
			$ak->getAttributeKeyDisplayName()."</option>\n";
		}
		?>
		</optgroup>
		</select>
	</div>
	<div class="form-group">
		<label class="control-label"><?= t('Title Text')?></label>
		<input type="text" class="form-control" name="attributeTitleText" value="<?= $this->controller->attributeTitleText ?>"/>
	</div>
</div>

<!-- Tab: Options -->
<div class="ccm-tab-content" id="ccm-tab-content-options">
	<div class="form-group">
		<label class="control-label"><?= t('Display property with formatting')?></label>
		<select name="displayTag" class="form-control">
			<option value="">- none -</option>
			<option value="h1" <?=($this->controller->displayTag == "h1" ? "selected" : "")?>>H1 (Heading 1)</option>
			<option value="h2" <?=($this->controller->displayTag == "h2" ? "selected" : "")?>>H2 (Heading 2)</option>
			<option value="h3" <?=($this->controller->displayTag == "h3" ? "selected" : "")?>>H3 (Heading 3)</option>
			<option value="p" <?=($this->controller->displayTag == "p" ? "selected" : "")?>>p (paragraph)</option>
			<option value="b" <?=($this->controller->displayTag == "b" ? "selected" : "")?>>b (bold)</option>
			<option value="address" <?=($this->controller->displayTag == "address" ? "selected" : "")?>>address</option>
			<option value="pre" <?=($this->controller->displayTag == "pre" ? "selected" : "")?>>pre (preformatted)</option>
			<option value="blockquote" <?=($this->controller->displayTag == "blockquote" ? "selected" : "")?>>blockquote</option>
			<option value="div" <?=($this->controller->displayTag == "div" ? "selected" : "")?>>div</option>
		</select>
	</div>
	<div class="form-group">
		<label class="control-label"><?= t('Format of Date Properties')?></label>
		<input type="text" class="form-control" name="dateFormat" value="<?= $this->controller->dateFormat ?>"/>
		<div class="text-muted"><?= sprintf(t('See the formatting options at %s.'), '<a href="http://www.php.net/date" target="_blank">php.net/date</a>'); ?></div>
	</div>
	<fieldset>
		<legend><?= t('Avatar')?></legend>
		<div class="form-group">
			<label class="control-label" for="avatar_width"><?= t('Width'); ?></label>
			<input id="avatar_width" class="form-control" type="text" name="avatarWidth" value="<?= $this->controller->avatarWidth; ?>"/>
		</div>
		<div class="form-group">
			<label class="control-label" for="avatar_height"><?= t('Height'); ?></label>
			<input id="avatar_height" class="form-control" type="text" name="avatarHeight" value="<?= $this->controller->avatarHeight; ?>"/>
		</div>
	</fieldset>
</div>
