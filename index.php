<?php

$hubspotApiKey = getenv('HUBSPOT_API_KEY');
$listId = getenv('HUBSPOT_LIST_ID');

$propertiesJson = getenv('HUBSPOT_CONTACT_PROPERTIES');
$propertiesArray = json_decode($propertiesJson, true);
$properties = $propertiesArray['properties'];





//get the name of the list
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.hubapi.com/contacts/v1/lists/$listId?hapikey=$hubspotApiKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response, true);

$listName = $response['name'];





//get all the contacts and the requested properties
$contactsArray = array();

$vidOffset = null;

do {
	$params = array(
		'hapikey' => $hubspotApiKey,
		'count' => '100'
	);
	
	//check if you need to include an offset
	if($vidOffset != null){
		$params['vidOffset'] = $vidOffset;
	}
	
	$query = http_build_query($params) . "&property=" . implode("&property=", $properties);
	

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.hubapi.com/contacts/v1/lists/$listId/contacts/all?" . $query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($ch), true);
	curl_close($ch);
	

	foreach($response['contacts'] as $contact){

		$tempArray = array();
		

		foreach($properties as $property){
			if(isset($contact['properties'][$property]['value'])){
				$tempArray[$property] = $contact['properties'][$property]['value'];
			}
			else{
				$tempArray[$property] = "";
			}
		}
		

		array_push($contactsArray, $tempArray);
	}
	

	$vidOffset = $response['vid-offset'];

} while ($response['has-more'] == true);

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $listName; ?></title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

</head>

<body>
	
<div class="container">
  
  <div class="page-header">
  	<h1><?php echo $listName; ?></h1>
  </div><!-- page-header -->
  
	<table class="table">
		<thead>
			<tr>
				<?php
				foreach($properties as $property){
					echo "<th>$property</th>";
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($contactsArray as $contact){
				echo "<tr>";
				foreach($contact as $property){
					echo "<td>$property</td>";
				}
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div><!-- container -->

</body>
</html>
