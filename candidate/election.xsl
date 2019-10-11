<?xml version="1.0" encoding="UTF-8" ?>
<!--
   Author: Joseph Saad
   Date: 28/08/2019 

   New Perspectives on XML, 3rd Edition
   Tutorial 6
   Case Problem 1

   Voter Web Style Sheet
  
   Filename:         election.xsl
   Supporting Files: 
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://example.com/candidates" version="1.0">
	<xsl:output method="html" doctype-system="about:legacy-compat" encoding="UTF-8" indent="yes"/>
	<xsl:variable name="candidateInfo" select="document('candidates.xml')/candidates/candidate"/>
	<xsl:template match="/">
		<html>
			<head>
				<title>Minnesota Congressional Election Results</title>
				<link href="vwstyles.css" rel="stylesheet" type="text/css"/>
			</head>
			<body>
				<div id="wrap">
					<header>			<img src="vwlogo.png" alt="Voter Web"/>			</header>
					<h1>Minnesota Congressional Election Results</h1>
					<section id="votingResults">
						<xsl:apply-templates select="congressResults/district"/>
					</section>
				</div>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="district">
		<h2>
			District
			<xsl:value-of select="@dNumber"/>
		</h2>
		<table class="electionTable">
		<thead>
		<tr>
		<th>Candidate</th>
		<th>Votes</th>
		</tr>
		</thead>
		<tbody>
		<xsl:apply-templates select="candidates/candidate"/>
		</tbody>
		</table>
	</xsl:template>
	<xsl:template match="candidate">
		<xsl:variable name="candidateVotes" select="sum(votes)"/>
		<xsl:variable name="totalVotes" select="sum(..//votes)"/>
		<xsl:variable name="candidatePercent" select="($candidateVotes div $totalVotes)"/>
		<xsl:variable name="candidateName" select="$candidateInfo[@candidateID=current()/@candidateID]/name"/>
		<xsl:variable name="candidateParty" select="$candidateInfo[@candidateID=current()/@candidateID]/party"/>
		<tr>
			<th>
			<xsl:value-of select="$candidateName"/>
			(
			<xsl:value-of select="$candidateParty"/>
			)
			</th>
			<th>
			<xsl:value-of select="format-number($candidateVotes, '###,##0')"/>
			(
			<xsl:value-of select="format-number($candidatePercent, '#0.0%')"/>
			)
			</th>
			<xsl:call-template name="drawCells">
				<xsl:with-param name="cellCount" select="round($candidatePercent * 100)"/>
				<xsl:with-param name="party" select="$candidateParty"/>
			</xsl:call-template>
		</tr>
	</xsl:template>
	<xsl:template name="drawCells">
		<xsl:param name="cellCount"/>
		<xsl:param name="party"/>
		<xsl:if test="$cellCount > 0">
			<td class="{$party}"/>
		<xsl:call-template name="drawCells">
			<xsl:with-param name="cellCount" select="$cellCount - 1"/>
			<xsl:with-param name="party" select="$party"/>
		</xsl:call-template>
		</xsl:if>
</xsl:template>
</xsl:stylesheet>