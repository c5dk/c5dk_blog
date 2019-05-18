<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package">

	<div class="c5dk-date-navigation-header">
		<h3><?= h($title)?></h3>
	</div>

	<?php if (count($dates)) { ?>
		<ul class="c5dk-date-navigation-dates">
			<li><i class="fa fa-calendar-o"></i> <a href="<?= $view->controller->getDateLink()?>"><?= t('All')?></a></li>

			<?php $first = true; ?>
			<?php foreach ($dates as $date) { ?>

				<?php
				$dateSplit = explode(' ', $view->controller->getDateLabel($date));
				$dateSplit = array_reverse($dateSplit);
				$year = array_shift($dateSplit);
				$text = implode(' ', array_reverse($dateSplit));
				?>

				<?php if ($date['year'] != $yearTmp) { ?>
					<?php if (isset($yearTmp)) { ?>
						</li></ul>
					<?php } ?>
					<li onclick="$(this).find('ul').toggleClass('hide')">
						<i class="fa fa-calendar-o"></i>
						<a><?= $date['year']; ?></a>
						<ul class="c5dk-date-navigation-dates<?= isset($yearTmp)? ' hide' :  ''; ?>">
						<?php $yearTmp = $date['year']; ?>
				<?php } ?>
				<li>
					<i class="fa fa-calendar"></i>
					<a href="<?= $view->controller->getDateLink($date)?>"<?= ($view->controller->isSelectedDate($date))? ' class="c5dk-date-navigation-date-selected"' : ''; ?>>
						<?= $text; ?>
					</a>
				</li>
			<?php } ?>
			<?php if (count($dates)) { ?>
				</li></ul>
			<?php } ?>
		</ul>
	<?php } else { ?>
		<?= t('None.')?>
	<?php } ?>


</div>
