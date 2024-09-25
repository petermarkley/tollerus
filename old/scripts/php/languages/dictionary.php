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

class dictionary {
	public $conf;
	public $dbh;
	protected $dbs;
	public $entry;
	public $enum = array();
	public $intro = null;
	public $script_data = array();
	
	//these values are read-only to public; please set using id_valid()
	protected $id = null;
	protected $fr = null;
	protected $lang = null;
	protected $lang_key = null;
	public function id_get() {
		return array(
			"id" => $this->id,
			"fr" => $this->fr,
			"lang" => $this->lang,
			"lang_key" => $this->lang_key
		);
	}
	
	const SAFE = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_"; //charset of Base64 (the variant that's URL- and filename-safe, RFC 4648 §5)
	const SIZE = 16; //maximum number of base64url digits allowed, finite for security reasons
	// http://php.net/manual/en/function.base64-encode.php#121767
	private static function base64url_encode( $data ){
	  return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
	}
	private static function base64url_decode( $data ){
	  return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
	}
	//functions to transcode between decimal integers and base64url strings
	public static function id_encode($id) {
		return self::base64url_encode(pack("N@3",$id<<8));
	}
	public static function id_decode($id) {
		return unpack("Nint",self::base64url_decode($id."AA"))["int"]>>8;
	}
	
	//takes path to json conf file containing mysql credentials, and connects to database
	function __construct($conf_file) {
		$this->conf = json_decode(file_get_contents($conf_file));
		try {
			$this->dbh = new PDO("mysql:host=" . $this->conf->host . ";dbname=" . $this->conf->database . ";charset=utf8",$this->conf->user,$this->conf->password);
		} catch (PDOException $e) {
			echo "PDO: ".$e->getMessage()."\n";
			exit();
		}
		$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//data structure
		$this->entry = (object) [
			"primary_morph" => null,
			"lang"          => "",
			"lang_human"    => "",
			"script"        => "",
			"script_human"  => "",
			"native"        => "",
			"roman"         => "",
			"phonemic"      => "",
			"etym"          => "",
			"classes"       => array(),
			"der"           => array(),
			"ext"           => array()
		];
		//retrieve enumerations
		$this->dbs = $this->dbh->prepare("SELECT * FROM `enum`;");
		$this->dbs->execute();
		foreach ($this->dbs as $row) {
			if (!isset($this->enum[$row["table"]]))
				$this->enum[$row["table"]] = array();
			$this->enum[$row["table"]][$row["key"]] = [$row["value"],$row["human"]];
		}
	}
	
	//takes a base64url string; sets and returns confirmed entry_id from database, and any matching class_id or morph_id (usable as url fragment)
	public function id_valid($in) {
		//sanity check
		$ok = true;
		if (strlen($in) > self::SIZE || strlen($in) < 1)
			$ok = false;
		for ($i=0; $i<strlen($in) && $ok == true; $i++) {
			if (strpos(self::SAFE,$in[$i]) === false)
				$ok = false;
		}
		
		//input is sane, hit database
		$id = null;
		$fr = null;
		$lang = null;
		if ($ok) {
			//check for entry_id
			$this->dbs = $this->dbh->prepare("SELECT 1 FROM `entries` WHERE `entry_id` = ?;");
			$this->dbs->execute([self::id_decode($in)]);
			if ($this->dbs->fetch(PDO::FETCH_NUM)) {
				//input is found as entry
				$id = $in;
				$fr = null;
				$lang = null;
			} else {
				//check for class_id or morph_id
				$this->dbs = $this->dbh->prepare("SELECT `entry_id` FROM `classes` WHERE `class_id` = ? UNION SELECT `entry_id` FROM `morph` WHERE `morph_id` = ?;");
				$tmp = self::id_decode($in);
				$this->dbs->execute([$tmp,$tmp]);
				if ($res = $this->dbs->fetch(PDO::FETCH_NUM)) {
					//input is found as fragment
					$id = self::id_encode($res[0]);
					$fr = $in;
					$lang = null;
				} else {
					//check for alphabet glyph or numeral
					$this->dbs = $this->dbh->prepare("SELECT `lang` FROM `intros` WHERE `content` LIKE ? UNION SELECT `lang` FROM `script_data` WHERE `content` LIKE ?;");
					$this->dbs->execute(["%".$in."%","%".$in."%"]);
					if ($res = $this->dbs->fetch(PDO::FETCH_NUM)) {
						//input is found as a fragment inside a lang intro or scripts
						$id = null;
						$fr = $in;
						$lang = $this->enum["enum_languages"][$res[0]][0];
					}
				}
			}
		}
		
		$this->id = $id;
		$this->fr = $fr;
		$this->lang = $lang;
		return array(
			"id" => $id,
			"fr" => $fr,
			"lang" => $lang
		);
	}
	
	//reads intro and script data from database
	public function intro_read($lang) {
		//sanity check
		$ok = true;
		if (strlen($lang) > self::SIZE || strlen($lang) < 1)
			$ok = false;
		for ($i=0; $i<strlen($lang) && $ok == true; $i++) {
			if (strpos(self::SAFE,$lang[$i]) === false)
				$ok = false;
		}
		if (!$ok) {
			return null;
		}
		$lang_key = null;
		for ($i=1; $i <= count($this->enum["enum_languages"]) && $lang_key === null; $i++) {
			if ($this->enum["enum_languages"][$i][0] == $lang) {
				$lang_key = $i;
			}
		}
		if ($lang_key === null) {
			return null;
		}
		$this->lang_key = $lang_key;
		//input is sane, hit database
		$this->dbs = $this->dbh->prepare("SELECT `content` FROM `intros` WHERE `lang` = ?;");
		$this->dbs->execute([$lang_key]);
		if ($res = $this->dbs->fetch(PDO::FETCH_NUM)) {
			$this->intro = $res[0];
		}
		$this->dbs = $this->dbh->prepare("SELECT `title`, `content` FROM `script_data` WHERE `lang` = ?;");
		$this->dbs->execute([$lang_key]);
		foreach($this->dbs as $row) {
			$this->script_data[] = $row;
		}
	}
	
	//if id_valid() has been called, reads entry from database
	public function entry_read() {
		if ($this->id !== null) {
			//retrieve and parse entry
			$this->dbs = $this->dbh->prepare("SELECT " .
					"e.`lang`, " .
					"e.`primary_morph`, " .
					"m.`script`, " .
					"m.`native`, " .
					"m.`roman`, " .
					"m.`phonemic`, " .
					"e.`etym` " .
				"FROM `entries` AS e JOIN `morph` AS m ON e.`primary_morph` = m.`morph_id` WHERE e.`entry_id` = ?;");
			$this->dbs->execute([self::id_decode($this->id)]);
			$e = $this->dbs->fetch(PDO::FETCH_ASSOC);
			$this->entry->primary_morph = $e["primary_morph"];
			$this->entry->lang          = $this->enum["enum_languages"][$e["lang"  ]][0];
			$this->entry->lang_human    = $this->enum["enum_languages"][$e["lang"  ]][1];
			$this->entry->script        = $this->enum["enum_scripts"  ][$e["script"]][0];
			$this->entry->script_human  = $this->enum["enum_scripts"  ][$e["script"]][1];
			$this->entry->native        = $e["native"];
			$this->entry->roman         = $e["roman"];
			$this->entry->phonemic      = $e["phonemic"];
			$this->entry->etym          = $e["etym"];
			
			//retrieve and parse classes and morphology
			$this->dbs = $this->dbh->prepare("SELECT " .
					"c.`class_id`, " .
					"c.`class_type`, " .
					"c.`priority`, " .
					"m.`morph_id`, " .
					"m.`script`, " .
					"m.`native`, " .
					"m.`roman`, " .
					"m.`phonemic`, " .
					"m.`definiteness`, " .
					"m.`number`, " .
					"m.`case`, " .
					"m.`verb_role`, " .
					"m.`tense`, " .
					"m.`aspect`, " .
					"m.`person`, " .
					"m.`voice`, " .
					"m.`irregular` " .
				"FROM `classes` AS c LEFT JOIN `morph` AS m ON c.`class_id` = m.`class_id` WHERE c.`entry_id` = ?;");
			$this->dbs->execute([self::id_decode($this->id)]);
			foreach ($this->dbs as $row) {
				if (!isset($this->entry->classes[$row["class_id"]])) {
					$this->entry->classes[$row["class_id"]] = (object) [
						"id" => self::id_encode($row["class_id"]),
						"type" => $this->enum["enum_classes"][$row["class_type"]][0],
						"priority" => $row["priority"],
						"morph" => array(),
						"def" => array(),
					];
				}
				if ($row["morph_id"] !== null) {
					$this->entry->classes[$row["class_id"]]->morph[$row["morph_id"]] = (object) [
						"id" => self::id_encode($row["morph_id"]),
						"script" => $this->enum["enum_scripts"][$row["script"]][0],
						"native" => $row["native"],
						"roman" => $row["roman"],
						"phonemic" => $row["phonemic"],
						"inflect" => [
							"definiteness" => ($row["definiteness"] !== null ? $this->enum["inflect_definiteness"][$row["definiteness"]][0] : null),
							"number"       => ($row["number"]       !== null ? $this->enum["inflect_number"      ][$row["number"]][0]       : null),
							"case"         => ($row["case"]         !== null ? $this->enum["inflect_case"        ][$row["case"]][0]         : null),
							"verb_role"    => ($row["verb_role"]    !== null ? $this->enum["inflect_verb_role"   ][$row["verb_role"]][0]    : null),
							"tense"        => ($row["tense"]        !== null ? $this->enum["inflect_tense"       ][$row["tense"]][0]        : null),
							"aspect"       => ($row["aspect"]       !== null ? $this->enum["inflect_aspect"      ][$row["aspect"]][0]       : null),
							"person"       => ($row["person"]       !== null ? $this->enum["inflect_person"      ][$row["person"]][0]       : null),
							"voice"        => ($row["voice"]        !== null ? $this->enum["inflect_voice"       ][$row["voice"]][0]        : null),
						],
						"irregular" => $row["irregular"]
					];
				}
			}
			
			//retrieve and parse definitions
			$this->dbs = $this->dbh->prepare("SELECT " .
					"s1.`class_id`, " .
					"s1.`sense_id`, " .
					"s1.`num` AS `num1`, " .
					"s1.`p`   AS `p1`, " .
					"s1.`use` AS `use1`, " .
					"s2.`subsense_id`, " .
					"s2.`num` AS `num2`, " .
					"s2.`p`   AS `p2`, " .
					"s2.`use` AS `use2` " .
				"FROM `senses` AS s1 LEFT JOIN `subsenses` AS s2 ON s1.`sense_id` = s2.`sense_id` WHERE s1.`entry_id` = ?;");
			$this->dbs->execute([self::id_decode($this->id)]);
			foreach ($this->dbs as $row) {
				if (!isset($this->entry->classes[$row["class_id"]]->def[$row["sense_id"]])) {
					$this->entry->classes[$row["class_id"]]->def[$row["sense_id"]] = (object) [
						"num"       => $row["num1"],
						"p"         => $row["p1"],
						"use"       => $row["use1"],
						"subsenses" => array()
					];
				}
				if ($row["subsense_id"] !== null) {
					$this->entry->classes[$row["class_id"]]->def[$row["sense_id"]]->subsenses[] = (object) [
						"num" => $row["num2"],
						"p"   => $row["p2"],
						"use" => $row["use2"]
					];
				}
			}
			
			//sort class structures
			usort($this->entry->classes,
				function($a,$b) {
					return $a->priority - $b->priority;
				}
			);
			foreach ($this->entry->classes as $k1 => $v1) {
				usort($this->entry->classes[$k1]->def,
					function($a,$b) {
						return $a->num - $b->num;
					}
				);
				foreach ($this->entry->classes[$k1]->def as $k2 => $v2) {
					if (!empty($v2->subsenses)) {
						usort($this->entry->classes[$k1]->def[$k2]->subsenses,
							function($a,$b) {
								return $a->num - $b->num;
							}
						);
					}
				}
			}
			
			//retrieve derivatives
			$this->dbs = $this->dbh->prepare("SELECT " .
					"e.`lang`, " .
					"d.`target_entry_id`, " .
					"m.`roman` " .
				"FROM `derivatives` AS d " .
				"JOIN `entries`     AS e ON d.`parent_entry_id` = e.`entry_id` " .
				"JOIN `morph`       AS m ON e.`primary_morph`   = m.`morph_id` " .
				"WHERE d.`parent_entry_id` = ?;");
			$this->dbs->execute([self::id_decode($this->id)]);
			foreach ($this->dbs as $row) {
				$this->entry->der[] = (object) [
					"lang"            => $this->enum["enum_languages"][$row["lang"]][0],
					"target_entry_id" => $row["target_entry_id"],
					"roman"           => $row["roman"]
				];
			}
			usort($this->entry->der,
				function($a,$b) {
					return strcmp($a->roman,$b->roman);
				}
			);
			
			//retrieve extras
			$this->dbs = $this->dbh->prepare("SELECT " .
					"e.`lang`, " .
					"x.*, " .
					"m.`roman` " .
				"FROM `extras`  AS x " .
				"JOIN `entries` AS e ON x.`parent_entry_id` = e.`entry_id` " .
				"JOIN `morph`   AS m ON e.`primary_morph`   = m.`morph_id` " .
				"WHERE x.`parent_entry_id` = ?;");
			$this->dbs->execute([self::id_decode($this->id)]);
			foreach ($this->dbs as $row) {
				$this->entry->ext[] = (object) [
					"extra_type"      => $this->enum["enum_extras"][$row["extra_type"]][0],
					"lang"            => $this->enum["enum_languages"][$row["lang"]][0],
					"target_entry_id" => $row["target_entry_id"],
					"roman"           => $row["roman"],
					"resource"        => $row["resource"],
					"display_num"     => $row["display_num"],
					"caption"         => $row["caption"]
				];
			}
		}
	}
	
	//filter inflections
	protected static function morph_filter($m,$filter) {
		foreach ($m as $key => $obj) {
			$ok = true;
			foreach ($filter as $f) {
				if ($obj->inflect[$f->dimension] != $f->value) {
					$ok = false;
					break;
				}
			}
			if (!$ok)
				unset($m[$key]);
		}
		return $m;
	}
	
	//takes a whitespace prefix for tab indentation; prints the stored language intro and script data
	public function intro_print($t) {
		if ($this->intro !== null) {
			echo $t."<div class=\"dictionary-intro\">\n";
			echo $t."\t<h2>".$this->enum["enum_languages"][$this->lang_key][1]."</h2>\n";
			echo $t."\t".$this->intro."\n";
			echo $t."</div>\n";
		}
		foreach ($this->script_data as $section) {
			echo $t."<div class=\"dictionary-section\">\n";
			echo $t."\t<h3>".$section["title"]."</h3>\n";
			echo $t."\t".$section["content"]."\n";
			echo $t."</div>\n";
		}
	}
	
	//takes a file path to a json file that defines how inflection tables should be displayed; takes a whitespace prefix for tab indentation; prints the stored entry
	public function entry_print($inflect_file,$t) {
		if ($this->id !== null) {
			//read inflection table, find correct language
			$inflect = json_decode(file_get_contents($inflect_file));
			$lang_index = 0; $found = false;
			while (!$found && $lang_index < count($inflect)) {
				if ($this->entry->lang == $inflect[$lang_index]->language)
					$found = true;
				else
					$lang_index++;
			}
			
			//print entry header
			echo $t."<div class=\"dictionary-entry dictionary-".$this->entry->lang."\">\n";
			echo $t."\t<span class=\"dictionary-entry-lang\">Language: <a href=\"?lang=".$this->entry->lang."\">".$this->entry->lang_human."</a></span>\n";
			echo $t."\t<h3>\n";
			echo $t."\t\t<a id=\"".self::id_encode($this->entry->primary_morph)."\"><span class=\"dictionary-header-roman\">".$this->entry->roman."</span></a>\n";
			echo $t."\t\t<span class=\"dictionary-header-phonemic\">/".$this->entry->phonemic."/</span>\n";
			echo $t."\t\t<span class=\"dictionary-header-native dictionary-header-".$this->entry->script."\">".$this->entry->native."</span>\n";
			echo $t."\t\t<a class=\"dictionary-header-permalink\" href=\"/lore/dictionary/?id=".$this->id."\">&#x1f517;</a>\n";
			echo $t."\t</h3>\n";
			//print entry body; loop through grammatical classes (not to be confused with PHP classes, or CSS classes ... lol)
			echo $t."\t<div class=\"dictionary-body\">\n";
			foreach ($this->entry->classes as $key => $class) {
				//find correct class group
				$group_index = 0; $found = false;
				while (!$found && $group_index < count($inflect[$lang_index]->groups)) {
					//find the matching class within the group
					foreach ($inflect[$lang_index]->groups[$group_index]->classes as $inflect_class) {
						if ($class->type == $inflect_class->name) {
							$found = true;
							$inflected = $inflect_class->inflected;
							break;
						}
					}
					if (!$found)
						$group_index++;
				}
				//print label for grammatical class
				echo $t."\t\t<div class=\"dictionary-class\">\n";
				echo $t."\t\t\t<a id=\"".$class->id."\" class=\"dictionary-class-label\">".$class->type."</a>\n";
				//print inflection tables, if any
				if ($inflected) {
					echo $t."\t\t\t<div class=\"dictionary-morph\">\n";
					$cnt = count($inflect[$lang_index]->groups[$group_index]->tables);
					for ($pos = 0; $pos < $cnt; $pos++) {
						$table = $inflect[$lang_index]->groups[$group_index]->tables[$pos];
						//filter inflections
						$table_morph = self::morph_filter($class->morph,$table->filter);
						//begin table grouping
						if ($pos == 0 || !$table->stack || ($table->stack && !$inflect[$lang_index]->groups[$group_index]->tables[$pos-1]->stack)) {
							echo $t."\t\t\t\t<div class=\"dictionary-inflect-container".($table->stack?" dictionary-inflect-stack":"")."\">\n";
						}
						//print table
						echo $t."\t\t\t\t\t<table class=\"dictionary-inflect".($table->stack?" dictionary-inflect-stack":"")."\">\n";
						echo $t."\t\t\t\t\t\t<thead";
						if ($table->fold_table && $table->align_on_stack) {
							echo " class=\"dictionary-inflect-mob dictionary-inflect-align_on_stack\"";
						}
						else {
							if ($table->fold_table)
								echo " class=\"dictionary-inflect-mob\"";
							if ($table->align_on_stack)
								echo " class=\"dictionary-inflect-align_on_stack\"";
						}
						echo ">\n";
						echo $t."\t\t\t\t\t\t\t<tr>\n";
						if ($table->fold_rows) {
							echo $t."\t\t\t\t\t\t\t\t<th colspan=\"3\" class=\"dictionary-inflect-dsk\">".$table->label."</th>\n";
							echo $t."\t\t\t\t\t\t\t\t<th colspan=\"5\" class=\"dictionary-inflect-mob\">".$table->label."</th>\n";
						}
						else {
							if ($table->align_on_stack) {
								echo $t."\t\t\t\t\t\t\t\t<td colspan=\"2\" class=\"dictionary-inflect-spacer dictionary-inflect-dsk\"></td>\n";
								echo $t."\t\t\t\t\t\t\t\t<th colspan=\"3\" class=\"dictionary-inflect-dsk\">".$table->label."</th>\n";
								echo $t."\t\t\t\t\t\t\t\t<th colspan=\"5\" class=\"dictionary-inflect-mob\">".$table->label."</th>\n";
							}
							else
								echo $t."\t\t\t\t\t\t\t\t<th colspan=\"5\">".$table->label."</th>\n";
						}
						echo $t."\t\t\t\t\t\t\t</tr>\n";
						echo $t."\t\t\t\t\t\t</thead>\n";
						echo $t."\t\t\t\t\t\t<tbody>\n";
						foreach ($table->rows as $row) {
							//filter inflections
							$row_morph = self::morph_filter($table_morph,$row->filter);
							//print row ...
							echo $t."\t\t\t\t\t\t\t<tr>\n";
							//row label
							echo $t."\t\t\t\t\t\t\t\t<th".($table->fold_rows?" class=\"dictionary-inflect-mob\"":"").">";
							$b = isset($row->brief);
							$l = isset($row->long);
							if ($b || $l)
								echo "<span class=\"dictionary-inflect-dsk".(!$b?" dictionary-inflect-mob":"").(!$l?" dictionary-inflect-lrg":"")."\">".$row->label."</span>";
							else
								echo $row->label;
							if ($b) echo "<abbr title=\"".$row->label."\" class=\"dictionary-inflect-mob\">".$row->brief."</abbr>";
							if ($l) echo "<span class=\"dictionary-inflect-lrg\">".$row->long."</span>";
							echo "</th><td class=\"dictionary-inflect-spacer".($table->fold_rows?" dictionary-inflect-mob":"")."\"></td>\n";
							//row data
							if (!empty($row_morph)) {
								//print roman
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-roman\">";
								$i=0;
								$last=count($row_morph)-1;
								foreach ($row_morph as $morph) {
									echo "<a id=\"".$morph->id."\"";
									if ($morph->irregular == true)
										echo " class=\"dictionary-inflect-irregular\"";
									echo ">";
									echo $morph->roman;
									echo "</a>";
									if ($i !== $last)
										echo "<br>";
									$i++;
								}
								echo "</td>\n";
								//print phonemic
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-phonemic\">";
								$i=0;
								$last=count($row_morph)-1;
								foreach ($row_morph as $morph) {
									echo "<span";
									if ($morph->irregular == true)
										echo " class=\"dictionary-inflect-irregular\"";
									echo ">/";
									echo $morph->phonemic;
									echo "/</span>";
									if ($i !== $last)
										echo "<br>";
									$i++;
								}
								echo "</td>\n";
								//print native
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-native dictionary-inflect-".$this->entry->script."\">";
								$i=0;
								$last=count($row_morph)-1;
								foreach ($row_morph as $morph) {
									echo "<span";
									if ($morph->irregular == true)
										echo " class=\"dictionary-inflect-irregular\"";
									echo ">";
									echo $morph->native;
									echo "</span>";
									if ($i !== $last)
										echo "<br>";
									$i++;
								}
								echo "</td>\n";
							}
							else {
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-roman dictionary-inflect-empty\">&#8211;</td>\n";
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-phonemic dictionary-inflect-empty\">&#8211;</td>\n";
								echo $t."\t\t\t\t\t\t\t\t<td class=\"dictionary-inflect-native dictionary-inflect-empty\">&#8211;</td>\n";
							}
							echo $t."\t\t\t\t\t\t\t</tr>\n";
						}
						echo $t."\t\t\t\t\t\t</tbody>\n";
						echo $t."\t\t\t\t\t</table>\n";
						//end table grouping
						if ($pos == $cnt-1 || !$table->stack || ($table->stack && !$inflect[$lang_index]->groups[$group_index]->tables[$pos+1]->stack)) {
							echo $t."\t\t\t\t</div>\n";
						}
					}
					echo $t."\t\t\t</div>\n";
				}
				//print definition
				echo $t."\t\t\t<ol class=\"dictionary-def";
				if (count($class->def) == 1)
					echo " dictionary-def-single";
				echo "\">\n";
				foreach ($class->def as $sense) {
					echo $t."\t\t\t\t<li>\n";
					echo $t."\t\t\t\t\t<p>\n";
					if (!empty($sense->use)) {
						echo $t."\t\t\t\t\t\t<span class=\"dictionary-def-use\">".$sense->use."</span>\n";
					}
					echo $t."\t\t\t\t\t\t<span class=\"dictionary-def-body\">".$sense->p."</span>\n";
					echo $t."\t\t\t\t\t</p>\n";
					if (!empty($sense->subsenses)) {
						echo $t."\t\t\t\t\t<ul>\n";
						foreach ($sense->subsenses as $subsense) {
							echo $t."\t\t\t\t\t\t<li>\n";
							echo $t."\t\t\t\t\t\t\t<p>\n";
							if (!empty($subsense->use)) {
								echo $t."\t\t\t\t\t\t\t\t<span class=\"dictionary-def-use\">".$subsense->use."</span>\n";
							}
							echo $t."\t\t\t\t\t\t\t\t<span class=\"dictionary-def-body\">".$subsense->p."</span>\n";
							echo $t."\t\t\t\t\t\t\t</p>\n";
							echo $t."\t\t\t\t\t\t</li>\n";
						}
						echo $t."\t\t\t\t\t</ul>\n";
					}
					echo $t."\t\t\t\t</li>\n";
				}
				echo $t."\t\t\t</ol>\n";
				echo $t."\t\t</div>\n";
			}
			echo $t."\t</div>\n";
			//print etymology, if any
			if (!empty($this->entry->etym)) {
				echo $t."\t<div class=\"dictionary-etym\">\n";
				echo $t."\t\t<p><span class=\"dictionary-etym-label\">origin:</span> <span class=\"dictionary-etym-body\">".$this->entry->etym."</span></p>\n";
				echo $t."\t</div>\n";
			}
			echo $t."</div>\n";
		}
	}
	
	//takes a search type and key and returns HTML of search results
	public function search($type,$key,$id,$fr,$t) {
		$none = $t."<p class=\"dictionary-search-none\">No results</p>\n";
		if (strlen($key) < 2) {
			echo $none;
			return;
		}
		switch ($type) {
			case "roman":
				$this->dbs = $this->dbh->prepare("SELECT `entry_id`, `morph_id`, `roman`, `native`, `lang` FROM `morph` WHERE `roman` LIKE ?;");
				$this->dbs->execute(["%".$key."%"]);
			break;
			case "def":
				$this->dbs = $this->dbh->prepare("
					SELECT `morph`.`entry_id`, `morph`.`morph_id`, `morph`.`roman`, `morph`.`native`, `morph`.`lang` FROM `morph`
					LEFT JOIN (SELECT `entry_id` FROM `senses` WHERE `p` LIKE ? UNION SELECT `entry_id` FROM `subsenses` WHERE `p` LIKE ?) AS `def`
					ON `morph`.`entry_id` = `def`.`entry_id`
					WHERE `def`.`entry_id`;
				");
				$this->dbs->execute(["%".$key."%","%".$key."%"]);
			break;
			default:
				echo $none;
				return;
			break;
		}
		$count = 0;
		$output = $t."<ul>\n";
		foreach ($this->dbs as $row) {
			if ($count < 50) {
				$count++;
				$entry_id = self::id_encode($row["entry_id"]);
				$morph_id = self::id_encode($row["morph_id"]);
				$output .= $t."\t<li>\n";
				$output .= $t."\t\t<a class=\"dictionary-search-result";
				if ($entry_id == $id && (!$fr || $fr == $morph_id)) {
					$output .= " dictionary-search-active";
				}
				$output .= "\" data-entry=\"".$entry_id."\" data-morph=\"".$morph_id."\" onclick=\"dictionary_fetch(event);\">\n";
				$output .= $t."\t\t\t<span class=\"roman\">".$row["roman"]."</span>\n";
				$output .= $t."\t\t\t<span class=\"native ".$this->enum["enum_languages"][$row["lang"]][0]."\">".$row["native"]."</span>\n";
				$output .= $t."\t\t</a>\n";
				$output .= $t."\t</li>\n";
			} else {
				break;
			}
		}
		if ($count > 0) {
			$output .= $t."</ul>\n";
			echo $output;
		} else {
			echo $none;
		}
	}
}
?>
