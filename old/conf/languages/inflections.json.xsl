<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE to_text [
	<!ENTITY tab   "&#09;">
	<!ENTITY nline "&#10;">
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
	<xsl:output method="text" encoding="UTF-8" indent="no"/>
	
	<xsl:template match="config">
		<xsl:text>[&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>]&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="language">
		<xsl:text>&tab;{&nline;</xsl:text>
		<xsl:text>&tab;&tab;"language": "</xsl:text><xsl:value-of select="@name"/><xsl:text>",&nline;</xsl:text>
		<xsl:text>&tab;&tab;"groups": [&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&tab;&tab;]&nline;</xsl:text>
		<xsl:text>&tab;}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="group">
		<xsl:text>&tab;&tab;&tab;{&nline;</xsl:text>
		<xsl:apply-templates select="list"/>
		<xsl:apply-templates select="layout"/>
		<xsl:text>&tab;&tab;&tab;}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="list">
		<xsl:text>&tab;&tab;&tab;&tab;"classes": [&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&tab;&tab;&tab;&tab;]</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="class">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;{</xsl:text>
		<xsl:text>"name": "</xsl:text><xsl:value-of select="@name"/><xsl:text>",</xsl:text>
		<xsl:text> "inflected": </xsl:text>
		<xsl:choose>
			<xsl:when test="@inflected=&quot;yes&quot;">
				<xsl:text>true</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>false</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="layout">
		<xsl:text>&tab;&tab;&tab;&tab;"tables": [&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&tab;&tab;&tab;&tab;]&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="table">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;{&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"label": "</xsl:text><xsl:value-of select="@label"/><xsl:text>",&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"stack": </xsl:text>
		<xsl:choose>
			<xsl:when test="@stack=&quot;yes&quot;">
				<xsl:text>true</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>false</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>,&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"align_on_stack": </xsl:text>
		<xsl:choose>
			<xsl:when test="@align_on_stack=&quot;yes&quot;">
				<xsl:text>true</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>false</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>,&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"fold_table": </xsl:text>
		<xsl:choose>
			<xsl:when test="@fold=&quot;yes&quot;">
				<xsl:text>true</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>false</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>,&nline;</xsl:text>
		<xsl:apply-templates mode="table"/>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="filter" mode="table">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"filter": [&nline;</xsl:text>
		<xsl:apply-templates mode="table"/>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;]</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="inflect" mode="table">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;{</xsl:text>
		<xsl:text>"dimension": "</xsl:text><xsl:value-of select="@dimension"/><xsl:text>",</xsl:text>
		<xsl:text> "value": "</xsl:text><xsl:value-of select="@value"/><xsl:text>"</xsl:text>
		<xsl:text>}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="rows" mode="table">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"fold_rows": </xsl:text>
		<xsl:choose>
			<xsl:when test="@fold=&quot;yes&quot;">
				<xsl:text>true</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>false</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>,&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;"rows": [&nline;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;]</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="row">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;{&nline;</xsl:text>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;"label": "</xsl:text><xsl:value-of select="@label"/><xsl:text>",&nline;</xsl:text>
		<xsl:if test="@brief">
			<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;"brief": "</xsl:text><xsl:value-of select="@brief"/><xsl:text>",&nline;</xsl:text>
		</xsl:if>
		<xsl:if test="@long">
			<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;"long": "</xsl:text><xsl:value-of select="@long"/><xsl:text>",&nline;</xsl:text>
		</xsl:if>
		<xsl:apply-templates mode="row"/>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="filter" mode="row">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;"filter": [&nline;</xsl:text>
		<xsl:apply-templates mode="row"/>
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;]</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	<xsl:template match="inflect" mode="row">
		<xsl:text>&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;&tab;{</xsl:text>
		<xsl:text>"dimension": "</xsl:text><xsl:value-of select="@dimension"/><xsl:text>",</xsl:text>
		<xsl:text> "value": "</xsl:text><xsl:value-of select="@value"/><xsl:text>"</xsl:text>
		<xsl:text>}</xsl:text>
		<xsl:if test="following-sibling::*"><xsl:text>,</xsl:text></xsl:if>
		<xsl:text>&nline;</xsl:text>
	</xsl:template>
	
	<xsl:template match="text()"/>
	<xsl:template match="text()" mode="table"/>
	<xsl:template match="text()" mode="row"/>
	
</xsl:stylesheet>
