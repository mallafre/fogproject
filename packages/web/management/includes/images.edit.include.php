<?php

// Blackout - 4:34 PM 23/09/2011
if ( IS_INCLUDED !== true ) die( _('Unable to load system configuration information.') );

$storagedir = '['._('StorageNodeRootDir').']';

if ( $_GET["rmimageid"] != null && is_numeric( $_GET["rmimageid"] ) )
{
	/*
	// Load Image
	$image = new Image($_GET["rmimageid"]);
	
	// Remove Image
	$image->destroy();
	*/

	$rmid = mysql_real_escape_string( $_GET["rmimageid"] );
	
	$sql = "SELECT imagestorageGroupID FROM images WHERE imageID = '$rmid'";
	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	$nfsGroup = -1;
	while( $ar = mysql_fetch_array( $res ) )
	{
		$nfsGroup = $ar["imagestorageGroupID"];
	}
	$tmpSD = getStorageRootByGroupID( $conn, $nfsGroup );
	if ( $tmpSD != null )
		$storagedir = $tmpSD;	
	
	if ( $_GET["confirm"] != "1" )
	{
		$sql = "select * from images where imageID = '$rmid'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			?>
			<h2><?php print _("Confirm Image Removal"); ?></h2>
			<?php
			echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("Image Name").":</td><td>" . $ar["imageName"] . "</td></tr>" );
				echo ( "<tr><td>"._("Image Description").":</td><td>" . $ar["imageDesc"] . "</td></tr>" );
				echo ( "<tr><td>"._("Image File").":</td><td>" . $storagedir . $ar["imagePath"] . "</td></tr>" );
				echo ( "<tr><td colspan=2><center><form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&rmimageid=$_GET[rmimageid]&confirm=1\"><input type=\"submit\" value=\""._("Delete only the image definition.")."\" /></form><br /><form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&rmimageid=$_GET[rmimageid]&confirm=1&killfile=1\"><input type=\"submit\" value=\""._("Delete image definition, and image file.")."\" /></form></center></td></tr>" );				
			echo ( "</table></center>" );		
		}
	}
	else
	{
		$output = "";
		?>
		<h2><?php print _("Image Removal Results"); ?></h2>
		<?php
		if ( $_GET["killfile"] == "1" )
		{
			$sql = "select imagePath, imageDD from images where imageID = '" . $rmid . "'";
			$res = mysql_query( $sql, $conn ) or die( mysql_error() );
			$file = null;
			$imageType = null;
			while( $ar = mysql_fetch_array( $res ) )
			{
				$file = $ar["imagePath"];
				$imageType = $ar["imageDD"];
			}
			
			if ( $file !== null )
			{
				if ( ftpDeleteImageDir($conn, $rmid) )
				{
					$output .= _("Image file has been deleted.")."<br />";
				}
				else
				{
					$output .= _("Failed to delete image file.")."<br />";
				}
			}
			else
				$output .= _("Failed to locate image file.")."<br />";
		}
		$sql = "delete from images where imageID = '" . $rmid . "'";
		if ( mysql_query( $sql, $conn ) )
		{
			$output .= _("Image definition has been removed.")."<br />";
			lg( _("image deleted")." :: $_GET[delid]" );				
		}
		else
			$output .= mysql_error();
			
		echo $output;
	}
	echo ( "</div>" );	
}
else
{
	if ( $_POST["update"] == "1" && is_numeric( $_POST["imgid"] ) )
	{
		try
		{
			// Error checking
			if (empty($_POST['name']))
			{
				throw new Exception('An image name is required!');
			}
			if ($FOGCore->getClass('ImageManager')->exists($_POST['name'], $_POST['imgid']))
			{
				throw new Exception('An image already exists with this name!');
			}
			if (empty($_POST['file']))
			{
				throw new Exception('An image file name is required!');
			}
			if (empty($_POST['storagegroup']))
			{
				throw new Exception('A Storage Group is required!');
			}
			if (empty($_POST['imagetype']) && $_POST['imagetype'] != '0')
			{
				throw new Exception('An image type is required!');
			}
		
			// Define new Image object with data provided
			$image = new Image($_POST['imgid']);
			
			$image	->set('name', 		$_POST['name'])
				->set('description',	$_POST['description'])
				->set('path', 		$_POST['file'])
				->set('storageGroupID', $_POST['storagegroup'])
				->set('osID', 		$_POST['os'])
				->set('imageTypeID', 	($_POST['imagetype'] ? $_POST['imagetype'] : 0));
			
			// Save to database
			if ($image->save())
			{
				// Log History event
				$FOGCore->logHistory(sprintf('Image added: ID: %s, Name: %s', $image->get('id'), $image->get('name')));
			
				// Set session message
				$FOGCore->setMessage('Image updated!');
			
				// Redirect to new entry
				$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=$sub&imageid=" . $image->get('id'));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Log History event
			$FOGCore->logHistory(sprintf('Image update failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
		
			// Set session message
			$FOGCore->setMessage($e->getMessage());
		}	
	}
	
	?>
	<h2><?php print _("Edit image definition"); ?></h2>
	<?php
	
	$image = new Image($_GET["imageid"]);
	
	if ($image->isValid())
	{
		echo ( "<center>" );
		if ( $_GET["tab"] == "gen" || $_GET["tab"] == "" )
		{
			echo ( "<form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&imageid=$_GET[imageid]\">" );
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("Image Name").":</td><td><input type=\"text\" name=\"name\" value=\"" . $image->get('name') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Image Description").":</td><td><textarea name=\"description\" rows=\"10\" cols=\"40\">" . $image->get('description') . "</textarea></td></tr>" );
				echo ( "<tr><td>"._("Operating System").":</td><td>" . $FOGCore->getClass('OSManager')->buildSelectBox($image->get('osID')) . "</td></tr>" );
				echo ( "<tr><td>"._("Storage Group").":</td><td>" . $FOGCore->getClass('StorageGroupManager')->buildSelectBox($image->get('storageGroupID')) . "</td></tr>" );				
				
				$masterStorageNode = $image->getStorageGroup()->getMasterStorageNode();
				
				echo ( "<tr><td>"._("Image File").":</td><td>" . ($masterStorageNode ? $masterStorageNode->get('path') : '') . "<input type=\"text\" name=\"file\" value=\"" . $image->get('path') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Image Type").":</td><td>" .  $FOGCore->getClass('ImageTypeManager')->buildSelectBox($image->get('imageTypeID')) . " <a href=\"javascript:popUpWindow('static/imagetypehelp.html');\"><img class=\"noBorder\" src=\"./images/help.png\" /></a></td></tr>" );
				echo ( "<tr><td colspan=2><center><input type=\"hidden\" name=\"update\" value=\"1\" /><input type=\"hidden\" name=\"imgid\" value=\"" . $image->get('id') . "\" /><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );				
			echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["tab"] == "delete" )
		{
			echo ( "<p>"._("Are you sure you would like to remove this image?")."</p>" );
			echo ( "<p><a href=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmimageid=" . $image->get('id') . "\"><img class=\"link\" src=\"images/delete.png\"></a></p>" );
		}
		echo ( "</center>" );
	}
}