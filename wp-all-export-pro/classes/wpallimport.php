<?php

final class PMXE_Wpallimport
{
	/**
	 * Singletone instance
	 * @var PMXE_Wpallimport
	 */
	protected static $instance;

	/**
	 * Return singletone instance
	 * @return PMXE_Wpallimport
	 */
	static public function getInstance() {
		if (self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct(){}

	public static function create_an_import( & $export )
	{

		$custom_type = (empty($export->options['cpt'])) ? 'post' : $export->options['cpt'][0];

		// Do not create an import for WooCommerce Orders & Refunds
		// if ( in_array($custom_type, array('shop_order'))) return false;

		if ( $export->options['is_generate_import'] and wp_all_export_is_compatible() ){				
				
			$import = new PMXI_Import_Record();

			if ( ! empty($export->options['import_id']) ) $import->getById($export->options['import_id']);

			if ($import->isEmpty())
			{
				$import->set(array(		
					'parent_import_id' => 99999,
					'xpath' => '/',			
					'type' => 'upload',																
					'options' => array('empty'),
					'root_element' => 'root',
					'path' => 'path',					
					'imported' => 0,
					'created' => 0,
					'updated' => 0,
					'skipped' => 0,
					'deleted' => 0,
					'iteration' => 1					
				))->save();					

				if ( ! empty(PMXE_Plugin::$session) and PMXE_Plugin::$session->has_session() ) 
				{
					PMXE_Plugin::$session->set('import_id', $import->id);
				}				
				$options = $export->options;
				$options['import_id'] = $import->id;

				$export->set(array(
					'options' => $options
				))->save();
			}
			else
			{
				if ( $import->parent_import_id != 99999 )
				{
					$newImport = new PMXI_Import_Record();

					$newImport->set(array(		
						'parent_import_id' => 99999,
						'xpath' => '/',			
						'type' => 'upload',																
						'options' => array('empty'),
						'root_element' => 'root',
						'path' => 'path',						
						'imported' => 0,
						'created' => 0,
						'updated' => 0,
						'skipped' => 0,
						'deleted' => 0,
						'iteration' => 1					
					))->save();					

					if ( ! empty(PMXE_Plugin::$session) and PMXE_Plugin::$session->has_session() ) 
					{
						PMXE_Plugin::$session->set('import_id', $newImport->id);
					}

					$options = $export->options;
					$options['import_id'] = $newImport->id;

					$export->set(array(
						'options' => $options
					))->save();
				}
				else
				{
					global $wpdb;
					$post = new PMXI_Post_List();
					$wpdb->query($wpdb->prepare('DELETE FROM ' . $post->getTable() . ' WHERE import_id = %s', $import->id));					
				}
			}
		}
	}

	public static $templateOptions = array();
	public static function generateImportTemplate( & $export, $file_path = '', $foundPosts = 0, $link_to_import = true )
	{
		$exportOptions = $export->options;

		$custom_type = (empty($exportOptions['cpt'])) ? 'post' : $exportOptions['cpt'][0];

		// Do not create an import template for WooCommerce Refunds
		// if ( empty($export->parent_id) and in_array($custom_type, array('shop_order_refund'))) return false;

		// Generate template for WP All Import	
		if ($exportOptions['is_generate_templates'])
		{
			self::$templateOptions = array(
				'type' => ( ! empty($exportOptions['cpt']) and $exportOptions['cpt'][0] == 'page') ? 'page' : 'post',
				'wizard_type' => 'new',
				'deligate' => 'wpallexport',
				'custom_type' => (XmlExportEngine::$is_user_export) ? 'import_users' : $custom_type,
				'status' => 'xpath',
				'is_multiple_page_parent' => 'no',
				'unique_key' => '',
				'acf' => array(),
				'fields' => array(),
				'is_multiple_field_value' => array(),				
				'multiple_value' => array(),
				'fields_delimiter' => array(),								
				'update_all_data' => 'no',
				'is_update_status' => 0,
				'is_update_title'  => 0,
				'is_update_author' => 0,
				'is_update_slug' => 0,
				'is_update_content' => 0,
				'is_update_excerpt' => 0,
				'is_update_dates' => 0,
				'is_update_menu_order' => 0,
				'is_update_parent' => 0,
				'is_update_attachments' => 0,
				'is_update_acf' => 0,
				'update_acf_logic' => 'only',
				'acf_list' => '',					
				'is_update_product_type' => 1,
				'is_update_attributes' => 0,
				'update_attributes_logic' => 'only',
				'attributes_list' => '',
				'is_update_images' => 0,
				'is_update_custom_fields' => 0,
				'update_custom_fields_logic' => 'only',
				'custom_fields_list' => '',												
				'is_update_categories' => 0,
				'update_categories_logic' => 'only',
				'taxonomies_list' => '',
				'export_id' => $export->id
			);					

			if ( in_array('product', $exportOptions['cpt']) and class_exists('PMWI_Plugin'))
			{
				$default = PMWI_Plugin::get_default_import_options();

				self::$templateOptions = array_replace_recursive(self::$templateOptions, $default);

				self::$templateOptions['_virtual'] = 1;
				self::$templateOptions['_downloadable'] = 1;
				self::$templateOptions['put_variation_image_to_gallery'] = 1;
				self::$templateOptions['disable_auto_sku_generation'] = 1;							
			}	

			if ( in_array('shop_order', $exportOptions['cpt']) and class_exists('PMWI_Plugin'))
			{
				self::$templateOptions['update_all_data'] = 'no';
				self::$templateOptions['is_update_status'] = 0;
				self::$templateOptions['is_update_dates'] = 0;
				self::$templateOptions['is_update_excerpt'] = 0;

				$default = PMWI_Plugin::get_default_import_options();

				self::$templateOptions['pmwi_order'] = $default['pmwi_order'];		
				
				self::$templateOptions['pmwi_order']['is_update_billing_details'] = 0;
				self::$templateOptions['pmwi_order']['is_update_shipping_details'] = 0;
				self::$templateOptions['pmwi_order']['is_update_payment'] = 0;
				self::$templateOptions['pmwi_order']['is_update_notes'] = 0;
				self::$templateOptions['pmwi_order']['is_update_products'] = 0;
				self::$templateOptions['pmwi_order']['is_update_fees'] = 0;
				self::$templateOptions['pmwi_order']['is_update_coupons'] = 0;
				self::$templateOptions['pmwi_order']['is_update_shipping'] = 0;
				self::$templateOptions['pmwi_order']['is_update_taxes'] = 0;
				self::$templateOptions['pmwi_order']['is_update_refunds'] = 0;
				self::$templateOptions['pmwi_order']['is_update_total'] = 0;
			}			

			if ( XmlExportEngine::$is_user_export )
			{									
				self::$templateOptions['is_update_first_name'] = 0;
				self::$templateOptions['is_update_last_name'] = 0;
				self::$templateOptions['is_update_role'] = 0;
				self::$templateOptions['is_update_nickname'] = 0;
				self::$templateOptions['is_update_description'] = 0;
				self::$templateOptions['is_update_login'] = 0;
				self::$templateOptions['is_update_password'] = 0;
				self::$templateOptions['is_update_nicename'] = 0;
				self::$templateOptions['is_update_email'] = 0;
				self::$templateOptions['is_update_registered'] = 0;
				self::$templateOptions['is_update_display_name'] = 0;
				self::$templateOptions['is_update_url'] = 0;
			}

			self::prepare_import_template( $exportOptions );												

			$tpl_options = self::$templateOptions;

			if ( 'csv' == $exportOptions['export_to'] ) 
			{						
				$tpl_options['delimiter'] = $exportOptions['delimiter'];
				$tpl_options['root_element'] = 'node';
			}
			else
			{
				$tpl_options['root_element'] = $exportOptions['record_xml_tag'];
			}
			
			$tpl_options['update_all_data'] = 'yes';
			$tpl_options['is_update_status'] = 1;
			$tpl_options['is_update_title']  = 1;
			$tpl_options['is_update_author'] = 1;
			$tpl_options['is_update_slug'] = 1;
			$tpl_options['is_update_content'] = 1;
			$tpl_options['is_update_excerpt'] = 1;
			$tpl_options['is_update_dates'] = 1;
			$tpl_options['is_update_menu_order'] = 1;
			$tpl_options['is_update_parent'] = 1;
			$tpl_options['is_update_attachments'] = 1;
			$tpl_options['is_update_acf'] = 1;
			$tpl_options['update_acf_logic'] = 'full_update';
			$tpl_options['acf_list'] = '';
			$tpl_options['is_update_product_type'] = 1;
			$tpl_options['is_update_attributes'] = 1;
			$tpl_options['update_attributes_logic'] = 'full_update';
			$tpl_options['attributes_list'] = '';
			$tpl_options['is_update_images'] = 1;
			$tpl_options['is_update_custom_fields'] = 1;
			$tpl_options['update_custom_fields_logic'] = 'full_update';
			$tpl_options['custom_fields_list'] = '';
			$tpl_options['is_update_categories'] = 1;
			$tpl_options['update_categories_logic'] = 'full_update';
			$tpl_options['taxonomies_list'] = '';					

			$tpl_data = array(						
				'name' => $exportOptions['template_name'],
				'is_keep_linebreaks' => 0,
				'is_leave_html' => 0,
				'fix_characters' => 0,
				'options' => $tpl_options,							
			);

			$exportOptions['tpl_data'] = $tpl_data;

			$export->set(array(
				'options' => $exportOptions
			))->save();						

		}

		$link_to_import and $export->options['is_generate_import'] and self::link_template_to_import( $export, $file_path, $foundPosts );
	}

	public static function link_template_to_import( & $export, $file_path, $foundPosts )
	{
		
		$exportOptions = $export->options;		

		// associate exported posts with new import
		if ( wp_all_export_is_compatible() )
		{
			$options = self::$templateOptions + PMXI_Plugin::get_default_import_options();											
									
			$import = new PMXI_Import_Record();

			$import->getById($exportOptions['import_id']);	

			if ( ! $import->isEmpty() and $import->parent_import_id == 99999 ){

				$xmlPath = $file_path;

				$root_element = '';

				$historyPath = $file_path;

				if ( 'csv' == $exportOptions['export_to'] ) 
				{
					$is_secure_import = PMXE_Plugin::getInstance()->getOption('secure');

					$options['delimiter'] = $exportOptions['delimiter'];

					include_once( PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php' );	

					$path_info = pathinfo($xmlPath);

					$path_parts = explode(DIRECTORY_SEPARATOR, $path_info['dirname']);

					$security_folder = array_pop($path_parts);

					$wp_uploads = wp_upload_dir();	

					$target = $is_secure_import ? $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXE_Plugin::UPLOADS_DIRECTORY . DIRECTORY_SEPARATOR . $security_folder : $wp_uploads['path'];						

					$csv = new PMXI_CsvParser( array( 'filename' => $xmlPath, 'targetDir' => $target ) );		

					if ( ! in_array($xmlPath, $exportOptions['attachment_list']) )
					{
						$exportOptions['attachment_list'][] = $csv->xml_path;							
					}
					
					$historyPath = $csv->xml_path;

					$root_element = 'node';

				}
				else
				{
					$root_element = apply_filters('wp_all_export_record_xml_tag', $exportOptions['record_xml_tag'], $export->id);
				}

				$import->set(array(						
					'xpath' => '/' . $root_element,
					'type' => 'upload',											
					'options' => $options,
					'root_element' => $root_element,
					'path' => $xmlPath,
					'name' => basename($xmlPath),
					'imported' => 0,
					'created' => 0,
					'updated' => 0,
					'skipped' => 0,
					'deleted' => 0,
					'iteration' => 1,		
					'count' => $foundPosts				
				))->save();				

				$history_file = new PMXI_File_Record();
				$history_file->set(array(
					'name' => $import->name,
					'import_id' => $import->id,
					'path' => $historyPath,
					'registered_on' => date('Y-m-d H:i:s')
				))->save();		

				$exportOptions['import_id']	= $import->id;					
				
				$export->set(array(
					'options' => $exportOptions
				))->save();		
			}																	
		}
	}

	public static function prepare_import_template( $exportOptions )
	{
		$options = $exportOptions;

		$is_xml_template = $options['export_to'] == 'xml';

		$required_add_ons = array();

		$cf_list   = array();
		$attr_list = array();
		$taxs_list = array();
		$acf_list  = array();

		$implode_delimiter = ($options['delimiter'] == ',') ? '|' : ',';

		if ( ! empty($options['is_user_export']) ) self::$templateOptions['pmui']['import_users'] = 1;

		foreach ($options['ids'] as $ID => $value) 
		{
			if (empty($options['cc_type'][$ID])) continue;

			if ($is_xml_template)
			{
				$element_name = (!empty($options['cc_name'][$ID])) ? str_replace(':', '_', preg_replace('/[^a-z0-9_:-]/i', '', $options['cc_name'][$ID])) : 'untitled_' . $ID;
			}
			else
			{
				$element_name = strtolower((!empty($options['cc_name'][$ID])) ? preg_replace('/[^a-z0-9_]/i', '', $options['cc_name'][$ID]) : 'untitled_' . $ID);
			}			

			$element_type = $options['cc_type'][$ID];

			switch ($element_type) 
			{
				case 'woo':
					
					if ( ! empty($options['cc_value'][$ID]) )
					{												
						if (empty($required_add_ons['PMWI_Plugin']))
						{
							$required_add_ons['PMWI_Plugin'] = array(
								'name' => 'WooCommerce Add-On Pro',
								'paid' => true,
								'url'  => 'http://www.wpallimport.com/woocommerce-product-import/'
							);
						}

						XmlExportWooCommerce::prepare_import_template( $options, self::$templateOptions, $cf_list, $attr_list, $element_name, $options['cc_label'][$ID] );
					}

					break;

				case 'acf':					

					if (empty($required_add_ons['PMAI_Plugin']))
					{
						$required_add_ons['PMAI_Plugin'] = array(
							'name' => 'ACF Add-On Pro',
							'paid' => true,
							'url'  => 'http://www.wpallimport.com/advanced-custom-fields/?utm_source=wordpress.org&utm_medium=wpai-import-template&utm_campaign=free+wp+all+export+plugin'
						);
					}

					$field_options = unserialize($options['cc_options'][$ID]);

					// add ACF group ID to the template options
					if( ! empty($templateOptions['acf']) and ! in_array($field_options['group_id'], $templateOptions['acf'])){
						$templateOptions['acf'][$field_options['group_id']] = 1;
					}					

					$templateOptions['fields'][$field_options['key']] = XmlExportACF::prepare_import_template( $options, self::$templateOptions, $acf_list, $element_name, $field_options);											 

					break;				

				default:

					XmlExportCpt::prepare_import_template( $options, self::$templateOptions, $cf_list, $attr_list, $taxs_list, $element_name, $ID);
					
					XmlExportMediaGallery::prepare_import_template( $options, self::$templateOptions, $element_name, $ID);

					XmlExportUser::prepare_import_template( $options, self::$templateOptions, $element_name, $ID);

					XmlExportWooCommerceOrder::prepare_import_template( $options, self::$templateOptions, $element_name, $ID);

					break;
			}
		}

		if ( ! empty($cf_list) )
		{
			self::$templateOptions['is_update_custom_fields'] = 1;
			self::$templateOptions['custom_fields_list'] = $cf_list;
		}
		if ( ! empty($attr_list) )
		{
			self::$templateOptions['is_update_attributes'] = 1;
			self::$templateOptions['attributes_list'] = $attr_list;
			self::$templateOptions['attributes_only_list'] = implode(',', $attr_list);
		}
		if ( ! empty($taxs_list) )
		{
			self::$templateOptions['is_update_categories'] = 1;
			self::$templateOptions['taxonomies_list'] = $taxs_list;
		}
		if ( ! empty($acf_list) )
		{
			self::$templateOptions['is_update_acf'] = 1;
			self::$templateOptions['acf_list'] = $acf_list;
		}

		self::$templateOptions['required_add_ons'] = $required_add_ons;
	}
}

PMXE_Wpallimport::getInstance();