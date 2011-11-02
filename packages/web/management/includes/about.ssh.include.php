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
<h2><?php print _("FOG Server Shell Access"); ?></h2>

<applet class="shell" width="600" height="377" archive="./lib/ssh/libbrowser.jar,./lib/ssh/SSHTerm-1.0.0.jar,./lib/ssh/SSHVnc.jar,./lib/ssh/SecureTunneling.jar,./lib/ssh/ShiFT.jar,./lib/ssh/j2ssh-common-0.2.7.jar,./lib/ssh/j2ssh-core-0.2.7.jar,./lib/ssh/cog-jglobus.jar,./lib/ssh/commons-logging.jar,./lib/ssh/cryptix-asn1-signed.jar,./lib/ssh/cryptix-signed.jar,./lib/ssh/cryptix32-signed.jar,./lib/ssh/filedrop.jar,./lib/ssh/jce-jdk13-135.jar,./lib/ssh/log4j-1.2.6.jar,./lib/ssh/openssh-pk-1.1.0.jar,./lib/ssh/puretls-signed.jar,./lib/ssh/putty-pk-1.1.0.jar,./lib/ssh/jlirc-unix-soc.jar" code="com.sshtools.sshterm.SshTermApplet">
	<param name=sshterm.autoconnect.host value="<?php print $_SERVER["HTTP_HOST"]; ?>" />
	<param name=sshterm.autoconnect.port value="<?php print $GLOBALS['FOGCore']->getSetting( "FOG_SSH_PORT"); ?>" />
	<param name=sshterm.autoconnect.username value="<?php print $GLOBALS['FOGCore']->getSetting( "FOG_SSH_USERNAME"); ?>" />
	<param name=sshterm.ui.autoHide value="true" />
</applet>