<?php

/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2011  Chuck Syperski & Jian Zhang
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

class PXEFileOption
{

    private $label;
    private $kernel;
    private $arKernelOptions;
    private $arAppendOptions;

    function __construct($label=null)
    {
        $this->label = $label;
        $this->kernel = null;
        $this->arKernelOptions = array();
        $this->arAppendOptions = array();
    }

    public function generate()
    {
        $option = "LABEL " . $this->label . "\n";
        $option .= "kernel " . $this->kernel;

        foreach ( $this->arKernelOptions as $key => $value )
        {
            if ( $key == null )
                continue;

            $option .= " " . $key;
            
            if( $value != null )
                $option .= "=" . $value;
        }

        $option .= "\n";

        $option .= "append " . $this->kernel . "\n";

        foreach ( $this->arAppendOptions as $key => $value )
        {
            if ( $key == null )
                continue;

            $option .= " " . $key;
            
            if( $value != null )
                $option .= "=" . $value;
        }

        $option .= "\n";
        return $option;
    }

    public function addKernelOption( $key, $value )
    {
        $this->arKernelOptions[$key] = $value;
    }

    public function removeKernelOption( $key )
    {
        unset( $this->arKernelOptions[$key] );
    }

    public function addAppendOption( $key, $value )
    {
        $this->arAppendOptions[$key] = $value;
    }

    public function removeAppendOption( $key )
    {
        unset( $this->arAppendOptions[$key] );
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

}
?>
