<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package" class="c5dk_blog_package_wrapper">
	<h3><?= t('C5DK Blog Editor Manager'); ?></h3>

	<!-- Root table -->
	<table class="table">
		<tr class="c5dk_blog_package_header">
			<th class="left_round_corner"><?= t('Root'); ?></th>
			<th><?= t('Path'); ?></th>
			<th style="text-align:right;"><?= t('Actions'); ?></th>
		</tr>

		<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
			<tr class="c5dk_blog_package_root_header">
				<td><?= $C5dkRoot->getCollectionName(); ?></td>
				<td><a href="<?= $C5dkRoot->getCollectionLink(); ?>"><?= $C5dkRoot->getCollectionPath(); ?></a></td>
				<td style="text-align:right;">
					<a class="btn btn-primary btn-sm" href="<?= URL::to($langpath . '/c5dk/blog/editor/manager/root', $C5dkRoot->getCollectionID()); ?>">
						<?= t('Manage Root'); ?>
					</a>
				</td>
			</tr>
		<?php } ?>
	</table>

</div>

<div style="clear: both;"></div>
