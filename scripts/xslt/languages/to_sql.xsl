<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE to_text [
	<!ENTITY tab	"&#09;">
	<!ENTITY nline	"&#10;">
]>
<!--
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
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
	<xsl:import href="ids.xsl"/>
	<xsl:output method="text" encoding="UTF-8" indent="no"/>
	
	<xsl:template match="dictionary">
		<xsl:text>SET NAMES &apos;utf8&apos;;&nline;</xsl:text>
		<xsl:text>INSERT INTO `enum_languages` (`value`, `human`) VALUES (&apos;</xsl:text><xsl:value-of select="@language"/><xsl:text>&apos;, &apos;</xsl:text><xsl:value-of select="@lang_human"/><xsl:text>&apos;);&nline;</xsl:text>
		<xsl:text>SET @lang := LAST_INSERT_ID();&nline;</xsl:text>
		<xsl:text>INSERT INTO `enum` SELECT &apos;enum_languages&apos; AS `table`, `key`, `value`, `human` FROM `enum_languages` WHERE `key` = @lang;&nline;</xsl:text>
		<xsl:text>&nline;</xsl:text>
		
		<xsl:apply-templates select="scripts"/>
		
		<xsl:text>INSERT INTO `intros` (`lang`, `content`) VALUES (&nline;</xsl:text>
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<xsl:text>&tab;&apos;</xsl:text><xsl:apply-templates select="intro"/><xsl:text>&apos;&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
		<xsl:text>&nline;</xsl:text>
		
		<xsl:apply-templates select="data/entry"/>
	</xsl:template>
	
	<xsl:template match="intro">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="scripts">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="script">
		<xsl:text>SET @script_</xsl:text><xsl:value-of select="@name"/><xsl:text> := null;&nline;</xsl:text>
		<xsl:text>SET @script_</xsl:text><xsl:value-of select="@name"/><xsl:text> := (SELECT `key` FROM `enum_scripts` WHERE `value` = &apos;</xsl:text><xsl:value-of select="@name"/><xsl:text>&apos;);&nline;</xsl:text>
		<xsl:text>DELIMITER $$&nline;</xsl:text> <!-- https://mariadb.com/kb/en/if/#comment_3844 -->
		<xsl:text>IF @script_</xsl:text><xsl:value-of select="@name"/><xsl:text> IS null THEN&nline;</xsl:text>
		<xsl:text>&tab;INSERT INTO `enum_scripts` (`value`, `human`) VALUES (&apos;</xsl:text><xsl:value-of select="@name"/><xsl:text>&apos;, &apos;</xsl:text><xsl:value-of select="@human"/><xsl:text>&apos;);&nline;</xsl:text>
		<xsl:text>&tab;SET @script_</xsl:text><xsl:value-of select="@name"/><xsl:text> := LAST_INSERT_ID();&nline;</xsl:text>
		<xsl:text>&tab;INSERT INTO `enum` SELECT &apos;enum_scripts&apos; AS `table`, `key`, `value`, `human` FROM `enum_scripts` WHERE `key` = @script_</xsl:text><xsl:value-of select="@name"/><xsl:text>;&nline;</xsl:text>
		<xsl:text>END IF $$&nline;</xsl:text>
		<xsl:text>DELIMITER ;&nline;</xsl:text>
		<xsl:text>&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="section">
		<xsl:text>INSERT INTO `script_data` (`lang`, `script`, `title`, `content`) VALUES (&nline;</xsl:text>
		<xsl:text>&tab;(SELECT @lang),</xsl:text>
		<xsl:text>&tab;(SELECT @script_</xsl:text><xsl:value-of select="../@name"/><xsl:text>),</xsl:text>
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:value-of select="@title"/>
		<xsl:text>&apos;,</xsl:text>
		<xsl:text>&tab;&apos;&lt;div class=&quot;dictionary-intro&quot;&gt;</xsl:text>
		<xsl:apply-templates select="intro"/>
		<xsl:text>&lt;/div&gt;</xsl:text>
		<xsl:apply-templates select="data"/>
		<xsl:text>&apos;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="data|symbols|marks">
		<xsl:choose>
			<xsl:when test="entry">
				<xsl:text>&lt;ol class=&quot;dictionary-list dictionary-</xsl:text>
				<xsl:value-of select="ancestor::section/@type"/>
				<xsl:if test=".//pronunciation">
					<xsl:text> has-pronunciations</xsl:text>
				</xsl:if>
				<xsl:text>&quot;&gt;</xsl:text>
				<xsl:choose>
					<xsl:when test="ancestor::section/@sort=&quot;yes&quot;">
						<xsl:apply-templates select="entry" mode="script_data">
							<xsl:sort select="@order" data-type="number"/>
						</xsl:apply-templates>
					</xsl:when>
					<xsl:otherwise>
						<xsl:apply-templates select="entry" mode="script_data"/>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:text>&lt;/ol&gt;</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="entry" mode="script_data">
		<xsl:text>&lt;li&gt;</xsl:text>
		<xsl:if test="glyph">
			<xsl:text>&lt;span class=&quot;dictionary-glyph </xsl:text><xsl:value-of select="ancestor::script/@name"/><xsl:text>&quot;&gt;</xsl:text>
			<xsl:apply-templates select="glyph" mode="script_data"/>
			<xsl:text>&lt;/span&gt;</xsl:text>
		</xsl:if>
		<xsl:text>&lt;span class=&quot;dictionary-list-label&quot;&gt;</xsl:text>
		<xsl:if test="not(pronunciation)">
			<xsl:text>&lt;a id=&quot;</xsl:text><xsl:value-of select="@id"/><xsl:text>&quot;&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="roman">
			<xsl:text>&lt;span class=&quot;dictionary-list-roman&quot;&gt;</xsl:text>
			<xsl:value-of select="roman"/>
			<xsl:text>&lt;/span&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="not(pronunciation)">
			<xsl:text>&lt;/a&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="phonemic">
			<xsl:text>&lt;span class=&quot;dictionary-list-phonemic&quot;&gt;/</xsl:text>
			<xsl:value-of select="phonemic"/>
			<xsl:text>/&lt;/span&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="pronunciation">
			<xsl:text>&lt;span class=&quot;dictionary-list-pronunciation&quot;&gt;</xsl:text>
			<xsl:text>&lt;a id=&quot;</xsl:text><xsl:value-of select="@id"/><xsl:text>&quot;&gt;</xsl:text>
			<xsl:if test="pronunciation/roman">
				<xsl:text>&lt;span class=&quot;dictionary-list-roman&quot;&gt;</xsl:text>
				<xsl:value-of select="pronunciation/roman"/>
				<xsl:text>&lt;/span&gt;</xsl:text>
			</xsl:if>
			<xsl:text>&lt;/a&gt;</xsl:text>
			<xsl:if test="pronunciation/phonemic">
				<xsl:text>&lt;span class=&quot;dictionary-list-phonemic&quot;&gt;/</xsl:text>
				<xsl:value-of select="pronunciation/phonemic"/>
				<xsl:text>/&lt;/span&gt;</xsl:text>
			</xsl:if>
			<xsl:if test="pronunciation/*[name()=ancestor::script/@name]">
				<xsl:text>&lt;span class=&quot;dictionary-list-native </xsl:text><xsl:value-of select="ancestor::script/@name"/><xsl:text>&quot;&gt;</xsl:text>
				<xsl:value-of select="pronunciation/*[name()=ancestor::script/@name]"/>
				<xsl:text>&lt;/span&gt;</xsl:text>
			</xsl:if>
			<xsl:text>&lt;/span&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="note">
			<xsl:text>&lt;span class=&quot;note&quot;&gt;</xsl:text>
			<xsl:value-of select="note"/>
			<xsl:text>&lt;/span&gt;</xsl:text>
		</xsl:if>
		<xsl:text>&lt;/span&gt;</xsl:text>
		<xsl:text>&lt;/li&gt;</xsl:text>
	</xsl:template>
	<xsl:template match="glyph" mode="script_data">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="base" mode="script_data">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="entry">
		<xsl:text>INSERT INTO `entries` (`lang`, `entry_id`, `etym`) VALUES (&nline;</xsl:text>
		<!-- `lang` -->
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<!-- `entry_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `etym` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="etym"/>
		<xsl:text>&apos;&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
		
		<xsl:text>&nline;</xsl:text>
		<xsl:apply-templates select="class"/>
		
		<!-- `primary_morph` -->
		<xsl:text>UPDATE `entries` SET `primary_morph` = </xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="class/morph/form[@primary=&quot;yes&quot;]/@id"/></xsl:call-template>
		<xsl:text> WHERE `entry_id` = </xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="@id"/></xsl:call-template>
		<xsl:text>;&nline;</xsl:text>
		
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="class">
		<xsl:text>INSERT INTO `classes` VALUES (&nline;</xsl:text>
		<!-- `lang` -->
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<!-- `entry_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_type` -->
		<xsl:text>&tab;(SELECT `key` FROM `enum_classes` WHERE `value` = &apos;</xsl:text>
		<xsl:value-of select="@type"/>
		<xsl:text>&apos;),&nline;</xsl:text>
		<!-- `priority` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:number select="position()"/>
		<xsl:text>&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
		
		<xsl:text>&nline;</xsl:text>
		<xsl:apply-templates select="def/sense"/>
		<xsl:text>&nline;</xsl:text>
		<xsl:apply-templates select="morph/form"/>
	</xsl:template>
	
	<xsl:template match="sense">
		<xsl:text>INSERT INTO `senses` (`lang`, `entry_id`, `class_id`, `num`, `p`, `use`) VALUES (&nline;</xsl:text>
		<!-- `lang` -->
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<!-- `entry_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `num` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:value-of select="@num"/>
		<xsl:text>,&nline;</xsl:text>
		<!-- `p` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="p" mode="sense"/>
		<xsl:text>&apos;,&nline;</xsl:text>
		<!-- `use` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:value-of select="p/@use"/>
		<xsl:text>&apos;&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
		
		<xsl:text>SET @sense_id := LAST_INSERT_ID();&nline;</xsl:text>
		<xsl:apply-templates select="subsense"/>
	</xsl:template>
	
	<xsl:template match="subsense">
		<xsl:text>INSERT INTO `subsenses` (`lang`, `entry_id`, `class_id`, `sense_id`, `num`, `p`, `use`) VALUES (&nline;</xsl:text>
		<!-- `lang` -->
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<!-- `entry_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `sense_id` -->
		<xsl:text>&tab;(SELECT @sense_id),&nline;</xsl:text>
		<!-- `num` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:number select="position()"/>
		<xsl:text>,&nline;</xsl:text>
		<!-- `p` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="p" mode="sense"/>
		<xsl:text>&apos;,&nline;</xsl:text>
		<!-- `use` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:value-of select="p/@use"/>
		<xsl:text>&apos;&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="form">
		<xsl:text>INSERT INTO `morph` (`lang`, `script`, `entry_id`, `class_id`, `class_type`, `morph_id`, `native`, `roman`, `phonemic`</xsl:text>
		<xsl:if test="@definiteness"><xsl:text>, `definiteness`</xsl:text></xsl:if>
		<xsl:if test="@number">      <xsl:text>, `number`</xsl:text>      </xsl:if>
		<xsl:if test="@case">        <xsl:text>, `case`</xsl:text>        </xsl:if>
		<!--<xsl:if test="@phase">       <xsl:text>, `phase`</xsl:text>       </xsl:if>-->
		<xsl:if test="@role">        <xsl:text>, `verb_role`</xsl:text>   </xsl:if>
		<xsl:if test="@tense">       <xsl:text>, `tense`</xsl:text>       </xsl:if>
		<xsl:if test="@aspect">      <xsl:text>, `aspect`</xsl:text>      </xsl:if>
		<xsl:if test="@person">      <xsl:text>, `person`</xsl:text>      </xsl:if>
		<xsl:if test="@voice">       <xsl:text>, `voice`</xsl:text>       </xsl:if>
		<xsl:text>, `irregular`) VALUES (&nline;</xsl:text>
		<!-- `lang` -->
		<xsl:text>&tab;(SELECT @lang),&nline;</xsl:text>
		<!-- `script` -->
		<xsl:text>&tab;(SELECT @script_</xsl:text><xsl:value-of select="/dictionary//script[@primary=&quot;yes&quot;]/@name"/><xsl:text>),&nline;</xsl:text>
		<!-- `entry_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="../../@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `class_type` -->
		<xsl:text>&tab;(SELECT `key` FROM `enum_classes` WHERE `value` = &apos;</xsl:text>
		<xsl:value-of select="../../@type"/>
		<xsl:text>&apos;),&nline;</xsl:text>
		<!-- `morph_id` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:call-template name="id_decode"><xsl:with-param name="input" select="@id"/></xsl:call-template>
		<xsl:text>,&nline;</xsl:text>
		<!-- `native` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="*[name()=/dictionary//script[@primary=&quot;yes&quot;]/@name]"/>
		<xsl:text>&apos;,&nline;</xsl:text>
		<!-- `roman` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="roman"/>
		<xsl:text>&apos;,&nline;</xsl:text>
		<!-- `phonemic` -->
		<xsl:text>&tab;&apos;</xsl:text>
		<xsl:apply-templates select="phonemic"/>
		<xsl:text>&apos;,&nline;</xsl:text>
		<!-- `definiteness` -->
		<xsl:if test="@definiteness">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_definiteness` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@definiteness"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `number` -->
		<xsl:if test="@number">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_number` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@number"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `case` -->
		<xsl:if test="@case">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_case` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@case"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `phase` -->
		<!--<xsl:if test="@phase">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_phase` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@phase"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>-->
		<!-- `verb_role` -->
		<xsl:if test="@role">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_verb_role` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@role"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `tense` -->
		<xsl:if test="@tense">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_tense` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@tense"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `aspect` -->
		<xsl:if test="@aspect">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_aspect` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@aspect"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `person` -->
		<xsl:if test="@person">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_person` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@person"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `voice` -->
		<xsl:if test="@voice">
			<xsl:text>&tab;(SELECT `key` FROM `inflect_voice` WHERE `value` = &apos;</xsl:text>
			<xsl:value-of select="@voice"/>
			<xsl:text>&apos;),&nline;</xsl:text>
		</xsl:if>
		<!-- `irregular` -->
		<xsl:text>&tab;</xsl:text>
		<xsl:choose>
			<xsl:when test="@irregular and @irregular=&quot;yes&quot;">
				<xsl:number value="1"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:number value="0"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>&nline;</xsl:text>
		<xsl:text>);&nline;</xsl:text>
		
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="*[name()=/dictionary//script/@name]">
		<xsl:text>&lt;span class=&quot;</xsl:text><xsl:value-of select="name()"/><xsl:text>&quot;&gt;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&lt;/span&gt;</xsl:text>
	</xsl:template>
	<xsl:template match="word">
		<xsl:text>&lt;a class=&quot;languages-word&quot; href=&quot;?id=</xsl:text>
		<xsl:value-of select="@href"/>
		<xsl:text>&quot;&gt;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&lt;/a&gt;</xsl:text>
	</xsl:template>
	<xsl:template match="etym"><xsl:apply-templates/></xsl:template>
	<xsl:template match="div"><xsl:text>&lt;div&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/div&gt;</xsl:text></xsl:template>
	<xsl:template match="p"><xsl:text>&lt;p&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/p&gt;</xsl:text></xsl:template>
	<xsl:template match="p" mode="sense"><xsl:apply-templates/></xsl:template>
	<xsl:template match="a">
		<xsl:text>&lt;a</xsl:text>
		<xsl:if test="@href">
			<xsl:text> href=&quot;</xsl:text><xsl:value-of select="@href"/><xsl:text>&quot;</xsl:text>
		</xsl:if>
		<xsl:text>&gt;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&lt;/a&gt;</xsl:text>
	</xsl:template>
	<xsl:template match="i"><xsl:text>&lt;i&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/i&gt;</xsl:text></xsl:template>
	<xsl:template match="c"><xsl:text>&lt;span class=&quot;languages-smallcaps&quot;&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/span&gt;</xsl:text></xsl:template>
	<xsl:template match="sup"><xsl:text>&lt;sup&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/sup&gt;</xsl:text></xsl:template>
	<xsl:template match="sub"><xsl:text>&lt;sub&gt;</xsl:text><xsl:apply-templates/><xsl:text>&lt;/sub&gt;</xsl:text></xsl:template>
	
	<!-- output sanitation -->
	<xsl:template match="text()">
		<xsl:call-template name="clean">
			<xsl:with-param name="input" select="."/>
		</xsl:call-template>
	</xsl:template>
	<xsl:template name="clean">
		<xsl:param name="input"/>
		<xsl:variable name="char" select="substring($input,1,1)" />
		<xsl:variable name="remains" select="substring($input,2)" />
		<xsl:choose>
			<xsl:when test="string($char) = string(&quot;&apos;&quot;)">
				<xsl:text>&apos;&apos;</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$char"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="string-length($remains) &gt; 0">
			<xsl:call-template name="clean">
				<xsl:with-param name="input" select="$remains"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
