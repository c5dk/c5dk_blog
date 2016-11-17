<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package">

    <div class="c5dk-date-navigation-header">
        <h3><?php echo h($title)?></h3>
    </div>

    <?php if (count($dates)) { ?>
        <ul class="c5dk-date-navigation-dates">
            <li><i class="fa fa-calendar-o"></i> <a href="<?php echo $view->controller->getDateLink()?>"><?php echo t('All')?></a></li>

            <?php foreach($dates as $date) { ?>
                <li><i class="fa fa-calendar"></i> <a href="<?php echo $view->controller->getDateLink($date)?>"
                        <?php if ($view->controller->isSelectedDate($date)) { ?>
                            class="c5dk-date-navigation-date-selected"
                        <?php } ?>><?php echo $view->controller->getDateLabel($date)?></a></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <?php echo t('None.')?>
    <?php } ?>


</div>
