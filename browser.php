<?php

/*
 * weaver: The stories engine
 *
 * Copyright 2010-2012 Mo McRoberts.
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

class WeaverBrowser extends Page
{
	protected $defaultSkin = 'weaver';
	protected $modelClass = 'Weaver';
	protected $supportedTypes = array('text/html', 'application/json', 'application/rdf+xml');
	protected $isDataSet = false;
	
	protected function perform_GET_RDF($type = 'application/rdf+xml')
	{
		$uri = $this->request->pageUri;
		if(strlen($uri) > 1 && substr($uri, -1) == '/') $uri = substr($uri, 0, -1);

		if(!isset($this->object))
		{
			$doc = new RDFDocument($uri . 'rdf', $uri);
		}
		else if($this->isDataSet)
		{
			$doc = new RDFDocument($uri . '.rdf', $this->request->base . $this->object->__get('relativeURI'));
		}
		else
		{
			$doc = new RDFDocument($uri . '.rdf', $this->request->base . $this->object->__get('instanceRelativeURI'));		
		}
		if(isset($this->object))
		{
			$this->object->rdf($doc, $this->request);
		}
		else
		{
			$this->populateRDF($doc);
		}
		$this->request->header('Content-type', 'application/rdf+xml');
		$this->request->flush();
		$xml = $doc->asXML();
		if(is_array($xml))
		{
			writeLn(implode("\n", $xml));
		}
		else
		{
			writeLn($xml);
		}
	}

	protected function assignTemplate()
	{
		parent::assignTemplate();
		$uri = $this->request->pageUri;
		if(strlen($uri) > 1 && substr($uri, -1) == '/') $uri = substr($uri, 0, -1);
		$this->links[] = array('href' => $uri . '.rdf', 'type' => 'application/rdf+xml', 'rel' => 'alternate');
	}

}
