<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2008  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
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

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

?>
<h2><?php print _("Select a module you would like to Activate"); ?></h2>
<?php

if ( $_GET["activate"] != null )
{
	if ( $pm->activatePlugin( $_GET["activate"] ) )
	{
		msgBox( _("Module activated!") );
	}
	else
	{
		msgBox( _("Failed to activate module!") );
	}
}

$allPlugs = $pm->getallPlugins();
if ( $allPlugs != null && count( $allPlugs ) > 0 )
{
	echo ( "<div id=\"pluginContainer\">" );
	$cntActive = 0;
	for( $i = 0; $i < count( $allPlugs ); $i++ )
	{
		if ( $allPlugs[$i] != null )
		{
			if ( ! $allPlugs[$i]->isActive() )
			{
				$cntActive++;
				echo ( "<div>" );
					echo ( "<a href=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&activate=" . md5( $allPlugs[$i]->getName() ) . "\">" );			
						echo ( "<img src=\"" . $allPlugs[$i]->getPath() . "/" . $allPlugs[$i]->getIcon() . "\" />" );		
						echo ( $allPlugs[$i]->getName() );
					echo ( "</a>" );				
				echo ( "</div>" );
			}
		}
	}
	if ( $cntActive == 0 )
	{
		echo ( "<p>"._("No inactive modules found.")."</p>" );
	}	
	echo ( "</div>" );
}
else
{
	echo ( "<p>"._("No plugins found.")."</p>" );
}