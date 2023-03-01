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
	
	if (!isset($_GET["type"]) || !isset($_GET["key"])) {
		http_response_code(400);
		exit();
	}
	$dictionary->search($_GET["type"],$_GET["key"],(isset($_GET["id"])?$_GET["id"]:null),(isset($_GET["fr"])?$_GET["fr"]:null),"\t\t\t\t");
}
else {
	http_response_code(303);
	header("Location: /lore/dictionary/?" . http_build_query($_GET));
}
?>
