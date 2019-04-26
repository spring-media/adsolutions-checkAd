<!--  Copyright: 2015/2017 by René Baudisch -->
<?php

	if(isset($_POST['send'])){
		if(isset($_FILES['datei'])) {$fileName = $_FILES['datei']['name'];} else {echo '<script type="text/javascript">alert("Es wurde keine Datei ausgewählt. Bitte wählen Sie eine Datei und versuchen Sie es erneut.");</script>';die;}
		if (strpos($fileName, ".zip")) {
			
			function ezip($file, $path){
				$zip = new ZipArchive;
				$res = $zip->open($file);
				$zip->extractTo($path);
				$zip->close();
			}

			function delete_files($target) {
				if(is_dir($target)){
					$files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
					foreach( $files as $file ) {
						delete_files( $file );
					}
					rmdir( $target );
				} elseif(is_file($target)) {
					unlink( $target );
				}
			}
			
			$dir = str_replace('.zip','', $fileName);
			if (file_exists($dir)) {delete_files("data/html5temp/".$dir);}
			mkdir("data/html5temp/".$dir);
			move_uploaded_file($_FILES['datei']['tmp_name'], $_FILES['datei']['name']);
			rename($fileName, "data/html5temp/".$dir."/".$fileName);
			ezip("data/html5temp/".$dir."/".$_FILES['datei']['name'], "data/html5temp/".$dir."/");
			unlink("data/html5temp/".$dir."/".$fileName);
			
			echo '<script type="text/javascript">alert("DIE HTML ZIP wurde hochgeladen und entpackt, zum Prüfen auf \"Größe berechnen\" klicken");</script>';
		} else {
			echo '<script type="text/javascript">alert("Falscher Dateityp - ZIP erwartet");</script>';
		}
	} else {
?>
<link rel="stylesheet" type="text/css" href="css/main.css"></link>
<div id="wrapper">
	<div id='firstContainer' class='Container'>
		<form id='BB_Upload' action='index.php' target="upload_target" method="POST" enctype="multipart/form-data">
			<input type='txt' value='1' name='send' style='display:none' />
			<input type='file' id='datei' name="datei" class='upload' />
			<button class="file" type='submit'>HTML5 ZIP hochladen</button>
		</form>
		<button type="submit" onclick="analyseScript();">Größe berechnen</button>
		<textarea id="scriptArea" name="script"></textarea>
		<div id="analyse"></div>
	</div>
	<div id='secondContainer' style="display:none;" class="Container">
		<h3>Preview iFrame</h3><div>Landingpage = http://www.bild.de / target = _blank</div>
		<iframe id="upload_preview" name="upload_preview" src="about:blank" style="width:1030px;height:900px;border:0px solid #fff;"></iframe>
	</div>	
</div>
<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/main.js"></script>

<?php
    };
?>