<?php

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

$storagedir = $GLOBALS['FOGCore']->getSetting( "FOG_NFS_DATADIR");
$bytes = ( @disk_total_space( $storagedir ) - @disk_free_space( $storagedir ) );
$gb = round( ( ( ($bytes / 1024) / 1024) /1024), 2);
echo ( $gb . " GB");