<?php

if (IS_INCLUDED !== true) die(_("Unable to load system configuration information."));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	if ($sub == "add")
	{
		require_once("./includes/snapin.add.include.php");
	}
	else if ($sub == "list")
	{
		require_once("./includes/snapin.list.include.php");
	}		
	else if ($sub == "edit")
	{
		require_once("./includes/snapin.edit.include.php");
	}
	else if ($sub == "search")
	{
		require_once("./includes/snapin.search.include.php");
	}						
	else
	{
		if ($GLOBALS['FOGCore']->getSetting( "FOG_VIEW_DEFAULT_SCREEN") == "LIST")
		{
			require_once("./includes/snapin.list.include.php");
		}
		else
		{
			require_once("./includes/snapin.search.include.php");
		}
	}
}