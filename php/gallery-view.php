<table id="repeatable-fieldset-one">
<thead>
	<tr>
		<th></th>
		<th><?php _e('Image', 'mvnp_basic');?></th>
		<th colspan="2"><?php _e('Meta Information', 'mvnp_basic');?></th>
	</tr>
</thead>
<tbody class="ui-sortable">
<?php

if($gallery_images):
	foreach($gallery_images as $i => $field){
		$json = json_encode($field['props']);?>
	<tr>
		<td class="drag-handle">
			<?php _e('Drag to Reorder', 'mvnp_basic');?>
		</td>
		<td>
			<a class="submitdelete toggle-media" href="#"><?php _e('Toggle Media Type', 'mvnp_basic');?></a>
			<img class="media-image <?php if($field['props'] == '' || $field['props'] == null){echo ' hidden';}?>" src="<?php if($field['props'] != '' && $field['props'] != 'null'){echo $field['props']['sizes']->thumbnail->url;} else {echo get_stylesheet_directory_uri() . '/images/gallery-no-image.jpg';}?>" title="<?php _e('Click to change image', 'mvnp_basic');?>" alt="<?php _e('Click to change image', 'mvnp_basic');?>">
			<textarea class="widefat custom-media-url" name="props[]" style="display: none;"><?php echo $json; ?></textarea>
			<label class="<?php if($field['iframe'] == ''){echo 'hidden';}?>">
				<?php _e('iFrame source', 'mvnp_basic');?>
				<input id="iframe-input-<?php echo $i + 1; ?>" type="text" class="widefat iframe-input" name="iframe[]" value="<?php if($field['iframe'] != ''){echo esc_attr($field['iframe']);}?>" placeholder="<?php _e('iFrame source', 'mvnp_basic');?>">
			</label>
		</td>
		<td>
			<div>
				<label>
					<?php _e('Media title', 'mvnp_basic');?>
					<input id="name-input-<?php echo $i + 1; ?>" type="text" class="widefat name-input i18n-multilingual" name="name[]" value="<?php if($field['name'] != ''){echo esc_attr($field['name']);}?>" placeholder="<?php _e('Name', 'mvnp_basic');?>">
				</label>
				<label>
					<?php _e('Media alt tag', 'mvnp_basic');?>
					<input id="alt-input-<?php echo $i + 1; ?>" type="text" class="widefat alt-input i18n-multilingual" name="alt[]" value="<?php if($field['alt'] != ''){echo esc_attr($field['alt']);}?>" placeholder="<?php _e('Alt Text', 'mvnp_basic');?>">
				</label>
				<label>
					<?php _e('Media link', 'mvnp_basic');?>
					<input id="link-input-<?php echo $i + 1; ?>" type="url" class="widefat link-input i18n-multilingual" name="link[]" value="<?php if($field['link'] != ''){echo esc_attr($field['link']);}?>" placeholder="<?php _e('Image link', 'mvnp_basic');?>">
				</label>
			</div>
			<div>
				<label>
					<?php _e('Media copy', 'mvnp_basic');?>
					<textarea id="copy-input-<?php echo $i + 1; ?>" class="widefat copy-input i18n-multilingual" name="copy[]" rows="5"><?php if($field['copy'] != ''){echo esc_attr($field['copy']);}?></textarea>
				</label>
			</div>
		</td>
		<td class="submitbox"><a class="submitdelete remove-row" href="#"><?php _e('Remove', 'mvnp_basic');?></a></td>
	</tr>
	<?php

	} else :
?>
<tr>
	<td class="drag-handle">
		<?php _e('Drag to Reorder', 'mvnp_basic');?>
	</td>
	<td>
		<a class="submitdelete toggle-media" href="#"><?php _e('Toggle Media Type', 'mvnp_basic');?></a>
		<img class="media-image" src="<?php echo get_stylesheet_directory_uri() . '/images/gallery-no-image.jpg' ?>" title="<?php _e('Click to add image', 'mvnp_basic');?>" alt="<?php _e('Click to add image', 'mvnp_basic');?>">
		<textarea class="widefat custom-media-url" name="props[]" style="display: none;"></textarea>
		<label class="hidden">
			<?php _e('iFrame source', 'mvnp_basic');?>
			<input id="iframe-input-default" type="text" class="widefat iframe-input" name="iframe[]" placeholder="<?php _e('iFrame source', 'mvnp_basic');?>">
		</label>
	</td>
	<td>
		<div>
			<label>
				<?php _e('Media title', 'mvnp_basic');?>
				<input id="name-input-default" type="text" class="widefat name-input i18n-multilingual" name="name[]" placeholder="<?php _e('Name', 'mvnp_basic');?>">
			</label>
			<label>
				<?php _e('Media alt tag', 'mvnp_basic');?>
				<input id="alt-input-default" type="text" class="widefat alt-input i18n-multilingual" name="alt[]" placeholder="<?php _e('Alt text', 'mvnp_basic');?>">
			</label>
			<label>
				<?php _e('Media link', 'mvnp_basic');?>
				<input id="link-input-default" type="url" class="widefat link-input i18n-multilingual" name="link[]" placeholder="<?php _e('Media link', 'mvnp_basic');?>">
			</label>
		</div>
		<div>
			<label>
				<?php _e('Media copy', 'mvnp_basic');?>
				<textarea id="copy-input-default" class="widefat copy-input i18n-multilingual" name="copy[]" rows="5"></textarea>
			</label>
		</div>
	</td>
	<td class="submitbox"><a class="submitdelete remove-row" href="#"><?php _e('Remove', 'mvnp_basic');?></a></td>
</tr>
<?php endif;?>
<tr class="empty-row screen-reader-text">
	<td class="drag-handle">
		<?php _e('Drag to Reorder', 'mvnp_basic');?>
	</td>
	<td>
		<a class="submitdelete toggle-media" href="#"><?php _e('Toggle Media Type', 'mvnp_basic');?></a>
		<img class="media-image" src="<?php echo get_stylesheet_directory_uri() . '/images/gallery-no-image.jpg' ?>" title="<?php _e('Click to add image', 'mvnp_basic');?>" alt="<?php _e('Click to add image', 'mvnp_basic');?>">
		<textarea class="widefat custom-media-url" style="display: none;"></textarea>
		<label class="hidden">
			<?php _e('iFrame source', 'mvnp_basic');?>
			<input id="iframe-input-new" type="text" class="widefat iframe-input" placeholder="<?php _e('iFrame source', 'mvnp_basic');?>">
		</label>
	</td>
	<td>
		<div>
			<label>
				<?php _e('Media title', 'mvnp_basic');?>
				<input id="name-input-new" type="text" class="widefat name-input i18n-multilingual" placeholder="<?php _e('Name', 'mvnp_basic');?>">
			</label>
			<label>
				<?php _e('Media alt tag', 'mvnp_basic');?>
				<input id="alt-input-new" type="text" class="widefat alt-input i18n-multilingual" placeholder="<?php _e('Alt text', 'mvnp_basic');?>">
			</label>
			<label>
				<?php _e('Media link', 'mvnp_basic');?>
				<input id="link-input-new" type="url" class="widefat link-input i18n-multilingual" placeholder="<?php _e('Media link', 'mvnp_basic');?>">
			</label>
		</div>
		<div>
			<label>
				<?php _e('Media copy', 'mvnp_basic');?>
				<textarea id="copy-input-new" class="widefat copy-input i18n-multilingual" rows="5"></textarea>
			</label>
		</div>
	</td>
	<td class="submitbox"><a class="submitdelete remove-row" href="#"><?php _e('Remove', 'mvnp_basic');?></a></td>
</tr>
</tbody>
<tfoot>
	<tr>
		<th colspan="3"><a id="add-row" class="button button-primary button-large" href="#"><?php _e('Add another', 'mvnp_basic');?></a></th>
	</tr>
</tfoot>
</table>
