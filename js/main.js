function analyseScript() {
	startLoadLayer();
	HTML5URL = "";
	if (document.getElementById('datei').value) {HTML5URL = document.getElementById('datei').files[0].name.replace('.zip','');}
	$.ajax({
		type: 'POST',
		data: "script="+encodeURIComponent(document.getElementById('scriptArea').value)+"&HTML5URL="+HTML5URL,
		headers: {'X-Requested-With': 'XMLHttpRequest'},
		url: 'data/toPhantom.php',
		success: function(data){
			document.getElementById('upload_preview').src = "data/"+((HTML5URL)?"html5temp/"+HTML5URL+"/index.html?clicktag=http%3A%2F%2Fwww.bild.de&target=_blank":"checkScript.html");
			document.getElementById('analyse').innerHTML = data;
		},
		complete: function() {
			document.getElementById('secondContainer').style.display = "block";
			document.getElementById('datei').value = "";
			stopLoadLayer();
		}
	})
}

function uploadZIP() {
	$.ajax({
		type: 'POST',
		data: "script="+encodeURIComponent(document.getElementById('scriptArea').value),
		url: 'data/toPhantom.php',
		success: function(data){
			document.getElementById('upload_preview').src = "data/checkScript.html";
			document.getElementById('analyse').innerHTML = data;
		},
		complete: function() {
			document.getElementById('secondContainer').style.display = "block";
		}
	})
}