<?xml version="1.0" encoding="UTF-8"?>
<!-- 
In this file you have direct access to the content of meter_readings.xml.
To get access to content form customers.xml,, use the document() function to load customers.xml.
If you store this in a variable, you will then have access to the content of customers.xml by using the varible anywhere in this xsl file.
For how to use the document() function in xsl, see lecture3 and w3schools
https://www.w3schools.com/xml/func_document.asp
 -->
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" indent="yes" />
	<xsl:template match="/">
			<xsl:apply-templates select="meterreadings/meterreading" />
	</xsl:template>

	<xsl:template match="meterreading">

	<xsl:variable name="Num" select="number"/>
	<xsl:variable name="Date" select="@date" />
	<xsl:variable name="Read" select="reading"/>
	
		 <xsl:for-each select="document('customers.xml')/customers/customer[meternumber=$Num]">

		 <div class="main">
		 <h1>Bills:</h1>
		 <table>
		 	<tr>
				<td style="vertical-align: top;">
					<h2>Customer information:</h2>
					<table border="1">
						<tr>
							<td style="color:blue;">Name</td>
							<td><xsl:value-of select="name"/></td>
						</tr>
						<tr>
							<td style="color:blue;" >Address</td>
							<td><xsl:value-of select="address"/></td>
						</tr>
						<tr>
							<td style="color:blue;" >Number</td>
							<td><xsl:value-of select="@number"/></td>
						</tr>
					</table>
					
					<h2>Account Summary:<xsl:value-of select="payment[last()]/@date"/> to <xsl:value-of select="$Date"/> - 92 days</h2>
					<table border="1">
						<tr>
							<th>Meter Number</th>
							<th>This Read</th>
							<th>Previous Read</th>
							<th>Usage (kWh)</th>
						</tr>
						<tr>
							<td><xsl:value-of select="$Num"/></td>
							<td><xsl:value-of select="$Read"/></td>
							<td><xsl:value-of select="payment[last()]/reading"/></td>
							<td><xsl:value-of select="$Read - payment[last()]/reading"/></td>
						</tr>
					</table>
					<h2>Bill Summary</h2>
					<table border="1">
						<tr>
							<th>Due Date</th>
							<th>Amount Due:</th>
						</tr>
						<tr>
							<td>29/12/2018</td>
							<td><xsl:value-of select="(payment[last()]/amountdue - payment[last()]/paid) + (((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124)) * 0.10) + 
							((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124))"/></td>
						</tr>
					</table>
				</td>
				<td style="vertical-align: bottom; padding-left:20px;">
					<h2>Account Calculations: </h2>
					<table border="1">
					<tr>
						<th></th>
						<th>Use</th>
						<th>Rate</th>
						<th>Total</th>
					</tr>
					<tr>
						<td><b>Usage:</b></td>
						<td><xsl:value-of select="$Read - payment[last()]/reading"/></td>
						<td>$0.124</td>
						<td><xsl:value-of select="($Read - payment[last()]/reading) * 0.124"/></td>
					</tr>
					<tr>
						<td><b>System Access Charge:</b></td>
						<td>92 days</td>
						<td>$0.373</td>
						<td><xsl:value-of select="92 * 0.373"/></td>
					</tr>
					<tr>
						<td><b>Total(excl. GST):</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="(92 * 0.373) + (($Read - payment[last()]/reading) * 0.124)"/></td>
					</tr>
					<tr>
						<td><b>GST payable:</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124)) * 0.10"/></td>
					</tr>
					<tr>
						<td><b>Total:</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="(((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124)) * 0.10) + ((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124))"/></td>
					</tr>
					
					<tr>
						<td ><b>Balance of Last Bill :</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="payment[last()]/amountdue"/></td>
					</tr>
					<tr>
						<td ><b>Less Payments:</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="payment[last()]/paid"/></td>
					</tr>
					<tr>
						<td ><b>Total Due:</b></td>
						<td></td>
						<td></td>
						<td><xsl:value-of select="(payment[last()]/amountdue - payment[last()]/paid) + (((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124)) * 0.10) + 
						((92 * 0.373) + (($Read - payment[last()]/reading) * 0.124))"/></td>
					</tr>
					</table>
				
				</td>
			</tr>
			</table>	
				
			</div>
    </xsl:for-each>
			
	</xsl:template>

	<xsl:template match="customer">
		<p><xsl:value-of select="meternumber"/></p>
	</xsl:template>
	
</xsl:stylesheet>