<?php
class media_upload{
	
	private $upload_div 	= "upload-hub";
	private $thumbnail_div 	= "media-thumbs";
	private $core_js_file 	= "plugins/dropzone/dropzone.js";
	private $core_css_file 	= "plugins/dropzone/dropzone.css";
	private $thumbnails 	= "";
	private $mediapath		= FILEROOT.PATH_PHOTO;
	
	/*** JS VARS ***/
	private $url 					= "calls/media_upload.php"; 
	private $acceptedFiles 			= "image/*";
	private $thumbnailWidth 		= 100;
	private $thumbnailHeight 		= 100;
	private $thumbnailEyeHeight 	= 22;
	private $thumbnailLineHeight 	= 135;
	private $maxFiles		 		= 'null';
	
	/*** GLOBAL VARS HOLDERS ***/
	private $db, $page, $record, $pagehash, $translate;
	
	
	function __construct($thumbnailWidth = false, $thumbnailHeight = false, $maxFiles = false, $acceptedFiles = false, $url = false){
		
		// get global vars
		global $db, $pid, $_record, $_pagehash, $_t, $js_assets, $css_assets;
		
		// save global vars locally
		$this->db 			= $db;
		$this->page 		= $pid;
		$this->record 		= $_record;
		$this->pagehash 	= $_pagehash;
		$this->translate 	= $_t;
		
		
		// if any value is passed memorize it locally
		if(!empty($thumbnailWidth)) $this->thumbnailWidth = $thumbnailWidth;
		if(!empty($thumbnailHeight)) $this->thumbnailHeight = $thumbnailHeight;
		if(!empty($acceptedFiles)) $this->acceptedFiles = $acceptedFiles;
		if(!empty($url)) $this->url = $url;
		if(!empty($maxFiles)) $this->maxFiles = (int) $maxFiles;
		
		$this->maxText = $_t->get('max-media-file-message');
		$this->hubtext = $_t->get('upload_media');
		
		// calculate lineheight of trash icon
		$this->thumbnailLineHeight = $this->thumbnailHeight-$this->thumbnailEyeHeight;
		
		
		// if page and record are not empty try to fetch thumbnails from db
		if(!empty($_record) and !empty($pid)){
			$thumbs = $db->select_all(DBTABLE_MEDIA, "WHERE page = '".$pid."' AND record = '".$_record."' ORDER BY `order`");
			if($thumbs){
				$this->setThumbs($thumbs);
			}
		}
		
		// add lightbox dependenicies to queue
		$js_assets[] = "plugins/fancybox/source/jquery.fancybox.js";
		$css_assets[] = "plugins/fancybox/source/jquery.fancybox.css";
		
		// clean up media records and files
		$this->cleanup();
		
		return true; // to be sure	
	}
    
    public function setDiv($name = false){
        
        if(empty($name) of !is_string($name)) return false;
        
        $this->upload_div = trim($name);
        
        return true;
        
    }

    /**
     * Setto nome sottocartella in cui collocare le foto caricate, 
     */
    public function setMediaSubPath($path = false){
        
        if(empty($path) of !is_string($path)) return false;
        $path = trim($path);
                
        $this->mediapath = FILEROOT.PATH_PHOTO . $path;
        
        return true;
        
    }

    /**
     * Sostituisco path default con nuova path. Usare con cautela! 
     */
    public function setMediaPath($path = false){
        
        if(empty($path) of !is_string($path)) return false;
        $path = trim($path);
                
        $this->mediapath = $path;
        
        return true;
        
    }

	
	/************** GET VALUES **************/
	
	// returns link to core dropzone js file
	public function getCoreJs(){
		return "<script src=\"".$this->core_js_file."\"></script>\n";
	}

	// returns js functions for setup
	public function setup(){
		global $canedit;
		$output = "
			$(document).ready(function() {
				$('.fancybox').fancybox();
			});
		";
		if($canedit){
			$output .= "
				$(document).ready(function() {

					MyDropzone = new Dropzone(
						\"div#".$this->upload_div.".enabled\",
						{

							url: \"".$this->url."\", 
							previewsContainer: \"#".$this->thumbnail_div."\",
							acceptedFiles: \"".$this->acceptedFiles."\",
							thumbnailWidth: ".$this->thumbnailWidth.",
							thumbnailHeight: ".$this->thumbnailHeight.",
							maxFiles: ".$this->maxFiles.", 
							uploadMultiple: false,

							previewTemplate: \"<div class='dz-preview dz-file-preview'><div class='dz-details'><div class='dz-filename'><span data-dz-name></span></div><div class='dz-size' data-dz-size></div><img data-dz-thumbnail /></div><div class='dz-progress'><span class='dz-upload' data-dz-uploadprogress></span></div><div class='dz-success-mark'><span><a href='' class='fancybox'><i class='fa fa-eye'></i></a></span></div><div class='dz-error-mark'><span><i class='fa fa-trash'></i></span></div><div class='dz-error-message'><span data-dz-errormessage></span></div></div>	\",

							init: function() { 
								this.on(\"processing\", function(file, responseText) { 
									showDropzoneLoader(true);
								});
								this.on(\"complete\", function(file, responseText) { 
									setTimeout(function(){ switchDropzone(); showDropzoneLoader(false); }, 200);

								});
								this.on(\"success\", function(file, responseText) { 
									var response = JSON.parse(responseText);
									if(response.result){
										var hidden = \"<input type='hidden' name='media[]' id='media_\"+response.id+\"' value='\"+response.id+\"'>\";
										$(\"form\").append(hidden);
										var t = file.previewTemplate;
										var e = $(t).find(\".dz-error-mark span\");
										e.data(\"id\", response.id);
										var h = file.previewElement;
										$(h).find('a').prop('href', '".PATH_PHOTO."'+response.filename);

									}else{
										 modal(response.msg, response.error, response.errorlevel);
									}
								}); 
							},
							params: { \"page\": \"".$this->page."\", \"record\" : \"".$this->record."\" }


						}
					);
					switchDropzone();

					// remove thumbnail, thus image
					$(document).on(\"click\", \".dz-success .dz-error-mark span\", function(){
						var t = $(this);
						var p = t.closest(\"div.dz-preview\");
						if(confirm(\"".$this->translate->get('delete_media_confirm')."\")){

							$.post(  
							 \"calls/media_delete.php\",  
							 {id: t.data(\"id\")},  
							 function(response){

								 if (response.result){													
									p.fadeOut(\"fast\", function(){ $(this).remove();  switchDropzone(); });								
								 }else{
									 modal(response.msg, response.error, response.errorlevel);
								 }
							 },  
							 \"json\"  
							);  	
						}
					});
				});	

				// see if the dropzone hub must be disabled or reenabled
				function switchDropzone(){
					var thumbs = $('.dz-image-preview').length;
					var maxFiles = ".$this->maxFiles.";
					if(maxFiles === null) return true; // don't have restrictions so exit
					if(thumbs >= maxFiles){
						MyDropzone.disable();					
						$(\"#".$this->upload_div." span\").html(\"".$this->maxText."\");
						$(\"#".$this->upload_div."\").addClass('disabled')
						$(\"#".$this->upload_div."\").removeClass('enabled')
					}else{

						if( $(\"#".$this->upload_div."\").hasClass('disabled') ){
							MyDropzone.enable();
							$(\"#".$this->upload_div." span\").html(\"".$this->hubtext."\");
							$(\"#".$this->upload_div."\").removeClass('disabled')
							$(\"#".$this->upload_div."\").addClass('enabled')
						} // end if hasClass

					} // end else

				} // end function

				function showDropzoneLoader(switchOn){
					if(switchOn){
						var div = $('<div>');
						div.prop(\"id\", 'dzloader');
						div.addClass(\"overlay\");
						var i = $(\"<i>\");
						i.addClass(\"fa fa-refresh fa-spin\");
						i.appendTo(div);
						$(\"#".$this->upload_div."\").append(div);
					}else{
						$('#dzloader').remove();
					}

				}
			";
		}
		return $output;
	}
	
	// returns styling
	public function getCss(){
		$font_size = (int) round($this->thumbnailHeight / 3);
		// style sheet
		$out = "<link rel=\"stylesheet\" href=\"".$this->core_css_file."\">\n";
		
		$out .= "<style type=\"text/css\">\n\n";
		
		if($this->thumbnailHeight){
		$out .= "	.dz-preview{
						height: ".$this->thumbnailHeight."px;
					}
				";
		}
					
		$out .= "	.dz-success .dz-error-mark{
						font-size: ".$font_size."px;
						line-height: ".$this->thumbnailLineHeight."px;
					}

					.dz-success .dz-success-mark{
						line-height: ".$this->thumbnailEyeHeight."px;
					}";

		if($this->thumbnailHeight or $this->thumbnailWidth){
			$out .= "	.placeholder-thumb{\n";
			$out .= (empty($this->thumbnailHeight)) ? "" : "		height: ".$this->thumbnailHeight."px\n";
			$out .= (empty($this->thumbnailWidth))  ? "" : "		width: ".$this->thumbnailWidth."px\n";
			$out .= "	}\n";
		}
							
		$out .= "</style>\n";
		return $out;
	}
	
	// returns html with the upload hub div and the thumbnail holder
	public function getHtml(){
		global $canedit;
		$class = ($canedit) ? "enabled" : "disabled";
		$out  = "<div class='".$class."' id=\"".$this->upload_div."\"><span>".$this->hubtext."</span></div>\n";
		$out .= "<div class='".$class."' id=\"".$this->thumbnail_div."\">".$this->thumbnails."</div>\n";
		return $out;
	}
	
	// delete all the records in the 'media' tab that have record set to 0, page id of current page and have been uploaded by the current user (identified by unique id of log_access table)
	public function cleanup(){
		if( !empty($_SESSION['access_log_id']) ){
			$list = $this->db->col_value("file", DBTABLE_MEDIA, "WHERE page='".$this->page."' AND record = '0' AND uploadedby = '".$_SESSION['access_log_id']."'");
			if( !empty($list) ){
				foreach($list as $filename){
					unlink( $this->mediapath.$filename );					
				}
				$this->db->delete(DBTABLE_MEDIA, "WHERE page='".$this->page."' AND record = '0' AND uploadedby = '".$_SESSION['access_log_id']."'");
			}
		}
	}
	
	// delete mediafile that don't have an corrisponding entry inb the media table - could take a while
	public function deleteOrfans(){
		// get all filenames from db
		$filesdb = $this->db->col_value("file", DBTABLE_MEDIA);
		$c=0;
		$iterator = new DirectoryIterator($this->mediapath);
		foreach ($iterator as $fileinfo) {
			
			if ($fileinfo->isFile() and !in_array($fileinfo->getFilename(), $filesdb)) {
				$c++;
				unlink( $this->mediapath.$fileinfo->getFilename() );
			}
		}
		
		return $c;
		
	}
		
		
		
	public function getMediaPath(){
		return $this->mediapath;
	}
	
	
	/************** SET VALUES **************/
	
	// use html of dropzone to show already assignd thumbnails 
	private function setThumbs($thumbs){
		foreach($thumbs as $thumb){
			global $canedit;
			$tag_width = (empty($this->thumbnailWidth)) ? "" : "width=\"".$this->thumbnailWidth."\"";
			$tag_height = (empty($this->thumbnailHeight)) ? "" : "height=\"".$this->thumbnailHeight."\"";
			$img_qry =  "required/img.php?q=80&file=../".PATH_PHOTO.$thumb['file']."&c=1";
			if(!empty($this->thumbnailWidth)) $img_qry .= "&w=".$this->thumbnailWidth;
			if(!empty($this->thumbnailHeight)) $img_qry .= "&h=".$this->thumbnailHeight;
			$this->thumbnails .= "
				<div class=\"dz-preview dz-processing dz-success dz-image-preview\">\n
					<div class=\"dz-details\">\n
						<div class=\"dz-filename\"><span data-dz-name=\"\">".$thumb['name']."</span></div>\n						
						<div data-dz-size=\"\" class=\"dz-size\"><strong>".$thumb['size']."</strong> KiB</div>						
						<img ".$tag_width." ".$tag_height." data-dz-thumbnail=\"\" alt=\"".$thumb['name']."\" src=\"".$img_qry."\">\n
					</div>\n
					<div class=\"dz-success-mark\"><span><a href=\"".PATH_PHOTO.$thumb['file']."\"' class=\"fancybox\"><i class=\"fa fa-eye\"></i></a></span></div>\n";
			$this->thumbnails .= ($canedit) ? "
					<div class=\"dz-error-mark\"><span data-id=\"".$thumb['id']."\"><i class=\"fa fa-trash\"></i></span></div>\n" : "";
			$this->thumbnails .= "
				</div>\n\n";
		}	
	}

	public function setHubId($name){
		$this->upload_div = $name;
	}

	public function setThumbId($name){
		$this->thumbnail_div = $name;
	}

	public function setCoreJs($name){
		$this->core_js_file = $name;
	}

	public function setCoreCss($name){
		$this->core_css_file = $name;
	}

	public function setHelpJs($name){
		$this->help_js_file = $name;
	}

	public function setMediaPath($path){
		$this->mediapath = $path;
	}

	


}
?>