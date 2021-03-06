<div id="post_content <?php the_ID() ?>" <?php post_class('post loop_deal background_alt deal_status-'.gb_get_status().' suggestion clearfix'); ?>>
	
	<h3 class="deal_merchant_title alt_text font_small gb_ff clearfix"><a href="<?php gb_suggestion_url()  ?>"><?php gb_e('Suggested on') ?> <?php the_date()  ?></a></h3>
	
	<a href="javascript:void(0)" title="<?php gb_e('Read') ?> <?php the_title(); ?>">

		<h3 class="contrast_alt"><?php the_title(); ?></h3>
	
		<?php if ( has_post_thumbnail() ): ?>
			<div class="loop_thumb contrast">
				<?php the_post_thumbnail('gbs_300x180'); ?>
			</div>
		<?php else : ?>
			<div class="loop_thumb contrast padded"><img src="<?php gb_header_logo(); ?>" /></div>
		<?php endif; ?>
		
	</a>

	<div class="deal_meta contrast clearfix">

		<div class="deal_meta_wrapper suggestion_wrap contrast">
			<?php if ( gb_suggested_can_vote() ): ?>
				<?php gb_suggestion_button();  ?>
			<?php else: ?>
				<span class="cannot_vote cloak"><?php gb_e('Thank you for your vote!')  ?></span>	
			<?php endif ?>
			
			<?php 
				/**
				 * div must include <?php the_ID();  ?>_vote_result in order for it to
				 * be dynamically updated via AJAX with the new vote total.
				 * 
				 */
			 ?>
			<span class="suggested_info gb_ff"><?php gb_e('Votes:') ?>&nbsp;<span id="<?php the_ID(); ?>_vote_result" class="suggested_votes"><?php gb_suggested_votes() ?></span></span>
		</div><!-- .suggestion_wrap -->

	</div>


	<div class="deal_meta contrast clearfix">

		<div class="deal_meta_wrapper clearfix">
			<div class="meta_column first clearfix">
				<div class="meta_value">
					<?php echo implode( ', ', gb_get_deal_voucher_locations() ) ?>
				</div>
				<span class="meta_title">
					<?php gb_e('Suggested Voucher Locations') ?>
				</span>
			</div>
		</div>
	</div>

</div>