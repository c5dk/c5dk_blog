<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
use Concrete\Attribute\Select\OptionList;

$this->requireAsset('javascript', 'tagcanvas');
?>

<?php if (isset($options) && count($options) > 0) { ?>
<div class="ccm-block-tags-wrapper">

	<?php if ($title): ?>
		<div class="ccm-block-tags-header">
			<h3><?= $title?></h3>
		</div>
	<?php endif; ?>
	<div id="c5dkCanvasContainerHtml5">
		<canvas id="c5dkCanvasImageBackground" width="270" height="270" style="width: 100%">
			<p>Anything in here will be replaced on browsers that support the canvas element</p>
			<ul class="ccm-tag-list">
				<?php foreach ($options as $option) { ?>
					<li>
					<?php if ($target) { ?>
						<a href="<?= $controller->getTagLink($option) ?>">
							<span class="ccm-block-tags-tag label"><?= $option->getSelectAttributeOptionValue()?></span>
						</a>
					<?php } else { ?>
						<span class="ccm-block-tags-tag label"><?= $option->getSelectAttributeOptionValue()?></span>
					<?php } ?>
					</li>
				<?php } ?>
			</ul>
		</canvas>
	</div>

</div>
<script type="text/javascript">
	$(document).ready(function() {

		if( ! $('#c5dkCanvasImageBackground').tagcanvas({
			interval : 20,
			textColour : '#FFF',
			textHeight : 14,
			textFont : "Helvetica, Arial, sans-serif",
			outlineColour : '#074F68',
			outlineThickness : 5,
			outlineOffset : 1,
			outlineMethod : "colour",
			centreImage : '<?= REL_DIR_PACKAGES; ?>/c5dk_blog/blocks/tags/templates/c5dk_tags/images/tagsicon.png',
			padding : 0,
			stretchX : 0.9,
			maxSpeed : 0.05,
			minBrightness : 0.3,
			depth : 0.92,
			pulsateTo : 0.2,
			pulsateTime : 0.75,
			initial : [0.110, -0.150],
			decel : 1,
			reverse : true,
			hideTags : false,
			shadow : '#ccf',
			shadowBlur : 3,
			weight : true,
			weightFrom : 'data-weight',
			weightGradient : {0:'#f00', 0.33:'#ff0', 0.66:'#0f0', 1:'#00f'},
			shape: "sphere",
			fadeIn : 800
		})) {
			// TagCanvas failed to load
			$('#c5dkCanvasContainerHtml5').hide();
		}
	});
</script>
<div style="clear: both"></div>
<?php } ?>