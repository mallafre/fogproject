<?php
/*
 *  FOG  is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
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
@error_reporting( 0 ); 

define( "IMAGEDIR", "./imagepool" );

if ( is_dir(IMAGEDIR) ) 
{

	if ($dh = opendir(IMAGEDIR)) 
	{
		$arFiles = array();
		while (($file = readdir($dh)) !== false) 
		{
			
			if ( is_file( IMAGEDIR . "/" . $file ) )
			{
				$arFiles[] = $file;
			}
		} 
		
		if ( count( $arFiles ) > 0 )
		{
			$intRand = rand( 0, (count($arFiles) -1) );
			header("Content-type: image/jpeg");
			@readfile(IMAGEDIR . "/" . $arFiles[$intRand]);
		}
	}
} 
 
?>
