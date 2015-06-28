<?php

namespace Qualisoft\AppBundle\Config;

class Config
{

	//Estructura de roles para el control de acceso
    static public $ROLES = array(
    	'BASIC' => array(
    		'BASIC'
    		),
    	'STANDARD'	=> array(
    		'BASIC',
    		'STANDARD', 
    		),
    	'ADMIN'	=> array(
    		'BASIC',
    		'STANDARD', 
    		'ADMIN'
    		),    
    	'MASTER'	=> array(
    		'BASIC',
    		'STANDARD', 
    		'ADMIN',
    		'MASTER'
    		),    			
    	);
}