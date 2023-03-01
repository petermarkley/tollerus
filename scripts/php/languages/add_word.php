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

$dom = new DOMDocument();
$source_file = "../../../private/languages/data/".$_GET["lang"].".xml";
$dom->load($source_file);
$prim_script = null;
$xpath = new DOMXpath($dom);
$scripts = $xpath->query("//script");
foreach ($scripts as $script) {
	if ($script->getAttribute("primary") == "yes") {
		$prim_script = $script;
		break;
	}
}

if (isset($_POST["new_word"])) {
	require "dictionary.php";
	$next_id_file = "../../../private/languages/next_id";
	$next_id = dictionary::id_decode(file_get_contents($next_id_file));
	
	$new_word = json_decode($_POST["new_word"]);
	$xpath = new DOMXpath($dom);
	$data = $xpath->query("/dictionary/data")->item(0);
	$entry = $dom->createElement("entry");
	$data->appendChild($entry);
	$entry->before("\t");
	$entry->after("\n\t");
	$entry->setAttribute("id",dictionary::id_encode($next_id++));
	foreach ($new_word->entry->classes as $inp_class) {
		$out_class = $dom->createElement("class");
		$entry->appendChild($out_class);
		$out_class->before("\n\t\t\t");
		$out_class->setAttribute("type",$inp_class->type);
		$out_class->setAttribute("id",dictionary::id_encode($next_id++));
		if (isset($inp_class->morph)) {
			$morph = $dom->createElement("morph");
			$out_class->appendChild($morph);
			$morph->before("\n\t\t\t\t");
			foreach ($inp_class->morph->forms as $inp_form) {
				$out_form = $dom->createElement("form");
				$morph->appendChild($out_form);
				$out_form->before("\n\t\t\t\t\t");
				foreach ($inp_form->attr as $attr => $val) {
					if ($attr == "verb_role" && ($inp_class->type == "verb" || $inp_class->type == "auxiliary verb")) {
						/*
						   FIXME --
						   this is a special case due to the fact that we are
						   hard-coding our inflection dimensions inside the
						   database schema (see `/scripts/sql/schema.sql` and
						   `/scripts/xslt/to_sql.xsl`)
						   --
						*/
						$key = "role";
					} else {
						$key = $attr;
					}
					$out_form->setAttribute($key,$val);
				}
				$out_form->setAttribute("id",dictionary::id_encode($next_id++));
				$out_form->appendChild($dom->createElement("roman",$inp_form->roman));
				$out_form->lastElementChild->before("\n\t\t\t\t\t\t");
				$out_form->appendChild($dom->createElement("phonemic",$inp_form->phonemic));
				$out_form->lastElementChild->before("\n\t\t\t\t\t\t");
				$out_form->appendChild($dom->createElement($prim_script->getAttribute("name"),$inp_form->native));
				$out_form->lastElementChild->before("\n\t\t\t\t\t\t");
				$out_form->lastElementChild->after("\n\t\t\t\t\t");
			}
			$morph->lastElementChild->after("\n\t\t\t\t");
		}
		$def = $dom->createElement("def");
		$out_class->appendChild($def);
		$def->before("\n\t\t\t\t");
		$num = 1;
		foreach ($inp_class->def->senses as $inp_sense) {
			$out_sense = $dom->createElement("sense");
			$def->appendChild($out_sense);
			$out_sense->before("\n\t\t\t\t\t");
			$out_sense->setAttribute("num",$num);
			$p = $dom->createElement("p");
			$p_frag = $dom->createDocumentFragment();
			$p_frag->appendXML($inp_sense->p);
			$p->appendChild($p_frag);
			$out_sense->appendChild($p);
			if (isset($inp_sense->use)) {
				$p->setAttribute("use",$inp_sense->use);
			}
			foreach ($inp_sense->subsenses as $inp_subsense) {
				$out_subsense = $dom->createElement("subsense");
				$out_sense->appendChild($out_subsense);
				$subp = $dom->createElement("p");
				$out_subsense->appendChild($subp);
				$subp_frag = $dom->createDocumentFragment();
				$subp_frag->appendXML($inp_subsense->p);
				$subp->appendChild($subp_frag);
				if (isset($inp_subsense->use)) {
					$subp->setAttribute("use",$inp_subsense->use);
				}
			}
			$num++;
		}
		$def->lastElementChild->after("\n\t\t\t\t");
		$def->after("\n\t\t\t");
	}
	if (isset($new_word->entry->etym)) {
		$etym = $dom->createElement("etym");
		$entry->appendChild($etym);
		$etym_frag = $dom->createDocumentFragment();
		$etym_frag->appendXML($new_word->entry->etym);
		$etym->appendChild($etym_frag);
		$etym->before("\n\t\t\t");
	}
	$entry->lastElementChild->after("\n\t\t");
}

$svg_data = simplexml_load_file("../../..".$prim_script->getAttribute("svg"));

$phonemes = [];
$inflect_enum = [];
function track_phonemes($char) {
	global $phonemes;
	if ($char == "," || $char == " ") {
		return;
	}
	$ok = true;
	foreach ($phonemes as $phoneme) {
		if ($char == $phoneme) {
			$ok = false;
			break;
		}
	}
	if ($ok) {
		$phonemes[] = $char;
	}
}
$sections = simplexml_import_dom($prim_script);
foreach ($sections as $sect) {
	if (isset($sect->data->entry)) {
		foreach ($sect->data->entry as $entry) {
			foreach (mb_str_split($entry->phonemic) as $char) {
				track_phonemes($char);
			}
		}
	}
	if (isset($sect->data->symbols)) {
		foreach ($sect->data->symbols->entry as $entry) {
			foreach (mb_str_split($entry->phonemic) as $char) {
				track_phonemes($char);
			}
		}
	}
	if (isset($sect->data->marks)) {
		foreach ($sect->data->marks->entry as $entry) {
			foreach (mb_str_split($entry->phonemic) as $char) {
				track_phonemes($char);
			}
		}
	}
}
$lang_data = simplexml_import_dom($dom);
foreach ($lang_data->data->entry as $entry) {
	foreach ($entry->class as $class) {
		if (isset($class->morph)) {
			foreach ($class->morph->form as $form) {
				foreach (mb_str_split($form->phonemic) as $char) {
					track_phonemes($char);
				}
				foreach ($form->attributes() as $attr => $val) {
					if ($attr != "id" && $attr != "primary") {
						if ($attr == "role" && ($class["type"] == "verb" || $class["type"] == "auxiliary verb")) {
							/*
							   FIXME --
							   this is a special case due to the fact that we are
							   hard-coding our inflection dimensions inside the
							   database schema (see `/scripts/sql/schema.sql` and
							   `/scripts/xslt/to_sql.xsl`)
							   --
							*/
							$key = "verb_role";
						} else {
							$key = $attr;
						}
						if (isset($inflect_enum[$key])) {
							if (array_search($val,$inflect_enum[$key]) === false) {
								$inflect_enum[$key][] = (string)$val;
							}
						} else {
							$inflect_enum[$key] = array((string)$val);
						}
					}
				}
			}
		}
	}
}
sort($phonemes);

echo "<!DOCTYPE html>\n";
?><html>
	<head>
		<title>Add Word</title>
		<!--<link href="/html/style/languages.css" rel="stylesheet" type="text/css"/>-->
		<style>
			@font-face {font-family: myneography; src: url("/html/assets/font/myneography_standard_medium.ttf");}
			.myneography {
				font-family: myneography;
				font-weight: normal;
				font-size: 1.5em;
				vertical-align: middle;
			}
			.keyboard {
				list-style-type: none;
				margin: 0;
				padding: 0;
			}
			.keyboard li {
				display: inline-flex;
				flex-direction: column;
				border: 1px solid #bbb;
				background-color: #ddd;
				box-shadow: 0 2px 2px 1px rgba(0,0,0,0.5);
				margin: 0.25em;
				padding: 0.1em;
				box-sizing: border-box;
				min-width: 40px;
				height: 50px;
				vertical-align: bottom;
				border-radius: 5px;
				cursor: pointer;
				align-items: center;
			}
			.keyboard li:hover, .keyboard li:focus {
				background-color: #eee;
			}
			.keyboard li:active, .keyboard li.sim_active {
				transform: translate( 0, 2px );
				box-shadow: 0 1px 1px 0 rgba(0,0,0,0.75);
			}
			.keyboard span {
				pointer-events: none;
				user-select: none;
			}
			.keyboard span.native {
				font-size: 24px;
				line-height: 24px;
				overflow: visible;
			}
			.keyboard span.label {
				font-size: 8px;
				line-height: 8px;
				font-family: "sans serif";
				max-width: 40px;
				overflow: visible;
				overflow-wrap: break-word;
			}
			
			h1 {
				text-align: center;
			}
			main {
				display: grid;
				grid-template-columns: 1fr 2fr;
				gap: 1rem;
			}
		</style>
	</head>
	<body>
		<h1>Add Word - <?php echo $dom->firstElementChild->getAttribute("lang_human"); ?></h1>
<?php

if (isset($_POST["new_word"])) {
	$ret = $dom->save($source_file);
	if ($ret === false) {
		echo "\t\t<div class=\"message error\"><code>public DOMDocument::save(\"".$source_file."\")</code> returned <code>FALSE</code></div>\n";
	} else {
		echo "\t\t<div class=\"message success\">Wrote ".$ret." bytes.</div>\n";
	}
	file_put_contents($next_id_file,dictionary::id_encode($next_id)."\n");
}

?>		<main>
			<div class="keyboards">
				<div>
					<h2>IPA Keyboard</h2>
					<ul class="keyboard">
<?php

foreach ($phonemes as $phoneme) {
	echo "\t\t\t\t\t\t<li data-char=\"".$phoneme."\">\n";
	echo "\t\t\t\t\t\t\t<span class=\"phonemic\">".$phoneme."</span>\n";
	echo "\t\t\t\t\t\t</li>\n";
}

?>					</ul>
				</div>
				<hr/>
				<div>
					<h2><?php echo $prim_script->getAttribute("human"); ?> Keyboard</h2>
					<ul class="keyboard">
<?php

function abbreviate($inp) {
	global $prim_script;
	$output = "";
	foreach (explode(" ",$inp) as $word) {
		if (strcasecmp($word,$prim_script->getAttribute("name"))==0 ||
		    strcasecmp($word,"syllable")==0 ||
		    strcasecmp($word,"vowel")==0 ||
		    strcasecmp($word,"mark")==0||
		    strcasecmp($word,"numeral")==0) {
			//skip these words
		} else if (strcasecmp($word,"superscripted")==0) {
			$output .= "SUP";
		} else if (strcasecmp($word,"subscripted")==0) {
			$output .= "SUB";
		} else if (strcasecmp($word,"monophthong")==0) {
			$output .= "MONO";
		} else if (strcasecmp($word,"diphthong")==0) {
			$output .= "DIPH";
		} else {
			$output .= $word;
		}
		$output .= " ";
	}
	return $output;
}
foreach ($svg_data->defs->font->glyph as $glyph) {
	echo "\t\t\t\t\t\t<li data-char=\"".$glyph["unicode"]."\">\n";
	echo "\t\t\t\t\t\t\t<span class=\"native ".$prim_script->getAttribute("name")."\">".$glyph["unicode"]."</span>\n";
	echo "\t\t\t\t\t\t\t<span class=\"label\">".abbreviate($glyph["glyph-name"])."</span>\n";
	echo "\t\t\t\t\t\t</li>\n";
}

?>					</ul>
				</div>
			</div>
			<div class="entry">
<?php
//read inflection table, find correct language
$inflect = json_decode(file_get_contents("../../../conf/languages/inflections.json"));
$lang_index = 0; $found = false;
while (!$found && $lang_index < count($inflect)) {
	if ($_GET["lang"] == $inflect[$lang_index]->language)
		$found = true;
	else
		$lang_index++;
}

?>				<form id="add_word" method="POST">
					<h2>New Word</h2>
					<fieldset class="primary">
						<h3>primary form</h3>
						<div>
							<div>
								<label>
									<input type="radio" name="primary" value="separate" checked/>
									<span>given here: </span>
									<input type="text" id="primary_roman" class="roman" name="roman" placeholder="roman"/>
									<input type="text" id="primary_phonemic" class="phonemic" name="phonemic" placeholder="phonemic"/>
									<input type="text" id="primary_native" class="native <?php echo $prim_script->getAttribute("name"); ?>" name="native"/>
								</label>
							</div>
							<div>
								<label>attach to:
									<select id="primary_attach" name="primary_attach">
									</select>
								</label>
							</div>
							<div>
								<h4>morphology traits</h4>
								<ul id="primary_morph">
<?php

$index = 1;
foreach ($inflect_enum as $dimension => $enum) {
	echo "\t\t\t\t\t\t\t\t\t<li>\n";
	echo "\t\t\t\t\t\t\t\t\t\t<label>".$dimension."\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t<select name=\"primary_dimension-".$index."\" data-dimension=\"".$dimension."\">\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"none\" selected>(none)</option>\n";
	foreach ($enum as $value) {
		echo "\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"".$value."\">".$value."</option>\n";
	}
	echo "\t\t\t\t\t\t\t\t\t\t\t</select>\n";
	echo "\t\t\t\t\t\t\t\t\t\t</label>\n";
	echo "\t\t\t\t\t\t\t\t\t</li>\n";
	$index++;
}

?>								</ul>
							</div>
						<div>
						</div>
							<label>
								<input type="radio" name="primary" value="given"/>
								<span>given below</span>
								<select id="primary_given" name="primary_given">
								</select>
							</label>
						</div>
					</fieldset>
<?php

foreach ($inflect[$lang_index]->groups as $group) {
	$classes = [];
	$inflected = false;
	foreach ($group->classes as $class) {
		$classes[] = $class->name;
		if ($class->inflected) {
			$inflected = true;
		}
	}
	if ($inflected) {
		echo "\t\t\t\t\t<template class=\"group_template\" data-classes=\"".implode(",",$classes)."\">\n";
		$morph_count = 1;
		foreach ($group->tables as $table) {
			$table_filters = [];
			foreach ($table->filter as $filter) {
				$table_filters[] = array(
					"dimension" => $filter->dimension,
					"value" => $filter->value
				);
			}
			echo "\t\t\t\t\t\t<h4>".$table->label."</h4>\n";
			echo "\t\t\t\t\t\t<ul class=\"inflection\">\n";
			foreach ($table->rows as $row) {
				$row_filters = [];
				foreach ($row->filter as $filter) {
					$row_filters[] = array(
						"dimension" => $filter->dimension,
						"value" => $filter->value
					);
				}
				echo "\t\t\t\t\t\t\t<li class=\"word_form\" data-count=\"".$morph_count."\" data-filters=\"".htmlentities(json_encode(array_merge($table_filters,$row_filters)))."\">\n";
				echo "\t\t\t\t\t\t\t\t<label>".$row->label."\n";
				echo "\t\t\t\t\t\t\t\t\t<input type=\"text\" class=\"numbered roman\" name=\"".$morph_count."-roman\" placeholder=\"roman\"/>\n";
				echo "\t\t\t\t\t\t\t\t\t<input type=\"text\" class=\"numbered phonemic\" name=\"".$morph_count."-phonemic\" placeholder=\"phonemic\"/>\n";
				echo "\t\t\t\t\t\t\t\t\t<input type=\"text\" class=\"numbered native ".$prim_script->getAttribute("name")."\" name=\"".$morph_count."-native\"/>\n";
				echo "\t\t\t\t\t\t\t\t\t<label><input type=\"checkbox\" class=\"numbered irregular\" name=\"".$morph_count."-irregular\"/> irregular</label>\n";
				echo "\t\t\t\t\t\t\t\t</label>\n";
				echo "\t\t\t\t\t\t\t</li>\n";
				$morph_count++;
			}
			echo "\t\t\t\t\t\t</ul>\n";
		}
		echo "\t\t\t\t\t\t\n";
		echo "\t\t\t\t\t</template>\n";
	}
}

?>					<template id="class_template">
						<fieldset class="class">
							<h3>class</h3>
							<div>
								<label>type
									<select name="class_type" class="class_type numbered">
<?php

foreach ($inflect[$lang_index]->groups as $group) {
	foreach ($group->classes as $class) {
		echo "\t\t\t\t\t\t\t\t\t\t<option value=\"".$class->name."\">".$class->name."</option>\n";
	}
}

?>									</select>
								</label>
							</div>
							<div class="morph">
							</div>
							<div class="def">
								<h4>Definition</h4>
								<ol class="senses">
								</ol>
								<button class="add_sense">add sense</button>
							</div>
						</fieldset>
					</template>
					<template id="subsense_template">
						<label>usage note: <input type="text" name="subsense_use" class="numbered"/></label><br>
						<textarea name="subsense" class="numbered"></textarea>
					</template>
					<template id="sense_template">
						<label>usage note: <input type="text" name="sense_use" class="numbered"/></label><br>
						<textarea name="sense" class="numbered"></textarea>
						<ul class="subsenses">
						</ul>
						<button class="add_subsense" data-subsenses="0">add subsense</button>
					</template>
					<button id="add_class">add class</button>
					<fieldset>
						<label>
							<h3>etymology</h3>
							<textarea id="etym" name="etym"></textarea>
						</label>
					</fieldset>
					<input type="hidden" id="new_word" name="new_word"/>
					<button id="save_word">save word</button>
				</form>
			</div>
		</main>
		<script>
			function keypress(event) {
				event.preventDefault();
				event.target.classList.add("sim_active");
				let inp = document.activeElement;
				if (typeof inp !== "undefined" && inp !== null && (inp.tagName.toLowerCase() == "input" || inp.tagName.toLowerCase() == "textarea")) {
					let pre = inp.value.substring(0,inp.selectionStart);
					let post = inp.value.substring(inp.selectionEnd,inp.value.length);
					let selection = inp.selectionStart+1;
					inp.value = pre+event.target.dataset.char+post;
					inp.setSelectionRange(selection,selection);
				}
			}
			function keyunpress(event) {
				let list = document.getElementsByClassName("sim_active");
				while (list.length > 0) {
					list[0].classList.remove("sim_active");
				}
			}
			let keyboards = document.getElementsByClassName("keyboard");
			for (let i=0; i < keyboards.length; i++) {
				for (let j=0; j < keyboards[i].childElementCount; j++) {
					keyboards[i].children[j].addEventListener("pointerdown", keypress, {capture: true});
				}
			}
			window.addEventListener("pointerup", keyunpress, {capture: true});
			class_count = 0;
			function add_class() {
				class_count++;
				let t = document.getElementById("class_template");
				let c = t.content.cloneNode(true);
				let b = document.getElementById("add_class");
				b.parentElement.insertBefore(c,b);
				c = b.previousElementSibling;
				let list = c.querySelectorAll(".numbered");
				for (let i=0; i < list.length; i++) {
					list[i].setAttribute("name",list[i].getAttribute("name")+"-"+class_count.toString().padStart(3,"0"));
				}
				let type = c.querySelector("select");
				type.addEventListener("change",show_group);
				let e = new Event("change");
				type.dispatchEvent(e);
				b = c.querySelector(".add_sense");
				b.addEventListener("click", add_sense);
				e = new Event("click");
				b.dispatchEvent(e);
			}
			function show_group(event) {
				let primary_attach = document.getElementById("primary_attach");
				primary_attach.innerHTML = "";
				let form = document.getElementById("add_word");
				let classes = form.querySelectorAll("fieldset.class");
				for (let i=0; i < classes.length; i++) {
					let class_type = classes[i].querySelector("select.class_type");
					let opt = document.createElement("option");
					opt.value = (i+1).toString()+"_"+class_type.value;
					opt.innerText = class_type.value;
					primary_attach.appendChild(opt);
				}
				let groups = document.getElementsByClassName("group_template");
				let group = null;
				for (let i=0; i < groups.length && group === null; i++) {
					let class_types = groups[i].dataset.classes.split(",");
					for (let j=0; j < class_types.length && group === null; j++) {
						if (event.target.value.toLowerCase() == class_types[j].toLowerCase()) {
							group = groups[i];
						}
					}
				}
				let parent = event.target.closest("fieldset");
				let morph = parent.querySelector(".morph");
				morph.innerHTML = "";
				if (group !== null) {
					let clone = group.content.cloneNode(true);
					morph.appendChild(clone);
					let list = morph.querySelectorAll(".numbered");
					for (let i=0; i < list.length; i++) {
						list[i].setAttribute("name",list[i].getAttribute("name")+"-"+class_count.toString().padStart(3,"0"));
					}
				}
				let primary_given = document.getElementById("primary_given");
				primary_given.innerHTML = "";
				for (let i=0; i < classes.length; i++) {
					let class_type = classes[i].querySelector("select.class_type");
					let word_forms = classes[i].querySelectorAll("li.word_form");
					for (let j=0; j < word_forms.length; j++) {
						let label = class_type.value+": ";
						let filters = JSON.parse(word_forms[j].dataset.filters);
						let tmp = [];
						for (let k=0; k < filters.length; k++) {
							tmp.push(filters[k].value);
						}
						label += tmp.join(" ");
						let opt = document.createElement("option");
						opt.value = (i+1).toString()+"_"+class_type.value+"-"+word_forms[j].dataset.count;
						opt.innerText = label;
						primary_given.appendChild(opt);
					}
				}
			}
			function add_subsense(event) {
				let subsense_count = Number(event.target.dataset.subsenses)+1;
				event.target.dataset.subsenses = subsense_count.toString();
				let t = document.getElementById("subsense_template");
				let c = t.content.cloneNode(true);
				let ul = event.target.parentElement.querySelector("ul.subsenses");
				let li = document.createElement("li");
				ul.appendChild(li);
				li.appendChild(c);
				let list = li.querySelectorAll(".numbered");
				for (let i=0; i < list.length; i++) {
					list[i].setAttribute("name",list[i].getAttribute("name")+"-"+subsense_count.toString().padStart(3,"0"));
				}
			}
			sense_count = 0;
			function add_sense(event) {
				sense_count++;
				let t = document.getElementById("sense_template");
				let c = t.content.cloneNode(true);
				let ol = event.target.parentElement.querySelector("ol.senses");
				let li = document.createElement("li");
				ol.appendChild(li);
				li.appendChild(c);
				let list = li.querySelectorAll(".numbered");
				for (let i=0; i < list.length; i++) {
					list[i].setAttribute("name",list[i].getAttribute("name")+"-"+sense_count.toString().padStart(3,"0"));
				}
				let b = li.querySelector(".add_subsense");
				b.addEventListener("click",add_subsense);
				let e = new Event("click");
				b.dispatchEvent(e);
			}
			function save_word(event) {
				let output = {entry:{classes:[]}};
				let form = document.getElementById("add_word");
				let classes = document.getElementsByClassName("class");
				for (let i=0; i < classes.length; i++) {
					let type = classes[i].querySelector(".class_type");
					output.entry.classes[i] = {
						type: type.value
					};
					let morph = classes[i].querySelector(".morph");
					let primary_radio = document.querySelector("input[name=\"primary\"]:checked").value;
					let primary_attach = document.getElementById("primary_attach");
					let primary_given = document.getElementById("primary_given");
					let primary_given_val = primary_given.value.split("-");
					let primary_is_attached = (primary_radio == "separate" && primary_attach.value == (i+1).toString()+"_"+type.value);
					let primary_is_given = (primary_radio == "given" && primary_given_val[0] == (i+1).toString()+"_"+type.value);
					if (morph.childElementCount > 0 || primary_is_attached) {
						output.entry.classes[i].morph = {forms:[]};
						if (primary_is_attached) {
							let f = {attr:{primary: "yes"}};
							let primary_morph = document.getElementById("primary_morph");
							let list = primary_morph.querySelectorAll("select");
							for (let j=0; j < list.length; j++) {
								if (list[j].value != "none") {
									f.attr[list[j].dataset.dimension] = list[j].value;
								}
							}
							let primary_roman = document.getElementById("primary_roman");
							f.roman = primary_roman.value;
							let primary_phonemic = document.getElementById("primary_phonemic");
							f.phonemic = primary_phonemic.value;
							let primary_native = document.getElementById("primary_native");
							f.native = primary_native.value;
							output.entry.classes[i].morph.forms[output.entry.classes[i].morph.forms.length] = f;
						}
						let list = morph.querySelectorAll("li.word_form");
						for (let j=0; j < list.length; j++) {
							let f = {attr:{}};
							if (primary_is_given && primary_given_val[1] == list[j].dataset.count) {
								f.attr.primary = "yes";
							} else {
								f.attr.primary = "no";
							}
							let filters = JSON.parse(list[j].dataset.filters);
							for (let k=0; k < filters.length; k++) {
								f.attr[filters[k].dimension] = filters[k].value;
							}
							let irregular = list[j].querySelector(".irregular");
							f.attr["irregular"] = (irregular.checked ? "yes" : "no");
							let roman = list[j].querySelector(".roman");
							f.roman = roman.value;
							let phonemic = list[j].querySelector(".phonemic");
							f.phonemic = phonemic.value;
							let native = list[j].querySelector(".native");
							f.native = native.value;
							output.entry.classes[i].morph.forms[output.entry.classes[i].morph.forms.length] = f;
						}
					}
					output.entry.classes[i].def = {senses:[]};
					let senses = classes[i].querySelector("ol.senses");
					for (let j=0; j < senses.childElementCount; j++) {
						let use = senses.children[j].querySelector("input");
						let p = senses.children[j].querySelector("textarea");
						if (p.value.length > 0) {
							output.entry.classes[i].def.senses[j] = {};
							if (use.value.length > 0) {
								output.entry.classes[i].def.senses[j].use = use.value;
							}
							output.entry.classes[i].def.senses[j].p = p.value;
							output.entry.classes[i].def.senses[j].subsenses = [];
							let subsenses = senses.children[j].querySelector("ul.subsenses");
							for (let k=0; k < subsenses.childElementCount; k++) {
								let subuse = subsenses.children[k].querySelector("input");
								let subp = subsenses.children[k].querySelector("textarea");
								if (subp.value.length > 0) {
									output.entry.classes[i].def.senses[j].subsenses[k] = {};
									if (subuse.value.length > 0) {
										output.entry.classes[i].def.senses[j].subsenses[k].use = subuse.value;
									}
									output.entry.classes[i].def.senses[j].subsenses[k].p = subp.value;
								}
							}
						}
					}
				}
				let etym = document.getElementById("etym");
				if (etym.value.length > 0) {
					output.entry.etym = etym.value;
				}
				
				let h = document.getElementById("new_word");
				h.value = JSON.stringify(output);
				/*
				let pre = document.getElementById("save_output");
				if (pre === null) {
					pre = document.createElement("pre");
					pre.setAttribute("id","save_output");
				} else {
					pre.innerText = "";
				}
				pre.innerText = JSON.stringify(output,null,2);
				form.parentElement.appendChild(pre);*/
				form.submit();
			}
			let button = document.getElementById("add_class");
			button.addEventListener("click", add_class);
			button = document.getElementById("save_word");
			button.addEventListener("click", save_word);
			let form = document.getElementById("add_word");
			form.addEventListener("submit", function (event) {
				let h = document.getElementById("new_word");
				if (h.value.length < 1) {
					event.preventDefault();
				}
			});
			let h = document.getElementById("new_word");
			h.value = "";
			add_class();
		</script>
	</body>
</html>

