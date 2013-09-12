<div class="dash_section background_alt">
		
		<h2 class="section_heading contrast gb_ff"><?php gb_e('My Voting Overview'); ?> <a class="section_heading_link font_x_small"  href="<?php gb_suggestions_url() ?>" title="<?php gb_e('Browse all my Suggestions'); ?>"><?php gb_e('See All&#63;'); ?></a></h2>
		
		<?php

			$voted_suggestions = SA_Voting::suggestions_user_voted();
			if ( empty( $voted_suggestions ) ) {
				?>
					<p><?php gb_e('You have not voted yet on any'); ?> <a href="<?php gb_suggestions_url() ?>" title="<?php gb_e('Browse Active Suggestions') ?>"><?php gb_e('suggestions'); ?></a>.</p>
				<?php
			}
			else {
				$args = array(
					'post_type' => gb_get_deal_post_type(),
					'post_status' => array( 'pending', 'publish', 'draft', 'future' ),
					'posts_per_page' => 25, // return this many
					'post__in' => SA_Voting::suggestions_user_voted(),
					'by_pass_suggestion_filter' => TRUE,
					'meta_query' => array(
						array(
							'key' => '_expiration_date',
							'value' => array(0, current_time('timestamp')),
							'compare' => 'NOT BETWEEN'
						))
				);
				$suggestions = new WP_Query( $args );
				
				if ( $suggestions->have_posts() ) {
					?>
					<table class="voting_table gb_table suggestions"><!-- Begin .gb_table -->
		
						<thead>
							<tr>
								<th class="contrast th_title"><?php gb_e('Suggestion'); ?></th>
								<th class="contrast th_stats"><?php gb_e('Votes'); ?></th>
								<th class="contrast th_status"><?php gb_e('Status'); ?></th>
								<th class="contrast th_share"><?php gb_e('Share'); ?></th>
							</tr>
						</thead>
						
						<tbody>
						<?php
							while ( $suggestions->have_posts()) : $suggestions->the_post();
								$suggested_deal = SA_Post_Type::get_instance( get_the_id() ); 
								$total_votes = $suggested_deal->get_votes();
								$threshold = $suggested_deal->get_threshold();
								$remaining = ( $threshold-$total_votes >= 0 ) ? $threshold-$total_votes : 0 ; ?>
								<tr>
									<td class="purchase_deal_title">
										<strong class="deal_title gb_ff clearfix"><?php the_title() ?></strong>
										<?php if ( gb_has_merchant_name() ): ?>
											<br/>
											<p class="merchant_link font_xx_small all_caps"><a href="<?php gb_merchant_url() ?>" class="button contrast_button"><?php gb_e('Merchant Info') ?></a></p>
										<?php endif ?>
									<td class="td_stats">
										<?php printf( gb__( '<span class="alt_button contrast_button gb_ff font_medium">%s</span>' ), $total_votes ) ?>
									</td>
									<td class="td_status">
										
										<?php
											// Voting is in progress since it's still a suggestion, with a $remaining left
											if ( $remaining && $suggested_deal->is_suggested_deal() ) { 
												printf( gb__( '<span class="progress_msg button gb_ff font_medium">%s&nbsp;Votes&nbsp;Needed</span>' ), $remaining );
											}
											elseif ( gb_deal_availability() && !$suggested_deal->is_suggested_deal() ) {
												?><a href="<?php echo get_permalink() ?>" class="button gb_ff font_medium"><?php printf( gb__( 'Available for Purchase!' ), $remaining ); ?></a><?php
											}
											// nothing remaining or not a suggestions
											else {
												printf( '<span class="button gb_ff font_medium">%s</span>', gb__('Pending') );
											} ?>
									</td>
									</td>
									<td class="td_share">
										<style type="text/css">.follow_text { display: none; }</style>
										<?php get_template_part('inc/social-share') ?>
									</td>
								</tr>
								<?php
							endwhile; ?>
						</tbody>
					</table><!-- End .gb_table -->
					
					<?php if ( $suggestions->found_posts > 25 ) : ?>
						<p><?php gb_e('This is a summary of your most recent votes.'); ?></p>
					<?php endif;

				} else {
					?>
						<p><?php gb_e('Could not find any recent voted voted on'); ?> <a href="<?php gb_suggestions_url() ?>" title="<?php gb_e('Browse Active Suggestions') ?>"><?php gb_e('suggestions'); ?></a>.</p>
					<?php
				}

			}
		?>
	</div>