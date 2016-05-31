<div class="wpallexport-collapsed wpallexport-section">
	<div class="wpallexport-content-section" style="margin-top:10px;">
		<div class="wpallexport-collapsed-header" style="padding-left: 25px;">
			<h3><?php _e('Configure Advanced Settings','wp_all_export_plugin');?></h3>	
		</div>
		<div class="wpallexport-collapsed-content" style="padding: 0;">
			<div class="wpallexport-collapsed-content-inner">				
				<table class="form-table" style="max-width:none;">
					<tr>
						<td colspan="3">																									
							<div class="input" style="margin:5px 0px;">
								<label for="records_per_request"><?php _e('In each iteration, process', 'wp_all_export_plugin');?> <input type="text" name="records_per_iteration" class="wp_all_export_sub_input" style="width: 40px;" value="<?php echo esc_attr($post['records_per_iteration']) ?>" /> <?php _e('records', 'wp_all_export_plugin'); ?></label>
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('WP All Export must be able to process this many records in less than your server\'s timeout settings. If your export fails before completion, to troubleshoot you should lower this number.', 'wp_all_export_plugin'); ?>">?</a>							
							</div>												
							<div class="input" style="margin:5px 0px;">														
								<input type="hidden" name="export_only_new_stuff" value="0" />
								<input type="checkbox" id="export_only_new_stuff" name="export_only_new_stuff" value="1" <?php echo $post['export_only_new_stuff'] ? 'checked="checked"': '' ?> />
								<label for="export_only_new_stuff"><?php printf(__('Only export %s once', 'wp_all_export_plugin'), empty($post['cpt']) ? __('records', 'wp_all_export_plugin') : wp_all_export_get_cpt_name($post['cpt'])); ?></label>															
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('If re-run, this export will only include records that have not been previously exported.', 'wp_all_export_plugin'); ?>">?</a>							
							</div>																				
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="include_bom" value="0" />
								<input type="checkbox" id="include_bom" name="include_bom" value="1" <?php echo $post['include_bom'] ? 'checked="checked"': '' ?> />
								<label for="include_bom"><?php _e('Include BOM in export file', 'wp_all_export_plugin') ?></label>															
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('The BOM will help some programs like Microsoft Excel read your export file if it includes non-English characters.', 'wp_all_export_plugin'); ?>">?</a>							
							</div>
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="creata_a_new_export_file" value="0" />
								<input type="checkbox" id="creata_a_new_export_file" name="creata_a_new_export_file" value="1" <?php echo $post['creata_a_new_export_file'] ? 'checked="checked"': '' ?> />
								<?php if ( ! XmlExportEngine::$is_auto_generate_enabled || ! XmlExportWooCommerceOrder::$is_active || $post['export_type'] != 'specific'): ?>	
								<label for="creata_a_new_export_file"><?php _e('Create a new file each time export is run', 'wp_all_export_plugin') ?></label>				
								<?php else: ?>											
								<label for="creata_a_new_export_file"><?php _e('Create a new import bundle each time export is run', 'wp_all_export_plugin') ?></label>				
								<?php endif; ?>
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('If disabled, the export file will be overwritten every time this export run.', 'wp_all_export_plugin'); ?>">?</a>							
							</div>

							<?php if ( ! XmlExportEngine::$is_auto_generate_enabled || ! XmlExportWooCommerceOrder::$is_active || $post['export_type'] != 'specific'): ?>	
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="split_large_exports" value="0" />
								<input type="checkbox" id="split_large_exports" name="split_large_exports" class="switcher" value="1" <?php echo $post['split_large_exports'] ? 'checked="checked"': '' ?> />
								<label for="split_large_exports"><?php _e('Split large exports into multiple files', 'wp_all_export_plugin') ?></label>															
								<span class="switcher-target-split_large_exports pl17" style="display:block; clear: both; width: 100%;">
									<div class="input pl17" style="margin:5px 0px;">							
										<label for="records_per_request"><?php _e('Limit export to', 'wp_all_export_plugin');?></label> <input type="text" name="split_large_exports_count" class="wp_all_export_sub_input" style="width: 50px;" value="<?php echo esc_attr($post['split_large_exports_count']) ?>" /> <?php _e('records per file', 'wp_all_export_plugin'); ?>															
									</div>																				
								</span>			
							</div>		
							<?php endif; ?>			

							<?php if ( XmlExportEngine::$is_auto_generate_enabled && XmlExportWooCommerceOrder::$is_active && $post['export_type'] == 'specific'): ?>	
							<!--  Include products -->
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="order_include_poducts" value="0" />
								<input type="checkbox" id="order_include_poducts" name="order_include_poducts" class="switcher" value="1" <?php echo $post['order_include_poducts'] ? 'checked="checked"': '' ?> />
								<label for="order_include_poducts"><?php _e('Include products', 'wp_all_export_plugin') ?></label>											
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('Products purchased in these orders will also be exported.', 'wp_all_export_plugin'); ?>">?</a>
								<span class="switcher-target-order_include_poducts pl17" style="display:block; clear: both; width: 100%;">
									<div class="input" style="margin-left: 5px;">
										<input type="hidden" name="order_include_all_poducts" value="0" />
										<input type="checkbox" id="order_include_all_poducts" name="order_include_all_poducts" class="switcher" value="1" <?php echo $post['order_include_all_poducts'] ? 'checked="checked"': '' ?> />
										<label for="order_include_all_poducts"><?php _e('Export all products', 'wp_all_export_plugin') ?></label>							
										<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('All products will be exported, not only those that were purchased in these orders.', 'wp_all_export_plugin'); ?>">?</a>
									</div>																				
								</span>			
							</div>
							<!-- Include Coupons -->
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="order_include_coupons" value="0" />
								<input type="checkbox" id="order_include_coupons" name="order_include_coupons" class="switcher" value="1" <?php echo $post['order_include_coupons'] ? 'checked="checked"': '' ?> />
								<label for="order_include_coupons"><?php _e('Include coupons', 'wp_all_export_plugin') ?></label>											
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('Coupons used in these orders will also be exported.', 'wp_all_export_plugin'); ?>">?</a>
								<span class="switcher-target-order_include_coupons pl17" style="display:block; clear: both; width: 100%;">
									<div class="input" style="margin-left: 5px;">
										<input type="hidden" name="order_include_all_coupons" value="0" />
										<input type="checkbox" id="order_include_all_coupons" name="order_include_all_coupons" class="switcher" value="1" <?php echo $post['order_include_all_coupons'] ? 'checked="checked"': '' ?> />
										<label for="order_include_all_coupons"><?php _e('Export all coupons', 'wp_all_export_plugin') ?></label>							
										<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('All coupons will be exported, not only those that were used in these orders.', 'wp_all_export_plugin'); ?>">?</a>
									</div>																				
								</span>			
							</div>
							<!-- Include Customers -->
							<div class="input" style="margin:5px 0px;">
								<input type="hidden" name="order_include_customers" value="0" />
								<input type="checkbox" id="order_include_customers" name="order_include_customers" class="switcher" value="1" <?php echo $post['order_include_customers'] ? 'checked="checked"': '' ?> />
								<label for="order_include_customers"><?php _e('Include customers', 'wp_all_export_plugin') ?></label>											
								<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('Customers who purchased created orders will also be exported.', 'wp_all_export_plugin'); ?>">?</a>
								<span class="switcher-target-order_include_customers pl17" style="display:block; clear: both; width: 100%;">
									<div class="input" style="margin-left: 5px;">
										<input type="hidden" name="order_include_all_customers" value="0" />
										<input type="checkbox" id="order_include_all_customers" name="order_include_all_customers" class="switcher" value="1" <?php echo $post['order_include_all_customers'] ? 'checked="checked"': '' ?> />
										<label for="order_include_all_customers"><?php _e('Export all customers', 'wp_all_export_plugin') ?></label>							
										<a href="#help" class="wpallexport-help" style="position: relative; top: -2px;" title="<?php _e('All customers will be exported, not only those that created these orders.', 'wp_all_export_plugin'); ?>">?</a>
									</div>																				
								</span>			
							</div>							
							<?php endif; ?>
							<br>
							<hr>
							<p style="text-align:right;">
								<div class="input">
									<label for="save_import_as" style="width: 103px;"><?php _e('Friendly Name:','wp_all_export_plugin');?></label> 
									<input type="text" name="friendly_name" title="<?php _e('Save friendly name...', 'pmxi_plugin') ?>" style="vertical-align:middle; background:#fff !important;" value="<?php echo esc_attr($post['friendly_name']) ?>" />
								</div>
							</p>
						</td>
					</tr>											
				</table>
			</div>
		</div>
	</div>
</div>	