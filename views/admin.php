<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package Source_Affix
 */
?>
<div class="wrap">

  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

  <div id="poststuff">

    <div id="post-body" class="metabox-holder columns-2">

      <!-- main content -->
      <div id="post-body-content">

        <div class="meta-box-sortables ui-sortable">

          <div class="postbox">

            <div class="inside">

      				<form action="options.php" method="post">
      				<?php settings_fields('sa-plugin-options-group'); ?>
      				<?php do_settings_sections('source-affix-main'); ?>
      				<?php submit_button(__('Save Changes', 'source-affix')); ?>
      				</form>

            </div> <!-- .inside -->

          </div> <!-- .postbox -->

        </div> <!-- .meta-box-sortables .ui-sortable -->

      </div> <!-- post-body-content -->

      <!-- sidebar -->
      <div id="postbox-container-1" class="postbox-container">

        <?php require_once( SOURCE_AFFIX_DIR . '/views/admin-right.php'); ?>

      </div> <!-- #postbox-container-1 .postbox-container -->

    </div> <!-- #post-body .metabox-holder .columns-2 -->

    <br class="clear">
  </div> <!-- #poststuff -->

</div> <!-- .wrap -->
