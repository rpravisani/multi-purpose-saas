<?php
class media_upload{
	
	private $hubtext 		= "Upload Media";
	private $upload_div 	= "upload-hub";
	private $thumbnail_div 	= "media-thumbs";
	private $core_js_file 	= "plugins/dropzone/dropzone.js";
	private $core_css_file 	= "plugins/dropzone/dropzone.css";
	private $thumbnails 	= "";
	
	/*** JS VARS ***/
	private $url 				= "calls/media_upload.php"; 
	private $acceptedFiles 		= "image/*";
	private $thumbnailWidth 	= 150;
	private $thumbnailHeight 	= 150;
	private $maxFiles		 	= 'null';
	
	/*** GLOBAL VARS HOLDERS ***/
	private $db, $page, $record, $pagehash, $translate;
	
	
	function __construct($hubtext = false, $thumbnailWidth = false, $thumbnailHeight = false, $maxFiles = false, $acceptedFiles = false, $url = false){
		
		// get global vars
		global $db, $pid, $_record, $_pagehash, $_t;
		
		// save global vars locally
		$this->db 			= $db;
		$this->page 		= $pid;
		$this->record 		= $_record;
		$this->pagehash 	= $_pagehash;
		$this->translate 	= $_t;
		
		// if any value is passed memorize it locally
		if(!empty($hubtext)) $this->hubtext = $hubtext;
		if(!empty($thumbnailWidth)) $this->thumbnailWidth = $thumbnailWidth;
		if(!empty($thumbnailHeight)) $this->thumbnailHeight = $thumbnailHeight;
		if(!empty($acceptedFiles)) $this->acceptedFiles = $acceptedFiles;
		if(!empty($url)) $this->url = $url;
		if(!empty($maxFiles)) $this->maxFiles = (int) $maxFiles;
		
		// if page and record are not empty try to fetch thumbnails from db
		if(!empty($_record) and !empty($pid)){
			$thumbs = $db->select_all(DBTABLE_MEDIA, "WHERE page = '".$pid."' AND record = '".$_record."' ORDER BY `order`");
			if($thumbs){
				$this->setThumbs($thumbs);
			}
		}
		
		return true; // to be sure	
	}

	
	/************** GET VALUES **************/
	
	// returns link to core dropzone js file
	public function getCoreJs(){
		return "<script src=\"".$this->core_js_file."\"></script>\n";
	}

	// returns js functions for setup
	public function setup(){
		global $canwrite;
		if(!$canwrite) return false;
		$output = "
			$(document).ready(function() {
				thumbs = $('.dz-image-preview').length;
				maxFiles = ".$this->maxFiles.";
				
				var mydz = $(\"div#".$this->upload_div.".enabled\").dropzone({ 
					url: \"".$this->url."\", 
					previewsContainer: \"#".$this->thumbnail_div."\",
					acceptedFiles: \"".$this->acceptedFiles."\",
					thumbnailWidth: ".$this->thumbnailWidth.",
					thumbnailHeight: ".$this->thumbnailHeight.",
					maxFiles: ".$this->maxFiles.", 
					uploadMultiple: false,
					
					previewTemplate: \"<div class='dz-preview dz-file-preview'><div class='dz-details'><div class='dz-filename'><span data-dz-name></span></div><div class='dz-size' data-dz-size></div><img data-dz-thumbnail /></div><div class='dz-progress'><span class='dz-upload' data-dz-uploadprogress></span></div><div class='dz-success-mark'><span>✔</span></div><div class='dz-error-mark'><span><i class='fa fa-trash'></i></span></div><div class='dz-error-message'><span data-dz-errormessage></span></div></div>	\",

					init: function() { 
						this.on(\"addedfile\", function(file, responseText) { 
						});
						this.on(\"success\", function(file, responseText) { 
							var response = JSON.parse(responseText);
							if(response.result){
								var hidden = \"<input type='hidden' name='media[]' id='media_\"+response.id+\"' value='\"+response.id+\"'>\";
								$(\"form\").append(hidden);
								var t = file.previewTemplate;
								var e = $(t).find(\".dz-error-mark span\");
								e.data(\"id\", response.id);
							}else{
								 modal(response.msg, response.error, response.errorlevel);
							}
						}); 
					},
					params: { \"page\": \"".$this->page."\", \"record\" : \"".$this->record."\" }
				});
				
				mydz.disable();
				
				
				$(document).on(\"click\", \".dz-success .dz-error-mark span\", function(){
					var t = $(this);
					var p = t.closest(\"div.dz-preview\");
					if(confirm(\"".$this->translate->get('delete_media_confirm')."\")){

						$.post(  
						 \"calls/media_delete.php\",  
						 {id: t.data(\"id\")},  
						 function(response){
							 
							 if (response.result){					
								p.fadeOut(\"fast\", function(){ $(this).remove(); });	
							 }else{
								 modal(response.msg, response.error, response.errorlevel);
							 }
						 },  
						 \"json\"  
						);  	
					}
				});
			});	
		";
		return $output;
	}
	
	// returns styling
	public function getCss(){
		$font_size = (int) round($this->thumbnailHeight / 3);
		// style sheet
		$out = "<link rel=\"stylesheet\" href=\"".$this->core_css_file."\">\n";
		
		$out .= "<style type=\"text/css\">\n\n
					.dz-preview{
						height: ".$this->thumbnailHeight."px;
					}
					
					.dz-success .dz-error-mark{
						font-size: ".$font_size."px;
						line-height: ".$this->thumbnailHeight."px;
					}

					.placeholder-thumb{
						height: ".$this->thumbnailHeight."px;
						width: ".$this->thumbnailWidth."px;
					}
					
							
				</style>\n";
		return $out;
	}
	
	// returns html with the upload hub div and the thumbnail holder
	public function getHtml(){
		global $canwrite;
		$class = ($canwrite) ? "enabled" : "disabled";
		$out  = "<div class='".$class."' id=\"".$this->upload_div."\">".$this->hubtext."</div>\n";
		$out .= "<div class='".$class."' id=\"".$this->thumbnail_div."\">".$this->thumbnails."</div>\n";
		return $out;
	}
	
	
	/************** SET VALUES **************/
	
	// use html of dropzone to show already assignd thumbnails - TODO: use img.php
	private function setThumbs($thumbs){
		foreach($thumbs as $thumb){
			$this->thumbnails .= "
				<div class=\"dz-preview dz-processing dz-success dz-image-preview\">\n
					<div class=\"dz-details\">\n
						<div class=\"dz-filename\"><span data-dz-name=\"\">".$thumb['name']."</span></div>\n						
						<div data-dz-size=\"\" class=\"dz-size\"><strong>".$thumb['size']."</strong> KiB</div>
						
						<img width=\"".$this->thumbnailWidth."\" height=\"".$this->thumbnailHeight."\" data-dz-thumbnail=\"\" alt=\"".$thumb['name']."\" src=\"required/img.php?q=80&file=../".PATH_PHOTO.$thumb['file']."&w=150&h=150&c=1\">\n
					</div>\n
					<div class=\"dz-success-mark\"><span>✔</span></div>\n
					<div class=\"dz-error-mark\"><span data-id=\"".$thumb['id']."\"><i class=\"fa fa-trash\"></i></span></div>\n
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

	


}
?>