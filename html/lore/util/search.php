<?php
if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
	require $_SERVER["DOCUMENT_ROOT"] . "/../scripts/php/languages/dictionary.php";
	$dictionary = new dictionary($_SERVER["DOCUMENT_ROOT"] . "/../conf/languages/mysql.json");
	
	if (!isset($_GET["type"]) || !isset($_GET["key"])) {
		http_response_code(400);
		exit();
	}
	$dictionary->search($_GET["type"],$_GET["key"],$_GET["id"],$_GET["fr"],"\t\t\t\t");
}
else {
	http_response_code(303);
	header("Location: /lore/dictionary/?" . http_build_query($_GET));
}
?>
