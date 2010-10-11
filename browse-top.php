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

class WeaverBrowseIndex extends WeaverBrowser
{
	protected $title = 'Oh, the tangled web...';
	protected $crumbName = 'Weaver';
	protected $templateName = 'index.phtml';

	protected function getObject()
	{
		if(null === ($tag = $this->request->consume()))
		{
			$this->objects = $this->model->query(array('kind' => 'universe'));
			return true;
		}
		$this->object = $this->model->locateObject($tag, null, 'universe');
		if($this->object === null)
		{
			return $this->error(Error::NOT_FOUND);
		}
		require_once(dirname(__FILE__) . '/browse-universe.php');
		$inst = new WeaverBrowseUniverse();
		$this->request->data['universe'] = $inst->object = $this->object;
		$inst->process($this->request);
		return false;
	}
}