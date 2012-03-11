<?php

// Blackout - 2:54 PM 23/09/2011
class Image extends FOGController
{
	// LEGACY Variables
	
	// Blackout - 8:21 AM 26/12/2011
	// Adjusted IDs to be consistent with new database rows
	const IMAGE_TYPE_SINGLE_PARTITION_NTFS = 1;
	const IMAGE_TYPE_DD = 2;
	const IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK = 3;
	const IMAGE_TYPE_MULTIPARTITION_MULTIDISK = 4;
	
	// Table
	public $databaseTable = 'images';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'imageID',
		'name'		=> 'imageName',
		'description'	=> 'imageDesc',
		'path'		=> 'imagePath',
		'createdTime'	=> 'imageDateTime',
		'createdBy'	=> 'imageCreateBy',
		'building'	=> 'imageBuilding',
		'size'		=> 'imageSize',
		'imageTypeID'	=> 'imageTypeID',
		'storageGroupID'=> 'imageNFSGroupID',
		'osID'		=> 'imageOSID',
		// TODO: Add 'size' for Image Size
		'size'		=> 'imageSize'
	);
	
	// Custom functions
	public function getStorageGroup()
	{
		return new StorageGroup($this->get('storageGroupID'));
	}
	
	public function getOS()
	{
		return new OS($this->get('osID'));
	}
	
	public function getImageType()
	{
		return new ImageType($this->get('imageTypeID'));
	}
	
	// Legacy functions - remove once updated in other areas
	public function setStorageGroup($id) 			{ }

	public function setID( $id )				{ $this->set('id', $id); }
	public function getID()				{ return $this->get('id'); 	}

	public function setType( $t )				{ $this->set('imageTypeID', $t); }
	public function getType()				{ return $this->get('imageTypeID'); 	}
	
	public function setName( $n )				{ $this->set('name', $n); }
	public function getName()				{ return $this->get('name');}
	
	public function setDescription( $d )			{ $this->set('description', $d); }
	public function getDescription()			{ return $this->get('description'); 	}
	
	public function setPath( $p )				{ $this->set('path', $p); }
	public function getPath()				{ return $this->get('path'); }
	
	public function setCreator( $c )			{ $this->set('createdBy', $c); }
	public function getCreator()				{ return $this->get('createdBy'); 	}
	
	public function setDate( $d )				{ $this->set('createdTime', $d); }
	public function getDate()				{ return $this->get('createdTime'); 	}	
}