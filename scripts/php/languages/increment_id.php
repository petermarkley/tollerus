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
require "scripts/php/languages/dictionary.php";
$last_id = rtrim(file_get_contents("private/languages/next_id"));
$last_id_num = dictionary::id_decode($last_id);
$next_id_num = $last_id_num + 1;
$next_id = dictionary::id_encode($next_id_num);
echo $last_id . " -> " . $next_id . "\n";
file_put_contents("private/languages/next_id",$next_id."\n");
