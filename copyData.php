<?php
require_once __DIR__ . '/config.php';
$session = session_start();

# Example images for debugging
#$newImageData = "R0lGODlhDwAPAKECAAAAzMzM/////wAAACwAAAAADwAPAAACIISPeQHsrZ5ModrLlN48CXF8m2iQ3YmmKqVlRtW4MLwWACH+H09wdGltaXplZCBieSBVbGVhZCBTbWFydFNhdmVyIQAAOw==";
#$newImageData = "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";

# Passed by previous page:
$datasetID = $_SESSION['datasetID'];
$oldImagePath = $_SESSION['oldImagePath'];
$newImageData = $_SESSION['newImageData'];

# Retrieve dataset of given ID
$datasetRequest = MLEARN4WEB_API_URL . "/getdata/" . $datasetID;
$dataset = trim(file_get_contents($datasetRequest));
$dataset = json_decode($dataset, true);

#var_dump($dataset);

# Include manipulated image by creating a new element within existing dataset
foreach ($dataset['data'] as $screenKey => $screen) {
    foreach ($screen as $elementKey => $element) {
        if ($element['type'] == 'image') {

            # If this is the selected image, carry out the copying
            if($element['value'] == $oldImagePath) {
                # create a copy of the element containing the original image
                $copy = array();
                $copy['elementId'] = $element['elementId'];
                $copy['type'] = $element['type'];
                $copy['value'] = $newImageData;

                # Increase version count
                $copy['elementId'] = increaseVersion($copy['elementId']);

                # Add new element to original element's screen
                $dataset['data'][$screenKey][] = $copy;
            }

            # Replace path of existing images with their base64 encoded image data to avoid data corruption.
            # (This is necessary because the API expects base64 encoded image data as image PUT parameter only.)
            $image = file_get_contents(MLEARN4WEB . $element['value']);
            $dataset['data'][$screenKey][$elementKey]['value'] = base64_encode($image);
        }
    }
}

# attributes not needed for PUT request
unset($dataset['_id']);
unset($dataset['scenarioId']);
unset($dataset['__v']);
unset($dataset['timestamp']);

#echo "dataset with new image element:";
#var_dump($dataset);

# Process updatedata PUT request
$dataString = json_encode($dataset);
$requestURL = MLEARN4WEB_API_URL . '/updatedata/' . $datasetID;
$headers= array('Accept: application/json','Content-Type: application/json');

$ch = curl_init($requestURL);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS,$dataString);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);
$_SESSION['saved'] = true;
curl_close($ch); # Seems like good practice

header('Location: ' . BASE_URL . '/editImage.php');

function increaseVersion($origVersionString) {

    $preString = substr($origVersionString, 0, 3) . '_v';
    if (strlen($origVersionString) == 3) {
        $newVersionString = $preString . '1';
    } else {
        $origVersion = intval(substr($origVersionString, 5));
        $newVersionString = $preString . ($origVersion + 1);
    }
    return $newVersionString;
}