<?php
/*
 *  FOG is a computer imaging solution.
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

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

?>
<h2><?php print _("FOG Version Information"); ?></h2>
<?php

echo ( "<p>" );
	echo ( "&nbsp;"._("Version").": " . FOG_VERSION  );
echo ( "</p>" );
echo ( "<p>" );
	echo ( "<div class=\"sub\">" );
	$blUseProxy = false;
	if ( trim( $GLOBALS['FOGCore']->getSetting( "FOG_PROXY_IP" ) ) != null )
	{
		$blUseProxy = true;
		$proxy = $GLOBALS['FOGCore']->getSetting( "FOG_PROXY_IP" ).":".$GLOBALS['FOGCore']->getSetting( "FOG_PROXY_PORT" );
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, '10');
	if ( $blUseProxy )
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
	curl_setopt($ch, CURLOPT_URL, "http://freeghost.sourceforge.net/version/index.php?version=" . FOG_VERSION );
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$ret = curl_exec($ch);
	echo ( "</div>" );
echo ( "</p>" );