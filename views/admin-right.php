<div class="meta-box-sortables">

	<div class="postbox">

		<h3><span>Help &amp; Support</span></h3>
		<div class="inside">
			<ul>
				<li><strong>Questions, bugs, or great ideas?</strong></li>
				<li><a href="http://wordpress.org/support/plugin/source-affix" target="_blank">Visit our plugin support page</a></li>
				<li><strong>Wanna help make this plugin better?</strong></li>
				<li><a href="http://wordpress.org/support/view/plugin-reviews/source-affix" target="_blank">Review and rate this plugin on WordPress.org</a></li>
			</ul>
		</div> <!-- .inside -->

	</div> <!-- .postbox -->

</div> <!-- .meta-box-sortables -->

<div class="meta-box-sortables">
	<div class="postbox">

		<h3><span>My Blog</span></h3>
		<div class="inside">
			<?php
			$rss = fetch_feed( 'https://www.nilambar.net/category/wordpress/feed' );

			$maxitems = 0;

			$rss_items = array();

			if ( ! is_wp_error( $rss ) ) {
				$maxitems = $rss->get_item_quantity( 5 );
				$rss_items = $rss->get_items( 0, $maxitems );
			}
			?>

			<?php if ( ! empty( $rss_items ) ) : ?>

				<ul>
					<?php foreach ( $rss_items as $item ) : ?>
						<li><a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a></li>
					<?php endforeach; ?>
				</ul>

			<?php endif; ?>
		</div> <!-- .inside -->

	</div> <!-- .postbox -->
</div> <!-- .meta-box-sortables -->
