<fieldset id="gb-account-contact-info">
	<table class="collapsable account form-table">
		<tbody>
			<?php foreach ( $fields as $key => $data ): ?>
				<tr>
					<?php if ( $data['type'] != 'checkbox' ): ?>
						<td><?php gb_form_label($key, $data, 'account_fields'); ?></td>
						<td>
							<?php gb_form_field($key, $data, 'account_fields'); ?>
							<?php if ( $data['desc'] != '' ): ?>
								<br/><small><?php echo $data['desc']  ?></small>	
							<?php endif ?>
						</td>
					<?php else: ?>
						<td colspan="2">
							<label for="gb_account_<?php echo $key; ?>"><?php gb_form_field($key, $data, 'account_fields'); ?> <?php echo $data['label']; ?></label>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>
<br/>