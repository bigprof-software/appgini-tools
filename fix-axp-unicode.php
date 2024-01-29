<?php

// if no file uploaded, show upload form
if (!isset($_FILES['axpFile'])) {
	showUploadForm();
}

// handle upload
handleUpload();

function showUploadForm($error = null) {
	?>
	<h1><a href="fix-axp-unicode.php">Fix AXP Unicode Tool</a></h1>

	<?php if ($error) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>

	<form enctype="multipart/form-data" method="post" action="fix-axp-unicode.php">
		<label>AXP file:</label>
		<input type="file" name="axpFile" accept=".axp" />
		<div><small>Max upload size: <?php echo ini_get('upload_max_filesize'); ?></small></div>
		<div>
			<input type="submit" value="Upload" />
			<input type="reset" value="Reset" />
		</div>
			
	</form>

	<style>
		body {
			font-family: sans-serif;
			width: 100%;
			max-width: 800px;
			margin: 0 auto;
			padding-top: 1em;
		}
		h1 > a {
			text-decoration: none;
			color: black;
		}
		.error {
			color: darkred;
			font-weight: bold;
			padding: 1em;
			border: 1px solid darkred;
			margin-bottom: 1em;
			background-color: lightpink;
			text-transform: capitalize;
		}
		label {
			display: block;
			margin-bottom: 0.5em;
			font-weight: bold;
		}
		input[type="file"] {
			display: block;
			border: dotted 1px darkgrey;
			padding: 1em;
			width: calc(100% - 2em);
			cursor: pointer;
		}
		input[type="submit"], input[type="reset"] {
			display: inline-block;
			margin: 1em 1em 0 0;
			padding: 1em 5em;
			cursor: pointer;
		}
		input[type="submit"] {
			text-transform: uppercase;
			font-weight: bold;
		}
	</style>
	<?php
	exit;
}

function uploadError($errNum) {
	switch ($errNum) {
		case UPLOAD_ERR_INI_SIZE:
			return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
		case UPLOAD_ERR_FORM_SIZE:
			return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
		case UPLOAD_ERR_PARTIAL:
			return 'The uploaded file was only partially uploaded';
		case UPLOAD_ERR_NO_FILE:
			return 'No file was uploaded';
		case UPLOAD_ERR_NO_TMP_DIR:
			return 'Missing a temporary folder';
		case UPLOAD_ERR_CANT_WRITE:
			return 'Failed to write file to disk';
		case UPLOAD_ERR_EXTENSION:
			return 'File upload stopped by extension';
		default:
			return 'Unknown upload error';
	}
}

function handleUpload() {
	// check for errors
	if ($_FILES['axpFile']['error'] !== UPLOAD_ERR_OK) {
		showUploadForm('Upload error: ' . uploadError($_FILES['axpFile']['error']));
	}

	// check file extension
	$ext = pathinfo($_FILES['axpFile']['name'], PATHINFO_EXTENSION);
	if ($ext !== 'axp') {
		showUploadForm('Invalid file extension: ' . $ext);
	}

	// read file
	$axp = file_get_contents($_FILES['axpFile']['tmp_name']);

	// fix unicode
	$axp = fixUnicode($axp);

	// save file
	$filename = pathinfo($_FILES['axpFile']['name'], PATHINFO_FILENAME);
	$filename = preg_replace('/[^a-z0-9_\.]/i', '-', $filename);
	//$filename = strtolower($filename);
	$filename = $filename . '.fixed.axp';
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	echo $axp;
	exit;
}

function fixUnicode($axp) {
	// parse XML, replacing entities inside CDATA with unicode
	$axp = preg_replace_callback('/<!\[CDATA\[(.*?)\]\]>/s', function($matches) {
		return '<![CDATA[' . decodeHtmlEntities($matches[1]) . ']]>';
	}, $axp);

	return $axp;
}
	

function decodeHtmlEntities($string) {
	return html_entity_decode($string, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}

