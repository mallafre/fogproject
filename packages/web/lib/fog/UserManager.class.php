<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2010  Chuck Syperski & Jian Zhang
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
 
class UserManager extends FOGManagerController
{
	// Table
	public $databaseTable = 'users';
	
	// Search query
	public $searchQuery = 'SELECT * FROM users WHERE uName LIKE "%${keyword}%"';
	
	// Custom function
	
	
	
	
	
	// LEGACY
	const ORDERBY_USERNAME = 1;
	
	function isPasswordValid($password, $passwordConfirm)
	{
		try
		{
			// Error checking
			if ($password != $passwordConfirm)
			{
				throw new Exception('Passwords do not match');
			}
			if (strlen($password) < $GLOBALS['FOGCore']->getSetting('FOG_USER_MINPASSLENGTH'))
			{
				throw new Exception('Password too short');
			}
			if (preg_replace('/[' . preg_quote($GLOBALS['FOGCore']->getSetting('FOG_USER_VALIDPASSCHARS')) . ']/', '', $password) != '')
			{
				throw new Exception('Invalid characters in password');
			}
			
			// Success
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
			
			// Fail
			return false;
		}
	}
	
	function isValidPassword( $password1, $password2 )
	{
		if ( $password1 == $password2 )
		{
			if ( strlen($password1) >= $GLOBALS['FOGCore']->getSetting( "FOG_USER_MINPASSLENGTH" ) )
			{
				$passChars = $GLOBALS['FOGCore']->getSetting('FOG_USER_VALIDPASSCHARS');
				for( $i = 0; $i < strlen( $password1 ); $i ++ )
				{
					$blFound = false;
					for( $z = 0; $z < strlen( $passChars ); $z++ )
					{
						if ( $passChars[$z] == $password1[$i] )
						{
							$blFound = true;
							break;
						}
					}
					
					if ( ! $blFound ) return false;
				}
				return true;
			}
		}
		return true;
	}
	
	public function isValidUsername( $username )
	{
		return preg_match("/^[[:alnum:]]*$/", $username );
	}
	
	public function attemptLogin( $username, $password )
	{
		if ( $username != null && $this->isValidUsername( $username ) && $password != null )
		{
			$sql = "SELECT 
					*
				FROM 
					users 
				WHERE 
					uName = '" . $this->DB->sanitize($username) . "' and 
					uPass = '" . md5( $password ) . "'";
					
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
				{
					return new User($ar);
				}
			}					
		}
		return null;
	}
	
	public function getAllUsers($ordering=self::ORDERBY_USERNAME)
	{
		if ( $this->DB != null && $ordering !== null && is_numeric( $ordering ) && $ordering >= 0 )
		{
			$orderby = "";
			if ( $ordering = self::ORDERBY_USERNAME )
				$orderby = " order by uName";
				
			$sql = "SELECT 
					uId
				FROM 
					users 
				" . $orderby;

			if ( $this->DB->query($sql) )
			{
				$arUserIds = array();
				while( $ar = $this->DB->fetch()->get() )
				{
					$arUserIds[] = $ar["uId"];
				}
				
				$arOut = array();
				for( $i = 0; $i < count( $arUserIds ); $i++ )
				{
					$uid = $arUserIds[$i];
					if ( $uid !== null && is_numeric($uid) )
					{
						$tmpUser = new User($uid);
						if ( $tmpUser != null )
							$arOut[] = $tmpUser;
					}
				}	
				return $arOut;
			}
		}
		return null;
	}

	public function deleteUser( $user )
	{
		if ( $this->DB != null && $user != null && $user->get('id') >= 0 )
		{
			$sql = "DELETE FROM users WHERE uId = '" . $this->DB->sanitize( $user->get('id') ) . "'";
			return $this->DB->query($sql)->affected_rows() == 1;
		}
		return false;
	}

	public function updateUser( $user )
	{
		if ( $this->DB != null && $user != null && $user->get('id') >= 0 )
		{
			if ( ! $this->exists( $user->get('name'), $user->get('id') ) )
			{
				$sql = "UPDATE 
						users 
					SET 
						uName = '" . $this->DB->sanitize( $user->get('name') ) . "', 
						" . (($user->get('password') != null &&  strlen(trim( $user->get('password') )) > 0) ? "uPass = MD5('" . $this->DB->sanitize( $user->get('password') ) . "'), " : "" ) . "
						uType = '" . $this->DB->sanitize($user->get('type')) . "' 
					WHERE 
						uId = " . $user->get('id');
						
				return $this->DB->query($sql)->affected_rows() == 1;
			}
			else
				throw new Exception( _("A user with this name already exists!") );
		}
		return false;
	}

	public function addUser( $user, $strCreator )
	{
		if ( $this->DB != null && $user != null )
		{
			if ( ! $this->exists( $user->get('name') ) )
			{
				$sql = "INSERT INTO 
						users( uName, uPass, uCreateDate, uCreateBy, uType ) 
						values( '" . $this->DB->sanitize($user->get('name')) . "', MD5('" . $this->DB->sanitize($user->getPassword()) . "'), NOW(), '" . $this->DB->sanitize($strCreator) . "', '" . (($user->get('type') == User::TYPE_MOBILE) ? '1' : '0') . "')";	
				return $this->DB->query($sql)->affected_rows() == 1;
			}
			else
				throw new Exeption(_("User already exists!"));
		}
		
		return false;	
	}
}