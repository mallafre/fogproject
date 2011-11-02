<?php

// Blackout - 2:54 PM 23/09/2011
class Image extends FOGController
{
	// Variables
	const IMAGE_TYPE_SINGLE_PARTITION_NTFS = 0;
	const IMAGE_TYPE_DD = 1;
	const IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK = 2;
	const IMAGE_TYPE_MULTIPARTITION_MULTIDISK = 3;
	
	// Table
	protected $databaseTable = 'images';
	
	// Name -> Database field name
	protected $databaseFields = array(
		'id'		=> 'imageID',
		'name'		=> 'imageName',
		'description'	=> 'imageDesc',
		'path'		=> 'imagePath',
		'createdTime'	=> 'imageDateTime',
		'createdBy'	=> 'imageCreateBy',
		'building'	=> 'imageBuilding',
		'size'		=> 'imageSize',
		'type'		=> 'imageDD',
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
		return new OS($this->data['osID']);
	}
	
	// Legacy functions - remove once updated in other areas
	public function setStorageGroup($id) 			{ }

	public function setID( $id )				{ $this->set('id', $id); }
	public function getID()				{ return $this->get('id'); 	}

	public function setType( $t )				{ $this->set('type', $t); }
	public function getType()				{ return $this->get('type'); 	}
	
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