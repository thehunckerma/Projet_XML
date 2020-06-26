<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');

$queryArr = explode(" ", $_GET["query"]); // Split GET query by space
$xmlDirPath = dirname(__FILE__) . "\\xml\\"; // Path for xml folder
$dirIter = new DirectoryIterator($xmlDirPath); // Initialize directory iterator 
$files = []; // Stores names of XML files that matches the query.

function contains($str, array $arr) // Returns the number of $arr strings contained in $str
{
  $i = 0;
  foreach ($arr as $a) {
    if (stripos($str, $a) !== false) $i++;
  }
  return $i;
}

function transformArr(array $arr)
{
  $n = sizeof($arr);
  for ($i = 1; $i < $n; $i++) { // Sort the array by number of matched words
    for ($j = $n - 1; $j >= $i; $j--) {
      if (explode("|", $arr[$j - 1])[1] > explode("|", $arr[$j])[1]) {
        $tmp = $arr[$j - 1];
        $arr[$j - 1] = $arr[$j];
        $arr[$j] = $tmp;
      }
    }
  }
  for ($i = 1; $i < $n; $i++) { // Remove the number of matched words in the sorted array
    $arr[$i] = explode("|", $arr[$j - 1])[0];
  }
  return $arr;
}

foreach ($dirIter as $fileinfo) { // Iterate over the XML files in the xml forlder
  if (!$fileinfo->isDot() && $fileinfo->getExtension() === "xml") {
    $file = $xmlDirPath . $fileinfo->getFilename(); // File name
    $xmlDoc = new DOMDocument(); // Initialize a DOMDocument object
    $xmlDoc->load($file); // Load the current XML file
    $elementsByTagName = $xmlDoc->getElementsByTagName('title'); // Get the nodes with tag name "title"
    $value = $elementsByTagName->item(0)->nodeValue; // By definition there is a single "title" element in each xml file
    $j = contains($value, $queryArr); // Match the words in the query with the content of "title" element
    if ($j > 0) { // If some words match continue
      $files[] = $file . "|" . $j; // Store the file and number of matches seprated by a pipe
    }
  }
}

print(json_encode(["files" => transformArr($files)]));
