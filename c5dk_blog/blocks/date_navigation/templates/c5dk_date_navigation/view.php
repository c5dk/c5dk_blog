<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package">

	<div class="c5dk-date-navigation-header">
		<h3><?= h($title)?></h3>
	</div>

	<?php if (count($dates)) { ?>
		<ul class="c5dk-date-navigation-dates">
			<li><i class="fa fa-calendar-o"></i> <a href="<?= $view->controller->getDateLink()?>"><?= t('All')?></a></li>

			<?php foreach ($dates as $date) { ?>
				<li><i class="fa fa-calendar"></i> <a href="<?= $view->controller->getDateLink($date)?>"
						<?php if ($view->controller->isSelectedDate($date)) { ?>
							class="c5dk-date-navigation-date-selected"
						<?php } ?>><?= $view->controller->getDateLabel($date)?></a></li>
			<?php } ?>
		</ul>
	<?php } else { ?>
		<?= t('None.')?>
	<?php } ?>


</div>
