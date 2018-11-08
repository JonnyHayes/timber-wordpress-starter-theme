<script>
	var eventAddress = '<?php echo $location; ?>';
		recurrence = JSON.parse('<?php echo json_encode($recurrence); ?>'),
		repeating = <?php echo count($recurrence); ?> > 0?true:false,
		noLocation = '<?php echo $location ?>' == ''?true:false;
</script>
<table id="event-details">
	<thead>
		<tr>
			<td><?php _e('Time &amp; Date', 'mvnp_basic');?></td>
			<td><?php _e('Address', 'mvnp_basic');?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div>
					<label>
						<strong><?php _e('Start Date', 'mvnp_basic');?></strong>
						<input name="start_date" class="widefat" type="date" value="<?php if($start_date != ''){echo $start_date;}?>">
					</label>
					<label>
						<strong><?php _e('Start Time', 'mvnp_basic');?></strong>
						<input name="start_time" id="start_time" class="widefat event-time" type="time" value="<?php if($start_time != ''){echo $start_time;}?>" <?php echo $all_day ? 'disabled' : ''; ?>>
					</label>
				</div>
				<div>
					<label>
						<strong><?php _e('End Date', 'mvnp_basic');?></strong>
						<input name="end_date" class="widefat" type="date" value="<?php if($end_date != ''){echo $end_date;}?>">
					</label>
					<label>
						<strong><?php _e('End Time', 'mvnp_basic');?></strong>
						<input name="end_time" id="end_time" class="widefat event-time" type="time" value="<?php if($end_time != ''){echo $end_time;}?>" <?php echo $all_day ? 'disabled' : ''; ?>>
					</label>
				</div>
				<div>
					<label>
						<input name="all_day" id="all_day" type="checkbox" <?php echo $all_day ? 'checked' : ''; ?>>
						<?php _e('All Day Event', 'mvnp_basic');?>
					</label>
				</div>
				<div>
					<label>
						<input name="recurring" id="recurring" type="checkbox" <?php echo $recurrence ? 'checked' : ''; ?>>
						<?php _e('Recurring Event', 'mvnp_basic');?>
					</label>
				</div>
				<div id="repeating-properties" style="display: none;">
					<div>
						<label>
							<? _e('Frequency', 'mvnp_basic'); ?>
							<select name="freq" id="freq">
								<option value=""><?php _e('Select a frequency', 'mvnp_basic');?></option>
								<option value="DAILY" <?php echo $recurrence['FREQ'] == 'DAILY' ? 'selected' : ''; ?>><?php _e('Daily', 'mvnp_basic');?></option>
								<option value="WEEKLY" <?php echo $recurrence['FREQ'] == 'WEEKLY' ? 'selected' : ''; ?>><?php _e('Weekly', 'mvnp_basic');?></option>
								<option value="MONTHLY" <?php echo $recurrence['FREQ'] == 'MONTHLY' ? 'selected' : ''; ?>><?php _e('Monthly', 'mvnp_basic');?></option>
								<option value="YEARLY" <?php echo $recurrence['FREQ'] == 'YEARLY' ? 'selected' : ''; ?>><?php _e('Yearly', 'mvnp_basic');?></option>
							</select>
						</label>
						<label id="interval-wrapper">
							<? _e('Interval', 'mvnp_basic'); ?>
							<select name="INTERVAL" id="interval">
								<?php for ($i = 1; $i <= 30; $i++){
									echo '<option ' . ($recurrence['INTERVAL'] == $i ? 'selected' : '') . ' value="' . $i . '">' . $i . '</option>';
								}?>
							</select>
						</label>
					</div>
					<div id="recurr_days" style="display: none;">
						<label>
							<input name="recurr_days[]" id="days_su" value="SU" type="checkbox" <?php echo in_array('SU', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Sunday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_mo" value="MO" type="checkbox" <?php echo in_array('MO', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Monday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_tu" value="TU" type="checkbox" <?php echo in_array('TU', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Tuesday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_we" value="WE" type="checkbox" <?php echo in_array('WE', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Wednesday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_th" value="TH" type="checkbox" <?php echo in_array('TH', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Thursday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_fr" value="FR" type="checkbox" <?php echo in_array('FR', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Friday', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_days[]" id="days_sa" value="SA" type="checkbox" <?php echo in_array('SA', $recurrence['BYDAY']) ? 'checked' : ''; ?>>
							<?php _e('Saturday', 'mvnp_basic');?>
						</label>
					</div>
					<div id="recurr_by" style="display: none;">
						<label>
							<input name="recurr_by" id="by_date" value="by_date" type="radio" <?php echo !array_key_exists('BYDAY', $recurrence) && $recurrence['FREQ'] == 'MONTHLY' ? 'checked' : ''; ?>>
							<?php _e('Day of the Month', 'mvnp_basic');?>
						</label>
						<label>
							<input name="recurr_by" id="by_day" value="by_day" type="radio" <?php echo array_key_exists('BYDAY', $recurrence) && $recurrence['FREQ'] == 'MONTHLY' ? 'checked' : ''; ?>>
							<?php _e('Day of the Week', 'mvnp_basic');?>
						</label>
					</div>
					<h4><?php _e('Ends', 'mvnp_basic');?></h4>
					<div style="margin-bottom: 20px;">
						<label>
							<input name="ends" id="never" type="radio" value="never" <?php echo !array_key_exists('COUNT', $recurrence) && !array_key_exists('UNTIL', $recurrence) ? 'checked' : ''; ?>>
							<?php _e('Never', 'mvnp_basic');?>
						</label>
					</div>
					<div>
						<label>
							<input name="ends" id="count_opt" type="radio" value="count_opt" <?php echo array_key_exists('COUNT', $recurrence) ? 'checked' : ''; ?>>
							<?php _e('After', 'mvnp_basic');?>
							<input name="COUNT" id="count" value="<?php echo array_key_exists('COUNT', $recurrence) ? $recurrence['COUNT'] : ''; ?>">
							<?php _e('Occurences', 'mvnp_basic');?>
						</label>
					</div>
					<div>
						<label>
							<input name="ends" id="until_opt" type="radio" value="until_opt" <?php echo array_key_exists('UNTIL', $recurrence) ? 'checked' : ''; ?>>
							<?php _e('Until', 'mvnp_basic');?>
							<input type="date" name="UNTIL" id="until" value="<?php echo array_key_exists('UNTIL', $recurrence) ? date('Y-m-d', strtotime($recurrence['UNTIL']) - get_option('gmt_offset') * 3600) : ''; ?>">
						</label>
					</div>
				</div>
			</td>
			<td>
				<div>
					<label>
						<input name="noLocation" id="noLocation" type="checkbox" <?php echo $location == '' ? 'checked' : ''; ?>>
						<?php _e('This event has no location', 'mvnp_basic');?>
					</label>
					<div id="event_location" style="display: none;">
						<label>
							<strong><?php _e('Location Name', 'mvnp_basic');?></strong>
							<input name="location_name" class="widefat location-name-input i18n-multilingual" type="text" value="<?php if($location_name != ''){echo $location_name;}?>">
						</label>
						<label>
							<strong><?php _e('Location Address', 'mvnp_basic');?></strong>
							<input name="location" class="widefat location-input" type="text" value="<?php if($location != ''){echo $location;}?>">
						</label>
						<div id="event-location-map" style="width: 100%; height: 200px;"><center><?php _e('Enter an Address', 'mvnp_basic')?></center></div>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
