<?php

function pmxe_pmxe_after_export($export_id, $export)
{		
	if ( ! empty(PMXE_Plugin::$session) and PMXE_Plugin::$session->has_session() )
	{
		PMXE_Plugin::$session->set('file', '');
		PMXE_Plugin::$session->save_data();				
	}

	if ( ! $export->isEmpty())
	{						
		$splitSize = $export->options['split_large_exports_count'];			

		$exportOptions = $export->options;
		// remove previously genereted chunks
		if ( ! empty($exportOptions['split_files_list']) and ! $export->options['creata_a_new_export_file'] )
		{
			foreach ($exportOptions['split_files_list'] as $file) {
				@unlink($file);
			}
		}		

		$is_secure_import = PMXE_Plugin::getInstance()->getOption('secure');

		if ( ! $is_secure_import)
		{
			$filepath = get_attached_file($export->attch_id);					
		}
		else
		{
			$filepath = wp_all_export_get_absolute_path($export->options['filepath']);
		}		

		// Split large exports into chunks
		if ( $export->options['split_large_exports'] and $splitSize < $export->exported )
		{

			$exportOptions['split_files_list'] = array();							

			if ( @file_exists($filepath) )
			{					

				switch ($export->options['export_to']) 
				{
					case 'xml':

						$main_xml_tag = apply_filters('wp_all_export_main_xml_tag', $export->options['main_xml_tag'], $export->id);
						$record_xml_tag = apply_filters('wp_all_export_record_xml_tag', $export->options['record_xml_tag'], $export->id);

						$records_count = 0;
						$chunk_records_count = 0;
						$fileCount = 1;

						$feed = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"  . "\n" . "<".$main_xml_tag.">";

						$file = new PMXE_Chunk($filepath, array('element' => $record_xml_tag, 'encoding' => 'UTF-8'));
						// loop through the file until all lines are read				    				    			   			   	    			    			    
					    while ($xml = $file->read()) {				    	

					    	if ( ! empty($xml) )
					      	{					      		
					      		$chunk = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"  . "\n" . $xml;					      		
						      					      		
						      	$dom = new DOMDocument('1.0', "UTF-8");
								$old = libxml_use_internal_errors(true);
								$dom->loadXML($chunk); // FIX: libxml xpath doesn't handle default namespace properly, so remove it upon XML load											
								libxml_use_internal_errors($old);
								$xpath = new DOMXPath($dom);								

								$records_count++;
								$chunk_records_count++;
								$feed .= $xml;								
							}

							if ( $chunk_records_count == $splitSize or $records_count == $export->exported ){
								$feed .= "</".$main_xml_tag.">";
								$outputFile = str_replace(basename($filepath), str_replace('.xml', '', basename($filepath)) . '-' . $fileCount++ . '.xml', $filepath);
								file_put_contents($outputFile, $feed);
								if ( ! in_array($outputFile, $exportOptions['split_files_list']))
						        	$exportOptions['split_files_list'][] = $outputFile;
								$chunk_records_count = 0;
								$feed = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"  . "\n" . "<".$main_xml_tag.">";
							}

						}							
						break;
					case 'csv':
						$in = fopen($filepath, 'r');

						$rowCount  = 0;
						$fileCount = 1;
						$headers = fgetcsv($in);
						while (!feof($in)) {
						    $data = fgetcsv($in);
						    if (empty($data)) continue;
						    if (($rowCount % $splitSize) == 0) {
						        if ($rowCount > 0) {
						            fclose($out);
						        }						        
						        $outputFile = str_replace(basename($filepath), str_replace('.csv', '', basename($filepath)) . '-' . $fileCount++ . '.csv', $filepath);
						        if ( ! in_array($outputFile, $exportOptions['split_files_list']))
						        	$exportOptions['split_files_list'][] = $outputFile;

						        $out = fopen($outputFile, 'w');						        
						    }						    
						    if ($data){				
						    	if (($rowCount % $splitSize) == 0) {
						    		fputcsv($out, $headers);
						    	}		    	
						        fputcsv($out, $data);
						    }
						    $rowCount++;
						}
						fclose($in);	
						fclose($out);	

						// convert splitted files into XLS format
						if ( ! empty($exportOptions['split_files_list']) && ! empty($export->options['export_to_sheet']) and $export->options['export_to_sheet'] == 'xls' )
						{
							require_once PMXE_Plugin::ROOT_DIR . '/classes/PHPExcel/IOFactory.php';

							foreach ($exportOptions['split_files_list'] as $key => $file) 
							{
								$objReader = PHPExcel_IOFactory::createReader('CSV');
								// If the files uses a delimiter other than a comma (e.g. a tab), then tell the reader
								$objReader->setDelimiter($export->options['delimiter']);
								// If the files uses an encoding other than UTF-8 or ASCII, then tell the reader
								$objPHPExcel = $objReader->load($file);
								$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
								$objWriter->save(str_replace(".csv", ".xls", $file));
								@unlink($file);

								$exportOptions['split_files_list'][$key] = str_replace(".csv", ".xls", $file);
							}
						}

						break;
					
					default:
						
						break;
				}				

				$export->set(array('options' => $exportOptions))->save();
			}	
		}	

		// convert CSV to XLS
		if ( @file_exists($filepath) and $export->options['export_to'] == 'csv' && ! empty($export->options['export_to_sheet']) and $export->options['export_to_sheet'] == 'xls')
		{			
			
			require_once PMXE_Plugin::ROOT_DIR . '/classes/PHPExcel/IOFactory.php';

			$objReader = PHPExcel_IOFactory::createReader('CSV');
			// If the files uses a delimiter other than a comma (e.g. a tab), then tell the reader
			$objReader->setDelimiter($export->options['delimiter']);
			// If the files uses an encoding other than UTF-8 or ASCII, then tell the reader

			$objPHPExcel = $objReader->load($filepath);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save(str_replace(".csv", ".xls", $filepath));
			@unlink($filepath);

			$filepath = str_replace(".csv", ".xls", $filepath);

			$exportOptions = $export->options;
			$exportOptions['filepath'] = wp_all_export_get_relative_path($filepath);
			$export->set(array('options' => $exportOptions))->save();
		}

		// make a temporary copy of current file
		if ( empty($export->parent_id) and @file_exists($filepath) and @copy($filepath, str_replace(basename($filepath), '', $filepath) . 'current-' . basename($filepath)))
		{
			$exportOptions = $export->options;
			$exportOptions['current_filepath'] = str_replace(basename($filepath), '', $filepath) . 'current-' . basename($filepath);						
			$export->set(array('options' => $exportOptions))->save();
		}

		// genereta export bundle
		$export->generate_bundle();		

		if ( ! empty($export->parent_id) ) 
		{
			$parent_export = new PMXE_Export_Record();
			$parent_export->getById($export->parent_id);
			if ( ! $parent_export->isEmpty() )
			{				
				$parent_export->generate_bundle(true);						
			}
		} 

		// send exported data to zapier.com
		$subscriptions = get_option('zapier_subscribe', array());		
		if ( ! empty($subscriptions) and empty($export->parent_id))
		{			

			$wp_uploads = wp_upload_dir();

			$fileurl = str_replace($wp_uploads['basedir'], $wp_uploads['baseurl'], $filepath);		

			$response = apply_filters('wp_all_export_zapier_response', array( 				
				'website_url' => home_url(),
				'export_id' => $export->id, 
				'export_name' => $export->friendly_name,
				'file_name' => basename($filepath),
				'file_type' => wp_all_export_get_export_format($export->options),
				'post_types_exported' => empty($export->options['cpt']) ? $export->options['wp_query'] : implode($export->options['cpt'], ','),
				'export_created_date' => $export->registered_on,
				'export_last_run_date' => date('Y-m-d H:i:s'),
				'export_trigger_type' => empty($_GET['export_key']) ? 'manual' : 'cron',
				'records_exported' => $export->exported,
				'export_file' => ''
			));

			if (file_exists($filepath))
			{
				$response['export_file_url'] = $fileurl;
				$response['status'] = 200;
				$response['message'] = 'OK';	
			}
			else
			{
				$response['export_file_url'] = '';
				$response['status'] = 300;
				$response['message'] = 'File doesn\'t exist';	
			}

			foreach ($subscriptions as $zapier) 
			{
				if (empty($zapier['target_url'])) continue;

				wp_remote_post( $zapier['target_url'], array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(
							'Content-Type' => 'application/json'
						),
					'body' => "[".json_encode($response)."]",
					'cookies' => array()
				    )
				);
			}			
		}

		// clean session 
		if ( ! empty(PMXE_Plugin::$session) and PMXE_Plugin::$session->has_session() )
		{
			PMXE_Plugin::$session->clean_session( $export->id );				
		}
	}	
}