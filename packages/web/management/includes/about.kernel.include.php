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
<h2><?php print _("FOG Kernel Updates"); ?></h2>
<?php

if ( $_GET["file"] != null )
{
	if ( $_GET["install"] == "1"  )
	{
		$_SESSION["allow_ajax_kdl"] = true;
		$_SESSION["dest-kernel-file"] = trim($_POST["dstName"]);
		$_SESSION["tmp-kernel-file"] = rtrim(sys_get_temp_dir(), '/') . '/' . basename( $_SESSION["dest-kernel-file"] );
		$_SESSION["dl-kernel-file"] = base64_decode( $_GET["file"] );
		
		if ( file_exists( $_SESSION["tmp-kernel-file"] ) )
			@unlink( $_SESSION["tmp-kernel-file"] );
		
		?>
			<div id="kdlRes">
				<p id="currentdlstate"><?php echo(_("Starting process...")); ?></p>
				<img id='img' src="./images/loader.gif" />
			</div>
		<?php
	}
	else
	{
		echo ( "<form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&install=1&file=$_GET[file]\"><p>" );
			echo ( _("What would you like to name your new kernel").":  <input class=\"smaller\" type=\"text\" name=\"dstName\" value=\""._("bzImage")."\" />" );
		echo ( "</p>" );
		echo ( "<p>" );
		echo ( "<input class=\"smaller\" type=\"submit\" value=\""._("Next")."\" />" );
		echo ( "</p></form>" );
	}
}
else
{
	echo ( "<div class=\"hostgroup\">" );	
		echo ( _("This section allows you to update the Linux kernel which is used to boot the client computers.  In FOG, this kernel holds all the drivers for the client computer, so if you are unable to boot a client you may wish to update to a newer kernel which may have more drivers built in.  This installation process may take a few minutes, as FOG will attempt to go out to the internet to get the requested Kernel, so if it seems like the process is hanging please be patient.") );
	echo ( "</div>" );
	
	echo ( "<div>" );
		$blUseProxy = false;
		if ( trim( $GLOBALS['FOGCore']->getSetting(  "FOG_PROXY_IP" ) ) != null )
		{
			$blUseProxy = true;
			$proxy = $GLOBALS['FOGCore']->getSetting(  "FOG_PROXY_IP" ).":".$GLOBALS['FOGCore']->getSetting(  "FOG_PROXY_PORT" );
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, '10');
		if ( $blUseProxy )
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		// TODO: Restyle this page using CSS in page
		curl_setopt($ch, CURLOPT_URL, "http://freeghost.sourceforge.net/kernelupdates/index.php?version=" . FOG_VERSION );
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$ret = curl_exec($ch);
	echo ( "</div>" );		
}