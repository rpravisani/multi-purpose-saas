<?php
/**
 * Classe che gestisce la crazione e configurazioni di hub per il media upload
 * 
 * Modifiche del 2020-01-29 =====================================================
 * Rendere multi istanza, ora il div contenitore del hub ha un id unico rendendo pertanto impossibile avere più instanze sulla stessa pagina
 * Inolte lo script php in calls che riceve i file e che quindi li rinomina, li salva e li memorizza è 1 rendendo impossbile la suddivisione
 * per categoria. Aggiungere colonna in tabella media per segmentazioni nella stessa pagina:
 * (ALTER TABLE `media`  ADD `section` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Sezione della pagina a cui appartiene  il media'  AFTER `page`;)
 * (ALTER TABLE `media` ADD `subdir` VARCHAR(255) NOT NULL DEFAULT '' AFTER `record`;)
 * (ALTER TABLE `media`  ADD `filetype` VARCHAR(100) NOT NULL COMMENT 'mime type restiuito da browser p.e. image/png'  AFTER `size`;)
 * Rendere dunque variabili questi parametri passando un array di config al metodo __constuct (visto che i parametri iniziano a diventare
 * tanti)
 * Inoltre scindere la generazione delle due parti del hub (dropzone e thumbnails)
 * Rendere variabili i testi all'interno del dropzone
 */


class media_upload{
	
	/********* PHP/HTML VARS - DEFAULT VALUES *********/
	public $path_photo     = PATH_PHOTO; // percorso relativo alla root. PATH_PHOTO è definita in sitevariables (e di norma valorizzato a "photo") 
	private $mediapath     = FILEROOT.PATH_PHOTO; // percorso completo (filesys) della upload dir 
	//public $subpath        = ""; // eventuale sotto directory di $path_foto
	public $section        = ""; // segment of the page (for multi hub per page)
	
    /**
     * Nome base del id del div drop-area. 
     * A questo verrà aggiunto il nome segmento (o nulla se segmento è vuoto).
     * Per cambiare comportamento default sovrascrivere il suo valore con metodo setHubDivId('nome_id_completo')
     */
	private $upload_div    = "upload-hub"; 
    
    /**
     * Nome base del id del div thumbnails holder. 
     * A questo verrà aggiunto il nome segmento (o nulla se segmento è vuoto).
     * Per cambiare comportamento default sovrascrivere il suo valore con metodo setThumbsDivId('nome_id_completo')
     */    
	private $thumbnail_div = "media-thumbs"; 
    
    /**
     * Path allo script e foglio di stile del plugin jQuery che da funzionalità al upload hub
     */
	private $core_js_file 	= "plugins/dropzone/dropzone.js";
	private $core_css_file 	= "plugins/dropzone/dropzone.css"; 
    
    /**
     * Path allo script e foglio di stile del lightbox
     */
	private $lightbox_js_file 	= "plugins/fancybox/source/jquery.fancybox.js";
	private $lightbox_css_file 	= "plugins/fancybox/source/jquery.fancybox.css"; 
    
    
    // Holder for thumbnail html div
	private $thumbnails 	= ""; 
    
    
	/********* THUMBNAIL VARS *********/
	public $thumbnailWidth 	  = 100; // larghezza delle thumbnails in px
	public $thumbnailHeight   = 100; // altezza delle thumbnails in px
	public $thumbnailQuality  = 100; // jpeg quality (da 0 a 100), usato in generatore immagine img.php parametro 'q'
	public $thumbnailCutimage = 0; // Paramtro se tagliare immagine (1) o se mostrare sempre tutto (0), usato in generatore immagine img.php parametro 'c'
    
    
    
	/********* JS VARS - DEFAULT VALUES *********/
	public $url 		    = "calls/media_upload.php"; // script php che prende in consegna i file uploadati, li rinomina sposta e registra in DB
	public $acceptedFiles 	= "image/*"; // maschera per i tipi di file accettati 
	public $maxFiles		= 'null'; // numero massimo di file caricabile per questo segmento. null è illimitato
	public $maxtext		    = ''; // Testo quando raggiungo il numero massimo di foto. Default prende valore da translations, ma può essere sovrascritto tramite args
	public $hubtext		    = ''; // Testo all'internod del div Dropzone. Default prende valore da translations, ma può essere sovrascritto tramite args
	public $delete_media_confirm = ''; // Testo all'internod del div Dropzone. Default prende valore da translations, ma può essere sovrascritto tramite args
    public $preview_template = "<div class='dz-preview dz-file-preview'><div class='dz-details'><div class='dz-filename'><span data-dz-name></span></div><div class='dz-size' data-dz-size></div><img data-dz-thumbnail /></div><div class='dz-progress'><span class='dz-upload' data-dz-uploadprogress></span></div><div class='dz-success-mark'><span><a href='' class='fancybox previewLink'><i class='fa fa-eye'></i></a></span></div><div class='dz-error-mark'><span><i class='fa fa-trash'></i></span></div><div class='dz-error-message'><span data-dz-errormessage></span></div></div>	";
	
    // calculated
	private $thumbnailEyeHeight 	= 22; 
	private $thumbnailLineHeight 	= 135; // valore di fallback che viene sostituito da calcolo in __construct
    
    public $dbg = "";
    
    private $max_upload_size = 0;
    public  $max_upload_size_ecceed = "Il file supera la dimensione massima consentita di {{maxFilesize}}MB.";
    
	/*** GLOBAL VARS HOLDERS ***/
	private $db, $page, $record, $pagehash, $translate;
	
	function __construct($args, $options = array()){
        
        ini_set('upload_max_filesize', '32M');
        
        // DEFAULT STARTUP OPTIONS
        $_generate_thumbnails = true;
        $_load_assets         = true;
        $_cleanup             = true;
        
        // Overhaul startup options
        if(isset($options['generate_thumbnails']))  $_generate_thumbnails = $options['generate_thumbnails'];
        if(isset($options['load_assets']))          $_load_assets = $options['load_assets'];
        if(isset($options['cleanup']))              $_cleanup = $options['cleanup'];
        		
		// get global vars
		global $db, $pid, $_record, $_pagehash, $_t, $js_assets, $css_assets;
		
		// save global vars locally
		$this->db 			= $db;
		$this->page 		= $pid;
		$this->record 		= $_record;
		$this->pagehash 	= $_pagehash;
		$this->translate 	= $_t;
        
        $this->max_upload_size = (float) ini_get("upload_max_filesize"); // in MB;
        
        // get traduzioni messaggi default, potrò sovrascriverli tramite $args
		$this->maxtext = $_t->get('max-media-file-message');
		$this->hubtext = $_t->get('upload_media');
		$this->delete_media_confirm = $_t->get('delete_media_confirm');
        
        // Mi assicuro che $args sia un'array
        if(empty($args) or !is_array($args)) $args = array();
		
        // overwirte args
        if(!empty($args)){
            
            $args = $db->make_data_safe($args);
                            
            foreach($args as $param => $value){
                
                    $this->$param = $value;                    
                

            }
            
        } // end if !empty args
        
        // aggiorno media path (la path filesys)
        $this->mediapath = FILEROOT.$this->path_photo;
        
        // Rendo unico id div drop-area e thumbnail div tramite segmento (se definto) - per cambaire compeltamente vedi commento ad inizio file
        $this->upload_div    .= (empty($this->section)) ? "" : "-".$this->camelCase($this->section);
        $this->thumbnail_div .= (empty($this->section)) ? "" : "-".$this->camelCase($this->section);
		
		// calculate lineheight of trash icon
		$this->thumbnailLineHeight = $this->thumbnailHeight-$this->thumbnailEyeHeight;		
		
        if($_generate_thumbnails) $this->generate_thumbnails();
        
        if($_load_assets){
            // add main js and css files of Dropzone plugin to queue if not already queued
            if(!in_array($this->core_js_file,  $js_assets))  $js_assets[]   = $this->core_js_file;
            if(!in_array($this->core_css_file, $css_assets)) $css_assets[]  = $this->core_css_file;

            // add lightbox dependencies to queue if not already queued
            if(!in_array($this->lightbox_js_file,  $js_assets))  $js_assets[]   = $this->lightbox_js_file;
            if(!in_array($this->lightbox_css_file, $css_assets)) $css_assets[]  = $this->lightbox_css_file;
        }
        
        if($_cleanup){
            // Rimuovo media file rimasti appesi, ovvero nuovo record => aggiungo foto, ma poi non salvo
            $this->cleanup();            
        }
		
	}
    
    public function generate_thumbnails(){
        global $db;
        if(!empty($this->record) and !empty($this->page)){
            $where = "WHERE page = '".$this->page."' AND record = '".$this->record."' AND section = '".$this->section."'";
            $where .= " ORDER by `order`";
			$thumbs = $db->select_all(DBTABLE_MEDIA, $where);
			
            if($thumbs){
                // ho delle miniature, imposto html
				$this->setThumbs($thumbs);
			}
		}
        
    }
    
	
	/************** GET VALUES **************/
	
	/**
     * Returns link to core dropzone js file
     * Used has help function outside of class, usually non necessary but nice to have
     */ 
	public function getCoreJs(){
		return "<script src=\"".$this->core_js_file."\"></script>\n";
	}
    
    public function getJsFunctions(){
        
        global $canedit;

        // Exit if user doesn't have permissions to edit record
		if(!$canedit) return;
        
    }

	// returns js functions for setup to be located at the end of cpanel.
	public function setup(){
		
        global $canedit;
		
        // fare in modo che venga caricato una volta solo...
        $output = "<!-- INIZIO DROPZONE SETUP $this->section -------------------------------------->\n";
        $output .= "
			$(document).ready(function() {
				$('.fancybox').fancybox();
			});
		";
        
        // Exit if user doesn't have permissions to edit record
		if(!$canedit) return;
        $output .= "
            $(document).ready(function() {

                MyDropzone".$this->camelCase($this->section, true)." = new Dropzone(
                    \"div#".$this->upload_div.".enabled\",
                    {

                        url: \"".$this->url."\", 
                        previewsContainer: \"#".$this->thumbnail_div."\",
                        acceptedFiles: \"".$this->acceptedFiles."\",
                        thumbnailWidth: ".$this->thumbnailWidth.",
                        thumbnailHeight: ".$this->thumbnailHeight.",
                        maxFiles: ".$this->maxFiles.", 
                        maxFilesize: ".$this->max_upload_size.", 
                        uploadMultiple: false,

                        previewTemplate: \"".$this->preview_template."\",

                        init: function() { 
                            this.on(\"error\", function(file, responseText) { 
                                modal(responseText, \"File troppo grande\", \"danger\");
                                this.removeFile(file);
                            });
                            this.on(\"processing\", function(file, responseText) { 
                                showDropzoneLoader".$this->camelCase($this->section, true)."(true);
                            });
                            this.on(\"complete\", function(file, responseText) { 
                                setTimeout(function(){ switchDropzone".$this->camelCase($this->section, true)."(); showDropzoneLoader".$this->camelCase($this->section, true)."(false); }, 200);

                            });
                            this.on(\"success\", function(file, responseText) { 
                                var response = JSON.parse(responseText);
                                if(response.result){
                                    var hidden = \"<input type='hidden' name='media[]' id='media_\"+response.id+\"' value='\"+response.id+\"'>\";
                                    $(\"form\").append(hidden);
                                    var t = file.previewTemplate;
                                    var e = $(t).find(\".dz-error-mark span\");
                                    if(!e.length){
                                        e = $(t).find(\".del-file\");
                                    }
                                    e.data(\"id\", response.id);                                                                        
                                    var h = file.previewElement;
                                    $(h).find('a.previewLink').prop('href', '".$this->path_photo."'+response.filename);

                                }else{
                                     modal(response.msg, response.error, response.errorlevel);
                                }
                            }); 
                        },
                        dictFileTooBig: \"".$this->max_upload_size_ecceed."\",
                        params: { \"page\": \"".$this->page."\", \"record\" : \"".$this->record."\", \"section\" : \"".$this->section."\",  \"path\" : \"".$this->path_photo."\" }

                    }
                );
                
                switchDropzone".$this->camelCase($this->section, true)."();

                // remove thumbnail, thus image
                $(document).on(\"click\", \"#".$this->thumbnail_div." .dz-success .dz-error-mark span\", function(){
                    var t = $(this);
                    var p = t.closest(\"div.dz-preview\");
                    if(confirm(\"".$this->delete_media_confirm."\")){

                        $.post(  
                         \"calls/media_delete.php\",  
                         {id: t.data(\"id\")},  
                         function(response){

                             if (response.result){													
                                p.fadeOut(\"fast\", function(){ $(this).remove();  switchDropzone".$this->camelCase($this->section, true)."(); });								
                             }else{
                                 modal(response.msg, response.error, response.errorlevel);
                             }
                         },  
                         \"json\"  
                        );  	
                    }
                });
                
                $(document).on(\"click\", \"#".$this->thumbnail_div." .btn-group .del-file\", function(){
                    var t = $(this);
                    var p = t.closest(\".btn-group\");
                    if(confirm(\"".$this->delete_media_confirm."\")){

                        $.post(  
                         \"calls/media_delete.php\",  
                         {id: t.data(\"id\")},  
                         function(response){

                             if (response.result){													
                                p.fadeOut(\"fast\", function(){ $(this).remove();  switchDropzone".$this->camelCase($this->section, true)."(); });								
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
        
        
        $output .= "
            // see if the dropzone hub must be disabled or reenabled
            function switchDropzone".$this->camelCase($this->section, true)."(){
                var thumbs = $('#".$this->thumbnail_div."').find('.dz-image-preview').length;
                var maxFiles =  MyDropzone".$this->camelCase($this->section, true).".options.maxFiles;
                if(maxFiles === null) return true; // don' MyDropzoneSlide.options.maxFilest have restrictions so exit
                if(thumbs >= maxFiles){
                    MyDropzone".$this->camelCase($this->section, true).".disable();					
                    $(\"#".$this->upload_div." span\").html(\"".$this->maxtext."\");
                    $(\"#".$this->upload_div."\").addClass('disabled')
                    $(\"#".$this->upload_div."\").removeClass('enabled')
                }else{
                    if( $(\"#".$this->upload_div."\").hasClass('disabled') ){
                        MyDropzone".$this->camelCase($this->section, true).".enable();
                        $(\"#".$this->upload_div." span\").html(\"".$this->hubtext."\");
                        $(\"#".$this->upload_div."\").removeClass('disabled')
                        $(\"#".$this->upload_div."\").addClass('enabled')
                    } // end if hasClass

                } // end else

            } // end function

            function showDropzoneLoader".$this->camelCase($this->section, true)."(switchOn){
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
        $output .= "<!-- FINE DROPZONE SETUP $this->section -------------------------------------->\n\n";
		
		return $output;
	}
	
	// returns styling
	public function getCss(){
		
        $font_size = (int) round($this->thumbnailHeight / 3);
		// style sheet
		$out = "";
		
		$out .= "<style type=\"text/css\">\n\n";
		
		if($this->thumbnailHeight){
		$out .= "	#".$this->thumbnail_div." .dz-preview{
						height: ".$this->thumbnailHeight."px;
					}
				";
		}
					
		$out .= "	#".$this->thumbnail_div." .dz-success .dz-error-mark{
						font-size: ".$font_size."px;
						line-height: ".$this->thumbnailLineHeight."px;
					}

					#".$this->thumbnail_div." .dz-success .dz-success-mark{
						line-height: ".$this->thumbnailEyeHeight."px;
					}";

		if($this->thumbnailHeight or $this->thumbnailWidth){
			$out .= "	#".$this->$thumbnail_div." .placeholder-thumb{\n";
			$out .= (empty($this->thumbnailHeight)) ? "" : "		height: ".$this->thumbnailHeight."px\n";
			$out .= (empty($this->thumbnailWidth))  ? "" : "		width: ".$this->thumbnailWidth."px\n";
			$out .= "	}\n";
		}
							
		$out .= "</style>\n";
		return $out;
	}
	
	/**
     * Returns html with the upload hub div (drop-area)
     * div id and hub text can be set through args passed to __construct
     *
     * @param extraClass (string)  Extra class to add to upload drop-area div
     */
	public function getHubHtml($extraClass = false){
		global $canedit;
        
        $class = ($canedit) ? "enabled" : "disabled";
        $class .= " upload-hub";
        
        if($extraClass){
            $extraClass = (string) trim($extraClass);
            $class .= " ".$extraClass;
        }
        		
        return "<div class='".$class."' id=\"".$this->upload_div."\"><span>".$this->hubtext."</span></div>\n";
	}
	
	/**
     * Returns html with the thumbnail div
     * thumbnail_div id can be set through args passed to __construct
     *
     * @param extraClass (string)  Extra class to add to upload drop-area div
     */
	public function getThumbsHtml($extraClass = false){
		
        global $canedit;
        
        $class = ($canedit) ? "enabled" : "disabled";
        $class .= " media-thumbs";
        
        if($extraClass){
            $extraClass = (string) trim($extraClass);
            $class .= " ".$extraClass;
        }
        		
        return "<div class='".$class."' id=\"".$this->thumbnail_div."\">".$this->thumbnails."</div>\n";
	}
	
	// LEGACY returns html with the upload hub div and the thumbnail holder
	public function getHtml(){
		global $canedit;
		$class = ($canedit) ? "enabled" : "disabled";
        
        $out  = "<div class='row'>\n";
        $out .= "  <div class='col-sm-6'>\n";
		$out .= "     <div class='".$class."' id=\"".$this->upload_div."\"><span>".$this->hubtext."</span></div>\n";
        $out .= "  </div>\n";
        $out .= "  <div class='col-sm-6'>\n";
		$out .= "     <div class='".$class."' id=\"".$this->thumbnail_div."\">".$this->thumbnails."</div>\n";
        $out .= "  </div>\n";
        $out .= "</div>\n";
		
        return $out;
	}
	
    /**************** MAINTAIN FUNCTIONS AND EXTRA ****************/
	// Delete all the records in the 'media' tab that have record set to 0, page id of current page and have been uploaded by the current user (identified by unique id of log_access table)
	public function cleanup(){
        
        $uploadedby = (isset($_SESSION['access_log_id'])) ? (int) $_SESSION['access_log_id'] : '0';
        
        $where = "WHERE page='".$this->page."' AND record = '0' AND section = '".$this->section."' AND uploadedby = '".$uploadedby."'";
        
        // get list of media record in DB
        $list = $this->db->col_value("file", DBTABLE_MEDIA, $where);
        
        if( empty($list) ) return;
        
        // delete files from upload dir
        foreach($list as $filename) unlink( $this->mediapath.$filename );					
        
        // delete record from media table in DB
        $this->db->delete(DBTABLE_MEDIA, $where);
        
		return true;
	}
	
	// delete mediafile that don't have an corrisponding entry in the media table - could take a while
	public function deleteOrfans(){
        
		// get all filenames from db
		$filesdb = $this->db->col_value("file", DBTABLE_MEDIA);
        
        // set counter of unlinked files
		$c=0; 
        
        // iterate through all the files in the upload dir defined by args (or default)
		$iterator = new DirectoryIterator($this->mediapath);
		foreach ($iterator as $fileinfo) {
			
            // if item is a file and its name is not in the array extracted from media table in DB unlink it
			if ($fileinfo->isFile() and !in_array($fileinfo->getFilename(), $filesdb)) {
				$c++; // count deleted files
				unlink( $this->mediapath.$fileinfo->getFilename() );
			}
		}
		
		return $c;
		
	}
		
		
    /**
     * Return the current media path / upload dir
     */
	public function getMediaPath(){
		return $this->mediapath;
	}
	
	
	/************** SET VALUES **************/
    
    /**
     * Forzo id custom a div drop-area
     *
     * @param id (string)  Nome id custom
     * @return true|false;
     */
    public function setHubDivId($id){
        
        if(empty($id) or !is_string($id)) return false;
        $this->upload_div = (string) trim($id);
        
        return true;
    }
	
    /**
     * Forzo id custom a div thumbnails
     *
     * @param id (string)  Nome id custom
     * @return true|false;
     */
    public function setThumbsDivId($id){
        
        if(empty($id) or !is_string($id)) return false;
        $this->thumbnail_div = (string) trim($id);
        
        return true;
    }
	
	/** 
     * Use html of dropzone to show already assignd thumbnails 
     *
     * @param $thumbs (array)  array risultato di $db->select_all(DBTABLE_MEDIA);
     */
	public function setThumbs($thumbs, $return = false){
		
        if(!is_array($thumbs)) return false;
        
        // loop dei records trovati in media relativo a questo hub (pagina, sezione e record)
        foreach($thumbs as $thumb){
			
            // recupero flag $canedit che indica se l'utente può modificare il record
            global $canedit;
            
            // recupero tipo di file, se non è immagine metto pulsante composto
            if( substr($thumb['filetype'], 0, 6) == 'image/'){
                $this->setSingleThumbnail($thumb);
            }else if($thumb['filetype'] == "application/pdf"){
                $this->setThumbButton($thumb);
            }else{
                continue;
            }
			
		}	// end foreach thumbs
        
        if($return) return $this->thumbnails;
	}

    /**
     * In base ad un record media immagine crea l'html per l'anteprima
     *
     * @param $thumb (array)  Singola riga di media
     * @return nulla, popola $this->thumbnails
     */
	public function setSingleThumbnail($thumb, $return = false){
        
        // recupero flag $canedit che indica se l'utente può modificare il record
        global $canedit;
        
        if(empty($thumb)) return false;
                
        // se sono state definite delle dimensioni per le minaiture imposto gli argomenti inline
        $tag_width = (empty($this->thumbnailWidth)) ? "" : "width=\"".$this->thumbnailWidth."\"";
        $tag_height = (empty($this->thumbnailHeight)) ? "" : "height=\"".$this->thumbnailHeight."\"";

        /**
         * Uso script php img.php per generare miniatura
         * Definisco i parametri per la generazione
         */ 
        $img_qry =  "required/img.php?q=".$this->thumbnailQuality."&file=../".$this->path_photo.$thumb['file']."&c=".$this->thumbnailCutimage;
        if(!empty($this->thumbnailWidth)) $img_qry .= "&w=".$this->thumbnailWidth;
        if(!empty($this->thumbnailHeight)) $img_qry .= "&h=".$this->thumbnailHeight;
        $html = "
            <div class=\"dz-preview dz-processing dz-success dz-image-preview\">\n
                <div class=\"dz-details\">\n
                    <div class=\"dz-filename\"><span data-dz-name=\"\">".$thumb['name']."</span></div>\n						
                    <div data-dz-size=\"\" class=\"dz-size\"><strong>".$thumb['size']."</strong> KiB</div>						
                    <img ".$tag_width." ".$tag_height." data-dz-thumbnail=\"\" alt=\"".$thumb['name']."\" src=\"".$img_qry."\">\n
                </div>\n
                <div class=\"dz-success-mark\"><span><a href=\"".$this->path_photo.$thumb['file']."\" class=\"fancybox\"><i class=\"fa fa-eye\"></i></a></span></div>\n";
        $html .= ($canedit) ? "
                <div class=\"dz-error-mark\"><span data-id=\"".$thumb['id']."\"><i class=\"fa fa-trash\"></i></span></div>\n" : "";
        $html .= "
            </div>\n\n";
                
        if($return){
            return $html;
        }else{
            $this->thumbnails .= $html;
        }
        
    }
    
	public function setThumbButton($thumb, $return = false){
        
        // recupero flag $canedit che indica se l'utente può modificare il record
        global $canedit;
        
        if(empty($thumb)) return false;
        
        $link = SITEROOT.$thumb['path'].$thumb['file'];
        $nome = $thumb['name'].".pdf";
        if(strlen($nome) > 30) $nome = substr($nome, 0, 35)."...";
        $html = "<div class='btn-group dz-image-preview'><a target='_blank' href='".$link."' class='btn btn-default previewLink' data-dz-name><span><i class='fa fa-file-pdf-o mr-2'></i>".$nome."</span></a><a data-id=\"".$thumb['id']."\" class='btn btn-danger del-file'><i class='fa fa-trash'></i></a></div>";
        
        if($return){
            return $html;
        }else{
            $this->thumbnails .= $html;
        }
                
    }
    

    /**
     * Aggiungo sottocartella a media path 
     * Accodo $subpath a $this->path_photo e a $this->mediapath
     */
    public function addMediaSubPath($subpath = ""){
        
        if(empty($subpath) or !is_string($subpath)) return false; 
        
        // mi assicuro che non inizi con spazi e '/' e che finisca con '/'
        $subpath = trim($subpath, ' /').'/';        
                
        $this->path_photo .= $subpath;
        $this->mediapath  .= $subpath;
        
        return true;
        
    }
    /**
     * Setto path completo in cui collocare i media caricati
     * Sostituisco la variabile path_photo (ergo 'photo/') con il valore di path
     * FILEROOT rimane sempre invariato
     */
    public function setMediaPath($path = ""){
        
        if(empty($path) or !is_string($path)) return false; 
        
        // mi assicuro che non inizi con spazi e '/' e che finisca con '/'
        $path = trim($path, ' /').'/';
        
        $this->path_photo = $path;
        $this->mediapath  = FILEROOT.$path;
        
        return true;
        
    }
    
    /**
     * Function to attach orphan records in media table to a record
     * As a defaults the global values (except section, which has no global value)
     *
     * @param section (string)  the section identifier. Default is empty  
     * @param _record (int)     the record media should be attached to. Default value is false which falls back to global value of $_record  
     * @param pid (int)         the page id. Default value is false which falls back to global value of $pid  
     * @param uploadedby (int)  the id of the access log table which identifies the user (session) that has uploaded the file. Default value is false which falls back to session value of access_log_id (or 0 if SA)  
     */
    public function attachMedia($section = "", $_record = false, $pid = false, $uploadedby = false){
        
        global $db;
        
        if(!$_record) global $_record;
        if(!$pid) global $pid;
        if(!$uploadedby) $uploadedby = (isset($_SESSION['access_log_id'])) ? (int) $_SESSION['access_log_id'] : '0'; // 2nd clause for SA
        
        // sanify
        $_record = (int) $_record;
        $pid = (int) $pid;
        $uploadedby = (int) $uploadedby;
        $section = $db->make_data_safe($section);
        
        
        // fundamental values cannot be empty
        if(empty($_record) or empty($pid) or empty($section) ) return false;
        
        // construct query
        $where = "WHERE page='".$pid."' AND record = '0' AND section = '".$section."' AND uploadedby = '".$uploadedby."'";			
        
        //update records in media tebale
        $this->db->update(DBTABLE_MEDIA, array("record" => $_record), $where);
        
		return true;
        
    }
    
    /**
     * Function that checks if a media file exists in filesys
     * Expects record id of the media file and returns boolean if found/not found or an int if either no media record i passed or if no record is found in medai table
     *
     * @param media_id (int)       Record id of the media file
     * @return         (int|bool)  Returns true if file exists, false if not, 0 (int) if media_id is empty (or NaN), -1 (int) if record doesn't exist in media table
     */
    public function checkFileExists($media_id){
        
        global $db;
        
        $media_id = (int) $media_id;
        if(empty($media_id)) return 0;
        
        $media = $db->get1row(DBTABLE_MEDIA, "WHERE id = '".$media_id."'");
        
        if(!$media) return -1;
        
        $path = FILEROOT.$media['path'].$media['file'];
        
        if(file_exists($path)){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * trasforma una stringa con spazi, trattini o punti in camelCase
     */
    public function camelCase($string, $firstUpper = false){
        
        $pattern = '/[\s\-_\.]/';

        $string = strtolower(trim($string));
        
        $e = preg_split($pattern, $string);
        if($e){
            $string = "";
            $start = ($firstUpper) ? -1 : 0;
            foreach($e as $i => $word){
                if($i > $start) $word = ucfirst($word);
                $string .= $word;
            }
        }

        return $string;        
        
    }

}
?>