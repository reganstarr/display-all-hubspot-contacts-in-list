<?php

$hubspotApiKey = getenv('HUBSPOT_API_KEY');
$listId = getenv('HUBSPOT_LIST_ID');
$propertiesArray = json_decode(getenv('HUBSPOT_PROPERTIES_JSON'), true);





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
	
	//add the contact properties you want to include to the rest of your url query. HubSpot uses the format &property=contact&property=firstname&property=lastname for passing multiple properties
	$query = http_build_query($params) . "&property=" . implode("&property=", $properties);
	
	//start the api call
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.hubapi.com/contacts/v1/lists/$listId/contacts/all?" . $query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($ch), true);
	curl_close($ch);
	
	//loop through the response and pull out the properties you are wanting
	foreach($response['contacts'] as $contact){
		//create a temporary array for storing the information
		$tempArray = array();
		
		//to avoid getting a "Notice" error, check that each property is actually set before trying to call its value
		foreach($properties as $property){
			if(isset($contact['properties'][$property]['value'])){
				$tempArray[$property] = $contact['properties'][$property]['value'];
			}
			else{
				$tempArray[$property] = "";
			}
		}
		
		//add the temporary array to our main contacts array
		array_push($contactsArray, $tempArray);
	}
	
	//take note of the vid offset for your next api call in order to call the next page of contacts
	$vidOffset = $response['vid-offset'];

	//check if there are still more contacts to get. If there are, go through the loop again
} while ($response['has-more'] == true);

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title></title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

</head>

<body>
	
<div class="container">
	<table class="table">
		<thead>
			<tr>
				<?php
				//loop through the properties for your column headings
				foreach($properties as $property){
					echo "<th>$property</th>";
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			//loop through each contact and create a row in the table
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
