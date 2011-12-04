<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */

//@ini_set( "max_execution_time", 120 );
 
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

?>
<h2><?php print _("MAC Address Manufacturer Listing"); ?></h2>
<?php

if ( $_GET["update"] == "1" )
{
	$f = "./other/oui.txt";
	if ( file_exists($f) )
	{
		$handle = fopen($f, "r");
		$start = 18;
		$imported = 0;
		while (!feof($handle)) 
		{
			$line = trim(fgets($handle));
			if ( preg_match( "#^([0-9a-fA-F][0-9a-fA-F][:-]){2}([0-9a-fA-F][0-9a-fA-F]).*$#", $line ) )
			{
				
				$macprefix = substr( $line, 0, 8 );					
				$maker = substr( $line, $start, strlen( $line ) - $start );
				try
				{
					if ( strlen(trim( $macprefix ) ) == 8 && strlen($maker) > 0 )
					{
						if ( $FOGCore->addUpdateMACLookupTable( $macprefix, $maker ) )
							$imported++;
					}
				}
				catch ( Exception $e )
				{
					echo ( $e->getMessage() . "<br />" );
				}
				
			}
		}
		fclose($handle);
		
		msgBox( $imported . _(" mac addresses updated!") );
	}
	else
	{
		msgBox( _("Unable to locate file: $f") );
	}
}
else if ( $_GET["clear"] == "1" )
{
	$FOGCore->clearMACLookupTable();
}

?>
<div class="hostgroup">
	<?php
	echo(_("This section allows you to import known mac address makers into the FOG database for easier identification."));
	?>
</div>

<div>
	<p>
	<?php echo(_("Current Records: ").$FOGCore->getMACLookupCount()); ?></p>
	
	<p>
		<input type="button" id="delete" value="<?php echo(_("Delete Current Records")); ?>" onclick="clearMacs();" />  <input style='margin-left: 20px' type="button" id="update" value="<?php echo(_("Update Current Listing")); ?>" onclick="updateMacs();" />
	</p>
	<br /><br />	
	<p>
	<?php echo(_("MAC address listing source: ")); ?><a href="http://standards.ieee.org/regauth/oui/oui.txt">http://standards.ieee.org/regauth/oui/oui.txt</a>
	</p>
</div>