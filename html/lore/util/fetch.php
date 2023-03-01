<?php

//---------------------------------------------------------------------
//                         Tollerus
//                Conlang Dictionary System
//      < https://github.com/petermarkley/tollerus >
// 
// Copyright 2023 by Peter Markley <peter@petermarkley.com>.
// Distributed under the terms of the Lesser GNU General Public License.
// 
// This file is part of Tollerus.
// 
// Tollerus is free software: you can redistribute it and/or modify it
// under the terms of the Lesser GNU General Public License as
// published by the Free Software Foundation, either version 2.1 of the
// License, or (at your option) any later version.
// 
// Tollerus is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// Lesser GNU General Public License for more details.
// 
// You should have received a copy of the Lesser GNU General Public
// License along with Tollerus.  If not, see
// < http://www.gnu.org/licenses/ >.
// 
//----------------------------------------------------------------------

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
