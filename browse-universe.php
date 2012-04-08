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

require_once(dirname(__FILE__) . '/browser.php');

class WeaverBrowseUniverse extends WeaverBrowser
{
	protected $templateName = 'universe.phtml';
	protected $isDataSet = true;
	
	public function __construct()
	{
		parent::__construct();
		$this->routes['stories'] = array('file' => 'browse-stories.php', 'class' => 'WeaverBrowseStories');
		$this->routes['things'] = array('file' => 'browse-things.php', 'class' => 'WeaverBrowseThings');
		$this->routes['events'] = array('file' => 'browse-event.php', 'class' => 'WeaverBrowseEvent');
		$this->routes['characters'] = array('file' => 'browse-characters.php', 'class' => 'WeaverBrowseCharacters');
		$this->routes['places'] = array('file' => 'browse-places.php', 'class' => 'WeaverBrowsePlaces');
	}

	public function process(Request $req)
	{
		$this->crumbName = $this->object->title;
		return parent::process($req);
	}

	protected function getObject()
	{
		if(null !== ($tag = $this->request->consume()))
		{
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		$this->title = $this->object->title;
		$this->objects = array();
		$this->objects['stories'] = $this->object->stories();
		$this->objects['things'] = $this->object->things();
		$this->objects['characters'] = $this->object->characters();
		$this->objects['places'] = $this->object->places();
		return true;
	}
}