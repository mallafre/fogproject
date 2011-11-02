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
class PXEFileBuilder
{
	private $defaultOption;
	private $otherOptions;
	private $comment;

        function __construct()
	{
            $this->defaultOption = null;
            $this->otherOptions = array();
            $this->comment = null;
	}

        public function generate()
        {
            $pxe = $comment;
            $pxe .= "\n";
            if ( $this->defaultOption != null )
            {
                $pxe .= "DEFAULT " . $this->defaultOption->getLabel() . "\n";
                $pxe .= $this->defaultOption->generate();
                $pxe .= "\n";
            }

            foreach( $otherOptions as $option )
            {
                if ( $option == null )
                    continue;

                $pxe .= $option->generate();
                $pxe .= "\n";
            }
            return $pxe;
        }

        public function addOtherOption( $option )
        {
            $this->otherOptions[] = $option;
        }

        public function getDefaultOption()
        {
            return $this->defaultOption;
        }

        public function setDefaultOption($defaultOption)
        {
            $this->defaultOption = $defaultOption;
        }

        public function getComment()
        {
            return $this->comment;
        }

        public function setComment($comment)
        {
            $this->comment = $comment;
        }
}
?>
