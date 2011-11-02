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

if ( $_FILES["report"] != null  )
{
	if ( file_exists( $_FILES['report']['tmp_name'] ) )
	{
		$uploadfile = realpath($GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" )) . "/" . basename($_FILES['report']['name']); 
		if ( file_exists( $GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" ) ) )
		{
			if ( file_exists( $uploadfile ) )
			{	
				unlink( $uploadfile );		
			}
			
			if ( endsWith( $uploadfile, ".php" ) )
			{
				if ( move_uploaded_file($_FILES['report']['tmp_name'], $uploadfile) )					
				{				
					msgBox( _("Your report has been added!") );
				}
				else
				{
					msgBox( _("Unable to move uploaded file.") . $uploadfile );
				}	
			}			
			else
				msgBox( _("File does not look like a php source file") );
		}
		else
		{
			msgBox( _("Unable to locate ") .  $GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" ) );
		}
	}
}


?>
<h2><?php print _("Upload FOG Reports"); ?></h2>
<div class="hostgroup">
	<?php echo(_("This section allows you to upload user defined reports that may not be part of the base FOG package.  The report files should end in .php.")); ?>  
</div>	

<p class="titleBottomLeft"><?php echo(_("Upload a FOG Report")); ?></p>
<form method="post" action="<?php echo( "?node=$_GET[node]&sub=$_GET[sub]");?>" enctype="multipart/form-data">
	<input type="file" name="report" value="" /> <span class="lightColor"> <?php echo(_("Max Size").": ".ini_get("post_max_size")); ?></span>
	<p><input type="submit" value="<?php echo(_("Upload File")); ?>" /></p>
</form>