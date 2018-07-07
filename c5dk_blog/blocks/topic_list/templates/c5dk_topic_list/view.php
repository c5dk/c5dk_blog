<?php  defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package" class="c5dk_topic_list_wrapper">

	<div class="c5dk-topic-list-header">
		<h3><?php echo h($title)?></h3>
	</div>

	<?php
	if ($mode == 'S' && is_object($tree)):
		$node = $tree->getRootTreeNodeObject();
		$node->populateChildren();
		if (is_object($node)) {
			print '<ul class="c5dk-topic-list-list">';
			print '<li><i class="fa fa-files-o"></i> <a href="' . $view->controller->getTopicLink($topic) . '" class="c5dk-topic-list-topic-selected">' . t('Show All Topics') . '</a></li>';
			$walk = function($node) use (&$walk, &$view, $selectedTopicID) {
				print '<ul class="c5dk-topic-list-list">';

				foreach ($node->getChildNodes() as $topic) {
					if (is_object($topic) && $topic->getTreeNodeTypeID() === "2") { ?>
						<li><i class="fa fa-chevron-circle-right"></i> <?php echo $topic->getTreeNodeDisplayName()?></li>
					<?php } else { ?>
						<li><i class="fa fa-file-o"></i> <a href="<?php echo $view->controller->getTopicLink($topic)?>"
								<?php if (isset($selectedTopicID) && $selectedTopicID == $topic->getTreeNodeID()) { ?>
									class="c5dk-topic-list-topic-selected"
								<?php } ?> ><?php echo $topic->getTreeNodeDisplayName()?></a></li>
					<?php } ?>
					<?php $walk($topic); ?>
				<?php }

				print '</ul>';
			};
			$walk($node);
			print '</ul>';
		}
	endif;

	if ($mode == 'P'): ?>
		<?php if (count($topics)) { ?>
			<ul class="c5dk-topic-list-page-topics">
			<?php foreach ($topics as $topic) { ?>
				<li><a href="<?php echo $view->controller->getTopicLink($topic)?>"><?php echo $topic->getTreeNodeDisplayName()?></a></li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<?php echo t('No topics.')?>
		<?php } ?>

	<?php endif; ?>

</div>

