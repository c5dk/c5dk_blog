<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<?php
Core::make('help')->display(t('You have requested deletion of a user that is the owner of some blog pages. We have made it possible to control what to do with these blog pages.<br><br>You can either choose to delete alle the blog pages that belong to this user, or you can assign a new user as the owner of these blog pages.'));
?>
<div id="c5dk-blog-package">
    <div class="c5dk_blog_red_allert_frame">
        <h2><?php echo t('Please be careful here!'); ?></h2>
        <h3><?php echo t('You have chosen to delete a blog user. This user previously wrote some blog pages and this user is the owner of these blog pages.'); ?></h3>
        <h4><?php echo t('If you are going to delete this user, the blog pages owned by this user will fail.'); ?></h4>
        <h4><?php echo t('So to help you to solve this, we are giving you some choices.'); ?></h4>
        <h4><?php echo t('1. You can choose to assign a new owner for these blog pages (BLUE BUTTON).'); ?></h4>
        <h4><?php echo t('2. You can decide to delete all blog pages that belong to the user you want to delete (RED BUTTON).'); ?></h4>
        <h4><?php echo t('But be sure that you understand what will happen if you decide to delete all the blog pages that belong to this user.'); ?></h4>
    </div>
    
    <div class="">
        <h3><?php echo t('Transfer Blog Page Ownership'); ?></h3>
        <p><?php echo t('Default we have chosen "<strong>admin</strong>" as the user that will own the blog pages from the user you are going to delete. But you can choose whatever user that you want to own these blog pages by clicking on the "<strong>Select User</strong>" link.'); ?></p>
        <form action="<?= View::action('transfer', $uID); ?>" method="POST">
        <?= Core::make('helper/form/user_selector')->selectUser('tID', 1); ?><br>
        <?= $form->submit('transfer', t('Delete User and Transfer Ownership of the Blog Pages!'), array(), 'btn btn-primary btn-lg'); ?>
        </form>
    </div>
    
    <hr>
    
    <div class="">
        <h3><?php echo t('Delete the User and Delete Blog Pages owned by the deleted user'); ?></h3>
        <p><?php echo t('If you click on this button, the user and all the blog pages the user wrote will be deleted. <strong>BE CAREFUL!</strong>'); ?></p>
        <form action="<?= View::action('delete', $uID); ?>" method="POST">
        <?= $form->submit('delete', t('Delete User and Delete All Blog Pages This User Wrote!'), array(), 'btn btn-danger btn-lg'); ?>
        </form>
    </div>
    
    <div style="clear:both"></div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<a class="btn ccm-input-submit pull-right btn btn-default" href="<?= View::url('/dashboard/users/search/view/', $uID); ?>"><?php echo t('Cancel'); ?></a>
		</div>
	</div>
</div>

<style>
/* Dashboard page styling */
#c5dk-blog-package .c5dk_blog_red_allert_frame {
    border: 5px solid #D60000;
    border-radius: 10px;
    padding: 0 20px 10px 20px;
}
</style>