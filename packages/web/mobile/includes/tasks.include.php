<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	echo ( "<p id=\"contentTitle\">" );
			echo ( _("Tasks Menu") );		
	echo ( "</p>" );	
	
	if ( $_GET[rmtask] != null && is_numeric($_GET[rmtask]) )
	{
		if ( ! ftpDelete( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . "01-" . str_replace ( ":", "-", strtolower($_GET[mac]) ) ) )
		{
			echo( "<p>"._("Unable to delete PXE file.")."</p>" );
		}
		
		$sql = "delete from tasks where taskID = '" . mysql_real_escape_string( $_GET[rmtask] ) . "' limit 1";
		if ( ! mysql_query( $sql, $conn ) )
			echo ( "<p>"._("FOG :: Database error!")."</p>" );
	}

	if ( $_GET["forcetask"] != null && is_numeric($_GET["forcetask"]) )
	{
		$sql = "update tasks set taskForce = '1' where taskID = '" . mysql_real_escape_string( $_GET["forcetask"] ) . "'";
		if ( ! mysql_query( $sql, $conn ) )
			echo( "<p>"._("FOG :: Database error!")."</p>" );
	}	
	
	echo ( "<div class=\"center\">" );
		$sql = "select 
		 		* 
		 		from tasks 
		 		inner join hosts on (taskHostID = hostID)
		 		left outer join images on (hostImage = imageID )
		 		where taskStateID in (0,1) order by taskCreateTime, taskName";	
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( mysql_num_rows( $res ) > 0 )
		{

			echo ( "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=100%>" );
			$i = 0;
			echo ( "<tr bgcolor=\"#e8e8e8\"><td>&nbsp;<b>"._("Force")."</b></td><td>&nbsp;<b>"._("Task Name")."</b></td><td><b>"._("Host")."</b></td><td><font class=\"smaller\">&nbsp;<b>"._("Type")."</b></font></td><td><font class=\"smaller\">&nbsp;<b>"._("State")."</b></font></td><td><font class=\"smaller\">&nbsp;<b>"._("Kill")."</b></font></td></tr>" );
			while( $ar = mysql_fetch_array( $res ) )
			{
				( $i++ % 2 == 0 ) ? ( $bg = "#fbfbfb" ) : ($bg = "" );
				if ( $ar[iState] > 0 )
					$bgcolor = "#B8E2B6";

				$state = state2text($ar["taskStateID"]);
				if ( $ar["taskStateID"] == 0 && hasCheckedIn( $conn, $ar["taskID"] ) )
					$state = "In Line";			

				$hname = $ar["hostName"];
				
				if ( $ar["taskForce"] == "1" )
					$hname = "* " . $hname;
					
				$blAllowForce = false;	
				if ( strtolower($ar["taskType"]) == "d" || strtolower($ar["taskType"]) == "u" )
					$blAllowForce = true;
				echo ( "<tr bgcolor=\"$bg\"><td>&nbsp;" );
				if ( $blAllowForce )
					echo ( "<a href=\"?node=" . $_GET["node"] . "&forcetask=" . $ar["taskID"] . "&mac=" . $ar["hostMAC"] ."\"><img src=\"./images/force.png\" border=0 class=\"task\" /></a>" );
					
				echo ( "</td><td>&nbsp;" . $ar["taskName"] . "</td><td>" . $hname . "</td><td>" . getImageAction( $ar["taskType"] ) . "</td><td>" . $state . "</td><td>&nbsp;&nbsp;<a href=\"?node=$_GET[node]&rmtask=$ar[taskID]&mac=$ar[hostMAC]\"><img src=\"images/kill.png\" border=0 class=\"task\" /></a></td></tr>" );
			}
			echo ( "</table>" );
		} 
		else
		{
			echo ( "<b>"._("No Active Tasks found")."</b>" );
		}

	echo ( "</div>" );	
}
?>


