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

date_default_timezone_set("America/New_York");
require $_SERVER["DOCUMENT_ROOT"] . "/../scripts/php/languages/dictionary.php";
$dictionary = new dictionary($_SERVER["DOCUMENT_ROOT"] . "/../conf/languages/mysql.json");

if (isset($_GET["id"])) {
	$valid = $dictionary->id_valid($_GET["id"]);
	if ($valid["id"] === null && $valid["lang"] === null) {
		http_response_code(404);
	} else if ($valid["fr"] !== null || $valid["lang"] !== null) {
		http_response_code(308);
		header("Location: /lore/dictionary/?" . http_build_query(["id"=>$valid["id"],"lang"=>$valid["lang"]]) . ($valid["fr"]!==null?"#".$valid["fr"]:""));
		exit();
	}
} else if (!isset($_GET["lang"])) {
	http_response_code(308);
	header("Location: /lore/dictionary/?lang=myconlang");
	exit();
}

echo "<!DOCTYPE html>\n";

?>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Tollerus Conlang Tracking System</title>
		<meta name="author" content="Peter Markley"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Gentium+Book+Plus:ital@0;1&display=swap&text=<?php echo urlencode("/bdefhijklmnopstuvwxzæðŋɑəɡɪɹɾʃʒʔʟθʊʌ.ˈˌ◌"); ?>" rel="stylesheet">
		<link href="/style/languages.css" rel="stylesheet" type="text/css"/>
		<style>.hidden {display:none;}</style>
	</head>
	<body>
<?php $prefix = "\t\t";

      echo $prefix; ?><h1>Tollerus Conlang Tracking System</h1>
<?php echo $prefix; ?><div id="dictionary">
<?php echo $prefix; ?>	<div id="dictionary-search">
<?php echo $prefix; ?>		<form action="">
<?php echo $prefix; ?>			<fieldset>
<?php echo $prefix; ?>				<div id="dictionary-search-type">
<?php echo $prefix; ?>					<label for="dictionary-search-type-elem" class="hidden">Type of search:</label>
<?php echo $prefix; ?>					<select id="dictionary-search-type-elem" name="type">
<?php echo $prefix; ?>						<option value="roman"<?php if (isset($_GET["type"]) && $_GET["type"] == "roman") echo " selected=\"selected\""; ?>>romanization</option>
<?php echo $prefix; ?>						<option value="def"<?php   if (isset($_GET["type"]) && $_GET["type"] == "def")   echo " selected=\"selected\""; ?>>definition</option>
<?php echo $prefix; ?>					</select>
<?php echo $prefix; ?>					<svg viewBox="0 0 26 26"><use href="/assets/svg/icons-ui.svg#chevron_down" width="26" height="26"/></svg>
<?php echo $prefix; ?>				</div>
<?php echo $prefix; ?>				<label for="dictionary-search-key" class="hidden">Search term(s):</label>
<?php echo $prefix; ?>				<input type="text" id="dictionary-search-key" name="key"<?php if (isset($_GET["key"])) echo " value=\"" . $_GET["key"] . "\""; ?> placeholder="search &hellip;"/>
<?php echo $prefix; ?>				<div id="dictionary-search-submit">
<?php echo $prefix; ?>					<input type="submit" value=""/>
<?php echo $prefix; ?>					<svg viewBox="0 0 26 26"><use href="/assets/svg/icons-ui.svg#magnifier" width="26" height="26"/></svg>
<?php echo $prefix; ?>				</div>
<?php echo $prefix; ?>			</fieldset>
<?php echo $prefix; ?>		</form>
<?php echo $prefix; ?>	</div>
<?php echo $prefix; ?>	<div id="dictionary-search-results">
<?php

if (isset($_GET["type"]) && isset($_GET["key"]) && isset($dictionary)) {
	$dictionary->search($_GET["type"],$_GET["key"],(isset($_GET["id"])?$_GET["id"]:null),(isset($_GET["fr"])?$_GET["fr"]:null),$prefix."\t\t");
}

?>
<?php echo $prefix; ?>	</div>
<?php echo $prefix; ?>	<div id="dictionary-display">
<?php

if (isset($dictionary)) {
	if (isset($_GET["lang"])) {
		$dictionary->intro_read($_GET["lang"]);
		$dictionary->intro_print($prefix."\t\t");
	}
	if (isset($_GET["id"])) {
		$dictionary->entry_read();
		$dictionary->entry_print($_SERVER["DOCUMENT_ROOT"] . "/../conf/languages/inflections.json",$prefix."\t\t");
	}
}

      echo $prefix; ?>	</div>
<?php echo $prefix; ?></div>
<?php echo $prefix; ?><p>This page powered by the <a href="https://github.com/petermarkley/tollerus">Tollerus Conlang System</a>.</p>
<?php echo $prefix; ?><script src="/assets/js/dictionary.js" type="text/javascript"></script>
<?php

?>	</body>
</html>
