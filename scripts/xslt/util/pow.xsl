<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
	<xsl:output method="text" encoding="UTF-8" indent="no"/>
	
	<!-- https://openwritings.net/pg/xslt/function-raise-power-xslt -->
	<xsl:template name="Pow">
		<xsl:param name="Base"/>
		<xsl:param name="Exponent"/>
		<xsl:param name="Result" select="1"/>
		<xsl:choose>
			<xsl:when test="$Exponent &lt; 0">
				<xsl:value-of select="0"/>
			</xsl:when>
			<xsl:when test="$Exponent = 0">
				<xsl:value-of select="1"/>
			</xsl:when>
			<xsl:when test="$Exponent = 1">
				<xsl:value-of select="$Result * $Base"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="Pow">
					<xsl:with-param name="Base" select="$Base"/>
					<xsl:with-param name="Exponent" select="$Exponent - 1"/>
					<xsl:with-param name="Result" select="$Result * $Base"/>
				</xsl:call-template>                          
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>
