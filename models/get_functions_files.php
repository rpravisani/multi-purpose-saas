<?php
defined('_CCCMS') or die;
/**********************************************
 *** MODEL                                  ***
 *** filename: gestione-automezzo.php       ***
 *** Inserisci e modifica un automezzo      ***
 **********************************************/

// set search pattern 
$pattern = "/\s*(public|private|protected)?\s+function\s+([a-zA-z0-9]+)\(([a-zA-Z0-9, \$=\(\)_\"]*)\)\s*{\s*/";

// set path
$path = FILEROOT.PATH_REQUIRED."classes/";

// get all files in dir
$dir = new DirectoryIterator($path);

// start recording...
ob_start();

// start loop
foreach ($dir as $fileinfo) {
    // loop files
	if (!$fileinfo->isDot()){ // skip . and ..
		// get content of php file
		$filename = $fileinfo->getFilename();
		//$file = file_get_contents($path.$filename);
		$handle = @fopen($path.$filename, "r");

		// collect functions
		if ($handle) {
			$row = 0;
			$function_list = array();
			while (($buffer = fgets($handle, 4096)) !== false) {
				$row++;
				preg_match($pattern, $buffer, $results);
				if(!empty($results)){
					$function_list[$row]['raw'] = trim($results[0]); 
					$function_list[$row]['type'] = $results[1]; 
					$function_list[$row]['name'] = $results[2]; 
					$function_list[$row]['vars'] = $results[3]; 
				}
			}
			if (!feof($handle)) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose($handle);
		}
		
		
		// filter out all functions
		//preg_match_all($pattern, $file, $functions);
		
		if(!empty($function_list)){
			$nfunc = count($function_list);
			?>
			
			<div class="col-md-4">
              <div class="box box-solid collapsed-box">
                <div class="box-header with-border">
                  <h3 class="box-title"><?php echo $filename; ?></h3>
                  <div class="box-tools pull-right">
                    <span data-toggle="tooltip" class="badge bg-light-blue" title="Found <?php echo $nfunc; ?> functions"><?php echo $nfunc; ?></span>
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                  </div><!-- /.box-tools -->
                </div><!-- /.box-header -->
                <div class="box-body" style="display: none;">
					<ul class="list-unstyled">
					<?php
					// loop through functions
					foreach($function_list as $r=>$function){
						$type = $function['type'];
						if(!empty($type)) $type = " <small>(".$type.")</small>";
						$name = $function['name'];
						$vars_string = $function['vars'];

						echo "   <li><strong>".$name."</strong>".$type." <u class='text-light-blue'>on line <strong>".$r."</strong></u>";
						if(!empty($vars_string)){
							$vars = explode(",", $vars_string);

							echo "    <ul class='vars'>\n";
							foreach($vars as $var){
								$splitted_var = explode("=", $var);

								echo "     <li>".trim($splitted_var[0]);
								if( !empty($splitted_var[1]) ) echo " ( default: <em class='text-green'><strong>".trim($splitted_var[1])."</strong></em> )";
								echo "</li>\n";				
							}
							echo "    </ul>\n";
						}
						echo "   </li>\n";
					} // end foreach functions
					
					?>
					</ul>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
            			
			<?php			
			
		} // end if not empty functions
		
    } // end if not dot
	
} // end foreach files in dir

// end recording
$functions_list = ob_get_contents();
ob_end_clean();


?>