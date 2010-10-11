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

class WeaverBrowseCharacters extends WeaverBrowser
{
	protected $templateName = 'characters.phtml';
	protected $crumbName = 'Characters';

	protected function getObject()
	{
		if(null !== ($tag = $this->request->consume()))
		{
			if(null !== ($obj = $this->model->locateObject($tag, null, 'character', $this->request->data['universe']->uuid)))
			{
				require_once(dirname(__FILE__) . '/browse-character.php');
				$inst = new WeaverBrowseCharacter();
				$inst->object = $obj;
				$inst->process($this->request);
				return false;
			}
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		$this->objects = $this->request->data['universe']->characters();
		return true;
	}
}