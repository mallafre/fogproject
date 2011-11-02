<?php

echo "@@start\n";
echo "@@general\n";
echo  trim(shell_exec("uname -r") . substr("\n", 0, -2)) . "\n";
echo  trim(shell_exec("hostname")) . "\n";
echo trim(shell_exec("uptime") ) . "\n";
echo trim(shell_exec("cat /proc/cpuinfo | head -n2 | tail -n1 | cut -f2 -d: | sed 's| ||'")) . "\n";
echo trim(shell_exec("grep '^processor' /proc/cpuinfo | tail -n 1 | awk '{print\$3+1}'") ) . "\n";
echo trim( shell_exec("cat /proc/cpuinfo | head -n5 | tail -n1 | cut -f2 -d: | sed 's| ||'") ) . "\n";
echo trim( shell_exec("cat /proc/cpuinfo | head -n7 | tail -n1 | cut -f2 -d: | sed 's| ||'") ) . "\n";
echo trim( shell_exec("cat /proc/cpuinfo | head -n8 | tail -n1 | cut -f2 -d: | sed 's| ||'") ) . "\n";

echo trim( shell_exec("free -m | head -n2 | tail -n1 | awk '{ print $2 }'") ) . "\n"; // total mem
echo trim( shell_exec("free -m | head -n3 | tail -n1 | awk '{ print $3 }'") ) . "\n";	// mem used
echo trim( shell_exec("free -m | head -n3 | tail -n1 | awk '{ print $4 }'") ) . "\n";	// mem free

echo "@@fs\n";
$t = shell_exec("df | grep -vE \"^Filesystem|shm\"");
$l = explode("\n", $t);
$hdtotal = 0;
$hdused = 0;
foreach ($l as $n) 
{
	if (preg_match("/(\d+) +(\d+) +(\d+) +\d+%/", $n, $matches))
	{
		if ( is_numeric( $matches[1] ) )
		{
			$hdtotal += $matches[1];
		}
		if ( is_numeric( $matches[2] ) )
		{
			$hdused += $matches[2];
		}
   	}
}

if ($hdtotal > 1500000) 
{
	$hdtotal = round($hdtotal / 1024 / 1024, 1) . " GB";
} 
else 
{
	$hdtotal = round($hdtotal / 1024, 1)  . " MB";
}
echo $hdtotal . "\n";


if ($hdused > 1500000) 
{
	$hdused = round($hdused / 1024 / 1024, 1) . " GB";
} 
else 
{
	$hdused = round($hdused / 1024, 1) . " MB";
}
echo $hdused . "\n";
echo "@@nic\n";
$allNic = shell_exec("cat '/proc/net/dev'");
$arLines = explode("\n", $allNic);
foreach($arLines as $line) 
{
	if (preg_match('/:/', $line)) 
	{
		list($dev_name, $stats_list) = preg_split('/:/', $line, 2);
		$stats = preg_split('/\s+/', trim($stats_list));
		echo trim($dev_name) . "$$" . $stats[0] . "$$" .  $stats[8] . "$$" . ($stats[2]+$stats[10]) . "$$" . ($stats[3]+$stats[11]) . "\n";
		                       // rx bytes            tx                    errors                            drops
       }

}
echo "@@end";
?>
