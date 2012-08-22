<?php
/**
 * Configuration
 */

header("Content-Type: text/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$uploadDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'uploads';
set_time_limit(300); // 5 min

$errors = array(
	101 => 'Failed to open input stream.',
	102 => 'Failed to open output stream.',
	103 => 'Failed to move uploaded file.'
);
$errorTpl = '{"jsonrpc" : "2.0", "error" : {"code": %s, "message": "%s"}, "id" : "id"}';
$successTpl = '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';

/**
 * Post data
 */

$chunk = isset($_POST["chunk"]) ? $_POST["chunk"] : 0;
$chunks = isset($_POST["chunks"]) ? $_POST["chunks"] : 0;
$fileName = isset($_POST["name"]) ? $_POST["name"] : '';

/**
 * File saving
 */

if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0777, true);
}

$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
$filePath = $uploadDir.DIRECTORY_SEPARATOR.$fileName;

if ($chunks <= 1 && file_exists($filePath)) {
	$fileName = getUniqueFilename($uploadDir, $fileName);
}

$inputPath = $_FILES['file']['tmp_name'];

if ( !is_readable($inputPath) ) {
	die(sprintf($errorTpl, 101, $errors[101]));
}
if ( !is_readable(dirname($filePath)) ) {
	die(sprintf($errorTpl, 102, $errors[102]));
}
if ( !is_uploaded_file($inputPath) ) { // Security check
	die(sprintf($errorTpl, 103, $errors[103]));
}

copy($inputPath, $filePath);
unlink($inputPath);

// Return response
die($successTpl);

/**
 * Functions
 */
function getUniqueFilename($uploadDir, $fileName)
{
	$dotPosition = strrpos($fileName, '.');
	$name = substr($fileName, 0, $dotPosition);
	$ext = substr($fileName, $dotPosition);

	$i = 1;
	do
	{
		$fileName = $name.'_'.$i.$ext;
		$fileExists = file_exists($uploadDir.DIRECTORY_SEPARATOR.$fileName);
		$i++;
	} while ( $fileExists );
	
	return $fileName;
}
?>