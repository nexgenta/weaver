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

class WeaverBrowseStories extends WeaverBrowser
{
	protected $templateName = 'stories.phtml';
	protected $crumbName = 'Stories';
	protected $isDataSet = true;
	
	protected function getObject()
	{
		if(null !== ($tag = $this->request->consume()))
		{
			if(null !== ($obj = $this->model->locateObject($tag, null, 'story', $this->request->data['universe']->uuid)))
			{
				require_once(dirname(__FILE__) . '/browse-story.php');
				$inst = new WeaverBrowseStory();
				$inst->object = $obj;
				$inst->process($this->request);
				return false;
			}
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		$this->objects = $this->request->data['universe']->stories();
		return true;
	}
	
	protected function populateRDF($doc)
	{		
		$me = $doc->subject($doc->primaryTopic, URI::void.'Dataset');
		$me['rdfs:label'] = 'Stories';
		$parent = $doc->primaryTopic;
		if(substr($parent, -1) == '/') $parent = substr($parent, 0, -1);
		if(substr($parent, 0, 1) == '/') $parent = substr($parent, 1);
		$parent = explode('/', $parent);
		array_pop($parent);
		$me['void:inDataset'] = new RDFURI('/' . implode('/', $parent));
		foreach($this->objects as $obj)
		{
			$uri = $this->request->base . $obj->relativeURI;
			$me['rdfs:seeAlso'] = new RDFURI($uri);
			$sub = $doc->subject($uri, 'http://contextus.net/stories/Story');
			$sub['rdfs:label'] = $obj->title;
		}
	}
}