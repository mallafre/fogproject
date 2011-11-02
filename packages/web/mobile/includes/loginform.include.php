<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

echo ( "<center><div class=\"login\">\n" );
	echo "<p class=\"loginTitle\">"._("FOG Mobile Login")."</p>\n";
	echo ( "<form method=\"post\" action=\"?node=login\">\n" );
		echo ( "<div class=\"loginElement\">"._("Username").":</div><div class=\"loginElement\"><input type=\"text\" class=\"login\" name=\"uname\" /></div>" );
		echo ( "<br />" );
		echo ( "<div class=\"loginElement\">"._("Password").": </div><div class=\"loginElement\"><input type=\"password\" class=\"login\" name=\"upass\" /></div>" );
		echo ( "<br />" );
		echo ( "<div class=\"loginElement\">"._("Language").": </div><div class=\"loginElement\"><select class=\"login\" name=\"ulang\" />");

		$path = "../management/languages";
		$dir_handle = @opendir($path) or die(_("Unable to open")." $path");
		while ($file = readdir($dir_handle))
			if ($file != "." && $file != ".." && is_dir("$path/$file")){
				echo "<option value=\"$file\"";
				if($_SESSION['locale'] == $file)
					echo " selected=\"selected\" ";
				echo ">$file</option><br/>";
			}
		closedir($dir_handle);
		echo ("</select></div>" );

		echo ( "<p><input type=\"submit\" value=\""._("Login")."\" /></p>" );
	echo ( "</form>" );
echo ( "</div></center>\n" );
?>
