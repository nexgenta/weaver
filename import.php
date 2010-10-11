<?php

/*
 * weaver: The stories engine
 *
 * Copyright 2010 Mo McRoberts.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

require_once(dirname(__FILE__) . '/model.php');

class WeaverImport extends CommandLine
{
	protected $modelClass = 'Weaver';

	protected function checkArgs(&$args)
	{
		if(!count($args))
		{
			return $this->error(Error::NO_OBJECT, null, null, 'Usage: weaver import FILE [FILE ...]');
		}
		return true;
	}

	public function main($args)
	{
		$r = true;
		foreach($args as $pathname)
		{
			if(!$this->importFile($pathname))
			{
				$r = false;
			}
		}
		return ($r ? 0 : 1);
	}

	protected function importFile($pathname)
	{
		$info = pathinfo($pathname);
		$base = basename($pathname);
		$class = null;
		if(!isset($info['extension']))
		{
			$info['extension'] = null;
		}
		switch($info['extension'])
		{
		case 'xml':
			require_once(dirname(__FILE__) . '/import-xml.php');
			$class = 'WeaverImportXML';
			break;
		case 'json':
			require_once(dirname(__FILE__) . '/import-json.php');
			$class = 'WeaverImportJSON';
			break;
		default:
			echo $base . ": Error: Unsupported file type\n";
			return false;
		}
		if(!strlen($class) || !class_exists($class))
		{
			echo $base . ": Error: Unable to import (internal error -- class $class does not exist)\n";
			return false;
		}
		$inst = new $class;
		$data = $inst->importFile($pathname);
		if(!$data)
		{
			echo $base . ": Error: Unable to import: import class failed\n";
			return false;
		}
		if(!($thing = Thing::objectForData($data)))
		{
			echo $base . ": Error: Unable to import: could not create asset object\n";
			return false;
		}
		if(true !== ($r = $thing->verify()))
		{
			echo $base . ": Error: Unable to import: " . $r . "\n";
			return false;
		}
		if(!isset($thing->kind))
		{
			$asset->kind = null;
		}
		if(!isset($thing->uuid) && isset($thing->curie))
		{
			if(isset($thing->curie))
			{
				if(null !== ($uuid = $this->model->uuidForCurie($asset->curie)))
				{
					echo $base . ": Note: Matched CURIE [" . $asset->curie . "] to existing UUID " . $uuid . "\n";
					$asset->uuid = $uuid;
				}
			}
		}
		if(!isset($thing->uuid) && $thing->kind == 'universe' && strlen($thing->slug))
		{
			if(($obj = $this->model->locateObject($thing->slug, null, $thing->kind)))
			{	
				$thing->uuid = $obj->uuid;
			}
		}
		if(!isset($thing->uuid) && $thing->kind != 'universe' && !isset($thing->universe))
		{
			echo $base . ": Refusing to import a " . $thing->kind . " which does not have an associated universe.\n";
			return 1;
		}
		if(!isset($thing->uuid) && !isset($thing->slug))
		{
			echo $base . ": Refusing to import a " . $thing->kind . " with no slug.\n";
			return 1;
		}
		if(!isset($thing->uuid))
		{
			if(null !== ($obj = $this->model->locateObject($thing->slug, (isset($thing->parent) ? $thing->parent : null), $thing->kind, $thing->universe)))
			{
				$thing->uuid = $obj->uuid;
			}
		}
		if(isset($thing->uuid))
		{			
			/* Check to see whether asset already exists with that UUID,
			 * and if so whether it's the same kind.
			 */
			if(($old = $this->model->dataForUuid($thing->uuid)))
			{
				if(!isset($old['kind']))
				{
					$old['kind'] = null;
				}
				if(strcmp($thing->kind, $old['kind']))
				{
					echo $base . ": Warning: Updating thing " . $thing->uuid . " from being a '" . $old['kind'] . "' to being a '" . $thing->kind . "'\n";
				}
				else
				{
					echo $base . ": Updating " . $thing->uuid . "\n";
				}
			}
		}
		if(isset($thing->uuid))
		{
			$created = false;
		}
		else
		{
			$created = true;
		}
		$thing->store();
		if($created)
		{
			echo $base . ": Created with UUID ". $thing->uuid . "\n";
		}
		return true;
	}
	
}

abstract class WeaverImportBase
{
	protected $model;
	
	public function __construct()
	{
		$this->model = Weaver::getInstance();
	}
}
