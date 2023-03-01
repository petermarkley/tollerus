<?php
if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
	require $_SERVER["DOCUMENT_ROOT"] . "/../scripts/php/languages/dictionary.php";
	$dictionary = new dictionary($_SERVER["DOCUMENT_ROOT"] . "/../conf/languages/mysql.json");
	
	if (isset($_GET["id"])) {
		$valid = $dictionary->id_valid($_GET["id"]);
		if ($valid["id"] === null) {
			http_response_code(404);
			exit();
		}
	}
	if (isset($_GET["lang"])) {
		$dictionary->intro_read($_GET["lang"]);
		$dictionary->intro_print("\t\t\t\t");
	}
	if (isset($_GET["id"])) {
		$dictionary->entry_read();
		$dictionary->entry_print($_SERVER["DOCUMENT_ROOT"] . "/../conf/languages/inflections.json","\t\t\t\t");
	}
}
else {
	http_response_code(303);
	header("Location: /lore/dictionary/?" . http_build_query($_GET));
}
?>
