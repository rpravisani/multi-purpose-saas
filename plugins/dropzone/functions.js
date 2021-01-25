$(document).ready(function() {
	$("div#upload-hub").dropzone({ 
		url: "calls/dummy-upload.php", 
		previewsContainer: "#media-thumbs",
		acceptedFiles: "image/*",
		thumbnailWidth: 150,
		thumbnailHeight: 150, 
		uploadMultiple: false,
		params: { "token": "value", "record" : "2" }
	});
	
	$(document).on("click", ".dz-success .dz-error-mark span", function(){
		var t = $(this).closest("div.dz-preview");
		if(confirm("Cancello questa immagine?")){
			t.fadeOut("fast", function(){ $(this).remove(); });	
		}
	});
});