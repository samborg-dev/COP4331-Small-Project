<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

function ContactsSearch(){

$userId = requireLogin();
$input = getJsonInput();

// wraps the name with '%' which allows for partial search
$searchTerm = '%' . $input['search'] . '%';

$searchQuery = $pdo->prepare("SELECT ContactID, FirstName, LastName, phone, email from Contacts WHERE UserID = ? AND CONCAT(FirstName,' ',LastName) LIKE ?");
$searchQuery->execute([$userId,$searchTerm]);

//checks if there is a match for the name searched
if($searchQuery->rowCount() == 0)
{
	sendError('No Match');
	
}
else{
//Creates array of associative arrays detailing information
	
	$searchResults = $searchQuery->fetchAll(PDO::FETCH_ASSOC);
	return $searchResults;
	sendError("");
	
}

}
