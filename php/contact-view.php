<table id="contact-page-info">
	<tbody>
		<tr>
			<td>
				<div>
					<label>
						<strong><?php _e('Street Address 1', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="text" class="widefat" name="street_1" value="<?php echo $contact_info['street_address_1']; ?>">
						<?php else:echo '<p>' . $contact_info['street_address_1'] . '</p>';endif;?>
					</label>
				</div>
				<div>
					<label>
						<strong><?php _e('Street Address 2', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="text" class="widefat" name="street_2" value="<?php echo $contact_info['street_address_2']; ?>">
						<?php else:echo '<p>' . $contact_info['street_address_2'] . '</p>';endif;?>
					</label>
				</div>
				<div class="addr-inline">
					<label>
						<strong><?php _e('City', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="text" class="widefat" name="city" value="<?php echo $contact_info['city']; ?>">
						<?php else:echo '<p>' . $contact_info['city'] . '</p>';endif;?>
					</label>
					<label>
						<strong><?php _e('State', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="text" class="widefat" name="state" value="<?php echo $contact_info['state']; ?>">
						<?php else:echo '<p>' . $contact_info['state'] . '</p>';endif;?>
					</label>
					<label>
						<strong><?php _e('Postal', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="number" class="widefat" name="postal" value="<?php echo $contact_info['postal']; ?>">
						<?php else:echo '<p>' . $contact_info['postal'] . '</p>';endif;?>
					</label>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div>
					<label>
						<strong><?php _e('Phone Number', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="tel" class="widefat" name="phone_number" value="<?php echo $contact_info['phone_number']; ?>">
						<?php else:echo '<p>' . $contact_info['phone_number'] . '</p>';endif;?>
					</label>
				</div>
				<div>
					<label>
						<strong><?php _e('Email Address', 'mvnp_basic')?></strong>
						<?php if(current_user_can('edit_theme_options')): ?>
						<input type="email" class="widefat" name="email" value="<?php echo $contact_info['email']; ?>">
						<?php else:echo '<p>' . $contact_info['email'] . '</p>';endif;?>
					</label>
				</div>
			</td>
	</tbody>
</table>
