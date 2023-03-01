<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
	<xsl:import href="../util/pow.xsl"/>
	<xsl:output method="text" encoding="UTF-8" indent="no"/>
	
	<!-- decode hexadecimal IDs -->
	<xsl:template name="id_decode">
		<xsl:param name="input"/>
		<xsl:param name="total" select="0"/>
		<xsl:variable name="char" select="substring($input,1,1)"/>
		<xsl:variable name="remaining" select="substring($input,2)"/>
		
		<!-- find value of digit -->
		<xsl:variable name="digit">
			<xsl:choose>
				<xsl:when test="$char = 'A'"><xsl:number value="0"/></xsl:when>
				<xsl:when test="$char = 'B'"><xsl:number value="1"/></xsl:when>
				<xsl:when test="$char = 'C'"><xsl:number value="2"/></xsl:when>
				<xsl:when test="$char = 'D'"><xsl:number value="3"/></xsl:when>
				<xsl:when test="$char = 'E'"><xsl:number value="4"/></xsl:when>
				<xsl:when test="$char = 'F'"><xsl:number value="5"/></xsl:when>
				<xsl:when test="$char = 'G'"><xsl:number value="6"/></xsl:when>
				<xsl:when test="$char = 'H'"><xsl:number value="7"/></xsl:when>
				<xsl:when test="$char = 'I'"><xsl:number value="8"/></xsl:when>
				<xsl:when test="$char = 'J'"><xsl:number value="9"/></xsl:when>
				<xsl:when test="$char = 'K'"><xsl:number value="10"/></xsl:when>
				<xsl:when test="$char = 'L'"><xsl:number value="11"/></xsl:when>
				<xsl:when test="$char = 'M'"><xsl:number value="12"/></xsl:when>
				<xsl:when test="$char = 'N'"><xsl:number value="13"/></xsl:when>
				<xsl:when test="$char = 'O'"><xsl:number value="14"/></xsl:when>
				<xsl:when test="$char = 'P'"><xsl:number value="15"/></xsl:when>
				
				<xsl:when test="$char = 'Q'"><xsl:number value="16"/></xsl:when>
				<xsl:when test="$char = 'R'"><xsl:number value="17"/></xsl:when>
				<xsl:when test="$char = 'S'"><xsl:number value="18"/></xsl:when>
				<xsl:when test="$char = 'T'"><xsl:number value="19"/></xsl:when>
				<xsl:when test="$char = 'U'"><xsl:number value="20"/></xsl:when>
				<xsl:when test="$char = 'V'"><xsl:number value="21"/></xsl:when>
				<xsl:when test="$char = 'W'"><xsl:number value="22"/></xsl:when>
				<xsl:when test="$char = 'X'"><xsl:number value="23"/></xsl:when>
				<xsl:when test="$char = 'Y'"><xsl:number value="24"/></xsl:when>
				<xsl:when test="$char = 'Z'"><xsl:number value="25"/></xsl:when>
				<xsl:when test="$char = 'a'"><xsl:number value="26"/></xsl:when>
				<xsl:when test="$char = 'b'"><xsl:number value="27"/></xsl:when>
				<xsl:when test="$char = 'c'"><xsl:number value="28"/></xsl:when>
				<xsl:when test="$char = 'd'"><xsl:number value="29"/></xsl:when>
				<xsl:when test="$char = 'e'"><xsl:number value="30"/></xsl:when>
				<xsl:when test="$char = 'f'"><xsl:number value="31"/></xsl:when>
				
				<xsl:when test="$char = 'g'"><xsl:number value="32"/></xsl:when>
				<xsl:when test="$char = 'h'"><xsl:number value="33"/></xsl:when>
				<xsl:when test="$char = 'i'"><xsl:number value="34"/></xsl:when>
				<xsl:when test="$char = 'j'"><xsl:number value="35"/></xsl:when>
				<xsl:when test="$char = 'k'"><xsl:number value="36"/></xsl:when>
				<xsl:when test="$char = 'l'"><xsl:number value="37"/></xsl:when>
				<xsl:when test="$char = 'm'"><xsl:number value="38"/></xsl:when>
				<xsl:when test="$char = 'n'"><xsl:number value="39"/></xsl:when>
				<xsl:when test="$char = 'o'"><xsl:number value="40"/></xsl:when>
				<xsl:when test="$char = 'p'"><xsl:number value="41"/></xsl:when>
				<xsl:when test="$char = 'q'"><xsl:number value="42"/></xsl:when>
				<xsl:when test="$char = 'r'"><xsl:number value="43"/></xsl:when>
				<xsl:when test="$char = 's'"><xsl:number value="44"/></xsl:when>
				<xsl:when test="$char = 't'"><xsl:number value="45"/></xsl:when>
				<xsl:when test="$char = 'u'"><xsl:number value="46"/></xsl:when>
				<xsl:when test="$char = 'v'"><xsl:number value="47"/></xsl:when>
				
				<xsl:when test="$char = 'w'"><xsl:number value="48"/></xsl:when>
				<xsl:when test="$char = 'x'"><xsl:number value="49"/></xsl:when>
				<xsl:when test="$char = 'y'"><xsl:number value="50"/></xsl:when>
				<xsl:when test="$char = 'z'"><xsl:number value="51"/></xsl:when>
				<xsl:when test="$char = '0'"><xsl:number value="52"/></xsl:when>
				<xsl:when test="$char = '1'"><xsl:number value="53"/></xsl:when>
				<xsl:when test="$char = '2'"><xsl:number value="54"/></xsl:when>
				<xsl:when test="$char = '3'"><xsl:number value="55"/></xsl:when>
				<xsl:when test="$char = '4'"><xsl:number value="56"/></xsl:when>
				<xsl:when test="$char = '5'"><xsl:number value="57"/></xsl:when>
				<xsl:when test="$char = '6'"><xsl:number value="58"/></xsl:when>
				<xsl:when test="$char = '7'"><xsl:number value="59"/></xsl:when>
				<xsl:when test="$char = '8'"><xsl:number value="60"/></xsl:when>
				<xsl:when test="$char = '9'"><xsl:number value="61"/></xsl:when>
				<xsl:when test="$char = '-'"><xsl:number value="62"/></xsl:when>
				<xsl:when test="$char = '_'"><xsl:number value="63"/></xsl:when>
				
				<xsl:otherwise><xsl:number value="0"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<!-- find place value -->
		<xsl:variable name="place">
			<xsl:choose>
				<xsl:when test="string-length($input)=0">
					<xsl:number value="0"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="Pow">
						<xsl:with-param name="Base" select="64"/>
						<xsl:with-param name="Exponent" select="string-length($input) - 1"/>
					</xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<!-- multiply digit value by place value to find quantity -->
		<xsl:variable name="quantity" select="$digit * $place"/>
		
		<!-- recurse until digits are consumed, output cumulative quantity -->
		<xsl:choose>
			<xsl:when test="string-length($input)=0">
				<xsl:number value="0"/>
			</xsl:when>
			<xsl:when test="string-length($remaining) &gt; 0">
				<xsl:call-template name="id_decode">
					<xsl:with-param name="input" select="$remaining"/>
					<xsl:with-param name="total" select="$total + $quantity"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:number value="$total + $quantity"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
