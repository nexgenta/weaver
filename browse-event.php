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

class WeaverBrowseEvent extends WeaverBrowser
{
	protected $templateName = 'event.phtml';
	protected $crumbName = 'Events';

	protected function getObject()
	{
		if(null === ($tag = $this->request->consume()))
		{
			return $this->request->redirect($this->request->root . $this->request->data['universe']->relativeURI);
		}
		if(null === ($this->object = $this->model->locateObject($tag, null, 'event', $this->request->data['universe']->uuid)))
		{
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		$this->title = $this->crumbName = $this->object->title;
		$this->addCrumb();
		return true;
	}
}

		