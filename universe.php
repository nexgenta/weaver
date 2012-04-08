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

class Universe extends Thing
{
	public function stories($order = 'title')
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'story', 'universe' => $this->uuid, 'order' => $order));
	}

	public function things($order = 'title')
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'thing', 'universe' => $this->uuid, 'order' => $order));
	}

	public function characters($order = 'title')
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'character', 'universe' => $this->uuid, 'order' => $order));
	}

	public function places($order = 'title')
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'place', 'universe' => $this->uuid, 'order' => $order));
	}
	
	protected function rdfResource($doc, $request)
	{
		$base = $request->base . $this->__get('relativeURI') . '/';
		$me = new RDFURI($doc->primaryTopic);
		$g = $doc->subject($doc->primaryTopic, URI::void.'Dataset');
		$g['rdfs:label'] = $this->title;
		$g['rdf:type'] = new RDFURI('http://purl.org/ontology/olo/core#OrderedList');
		$g['olo:length'] = 5;
		

		$sub = new RDFInstance(null, 'http://purl.org/ontology/olo/core#Slot');
		$doc->add($sub);
		$g['olo:slot'] = $sub;
		$sub['olo:item'] = new RDFURI($base . 'stories');
		$sub['olo:index'] = 1;
		$g['void:classPartition'] = new RDFURI($base . 'stories');
		$sub = $doc->subject($base . 'stories', URI::void.'Dataset');
		$sub['rdfs:label'] = 'Stories';
		$sub['void:Class'] = new RDFURI('http://contextus.net/stories/Story');
		$sub['void:inDataset'] = $me;
		
		$sub = new RDFInstance(null, 'http://purl.org/ontology/olo/core#Slot');
		$doc->add($sub);
		$g['olo:slot'] = $sub;
		$sub['olo:item'] = new RDFURI($base . 'events');
		$sub['olo:index'] = 2;
		$g['void:classPartition'] = new RDFURI($base . 'events');
		$sub = $doc->subject($base . 'events', URI::void.'Dataset');
		$sub['rdfs:label'] = 'Events';
		$sub['void:Class'] = new RDFURI('http://purl.org/NET/c4dm/event.owl#Event');
		$sub['void:inDataset'] = $me;

		$sub = new RDFInstance(null, 'http://purl.org/ontology/olo/core#Slot');
		$doc->add($sub);
		$g['olo:slot'] = $sub;
		$sub['olo:item'] = new RDFURI($base . 'characters');
		$sub['olo:index'] = 3;
		$g['void:classPartition'] = new RDFURI($base . 'characters');
		$sub = $doc->subject($base . 'characters', URI::void.'Dataset');
		$sub['rdfs:label'] = 'Characters';
		$sub['void:Class'] = new RDFURI(URI::foaf.'Agent');
		$sub['void:inDataset'] = $me;
		
		$sub = new RDFInstance(null, 'http://purl.org/ontology/olo/core#Slot');
		$doc->add($sub);
		$g['olo:slot'] = $sub;
		$sub['olo:item'] = new RDFURI($base . 'places');
		$sub['olo:index'] = 4;
		$g['void:classPartition'] = new RDFURI($base . 'places');	
		$sub = $doc->subject($base . 'places', URI::void.'Dataset');
		$sub['rdfs:label'] = 'Places';
		$sub['void:Class'] = new RDFURI(URI::geo.'Point');
		$sub['void:inDataset'] = $me;
		
		$sub = new RDFInstance(null, 'http://purl.org/ontology/olo/core#Slot');
		$doc->add($sub);
		$g['olo:slot'] = $sub;
		$sub['olo:item'] = new RDFURI($base . 'things');
		$sub['olo:index'] = 5;
		$g['void:classPartition'] = new RDFURI($base . 'things');
		$sub = $doc->subject($base . 'things', URI::void.'Dataset');
		$sub['rdfs:label'] = 'Things';
		$sub['void:Class'] = new RDFURI(URI::owl.'Thing');
		$sub['void:inDataset'] = $me;

	}
}
