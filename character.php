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

require_once(dirname(__FILE__) . '/thing.php');

class Character extends Thing
{
	protected function rdfResource($doc, $request)
	{
		$g = $doc->graph($doc->primaryTopic, RDF::foaf.'Person');
		if(isset($this->title))
		{
			$g['foaf:name'] = $this->title;
		}
		if(isset($this->altNames))
		{
			foreach($this->altNames as $name)
			{
				$g['foaf:name'] = $name;
			}
		}
		if(isset($this->prefix))
		{
			$g['foaf:title'] = $this->prefix;
		}
		if(isset($this->firstName))
		{
			$g['foaf:firstName'] = $this->firstName;
		}
		if(isset($this->lastName))
		{
			$g['foaf:lastName'] = $this->lastName;
		}
		if(isset($this->givenName))
		{
			$g['foaf:givenName'] = $this->givenName;
		}
		if(isset($this->familyName))
		{
			$g['foaf:familyName'] = $this->familyName;
		}
		if(isset($this->sameAs))
		{
			foreach($this->sameAs as $as)
			{
				$g['owl:sameAs'] = new RDFURI($as);
			}
		}
		if(isset($this->seeAlso))
		{
			foreach($this->seeAlso as $as)
			{
				$g['rdfs:seeAlso'] = new RDFURI($as);
			}
		}
		
	}

	protected function rdfLinks($doc, $request)	   
	{
		parent::rdfLinks($doc, $request);
		$events = $this->featuredInEvents();
		foreach($events as $ev)
		{
			$g = $doc->graph($request->root . $ev->instanceRelativeURI, 'http://purl.org/NET/c4dm/event.owl#Event');
			$g['ev:agent'] = new RDFURI($request->root . $this->__get('instanceRelativeURI'));
		}
	}													
}

