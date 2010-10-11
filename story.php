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

class Story extends Thing
{
	public function verify()
	{
		$this->transformProperty('event', 'events', true);
		return parent::verify();
	}

	public function events()
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'event', 'universe' => $this->universe, 'tag' => $this->uuid));
	}

	public function characters()
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'character', 'universe' => $this->universe, 'tag' => $this->uuid));
	}
	
	public function rdfResource($doc, $request)
	{
		$stories = 'http://contextus.net/stories/';
		$olo = 'http://purl.org/ontology/olo/core#';
		$g = $doc->graph($doc->primaryTopic, $stories.'Story');
		if(isset($this->subjects))
		{
			foreach($this->subjects as $subj)
			{
				$g->{RDF::dct.'subject'}[] = new RDFURI($subj);
			}
		}
		$el = new RDFGraph(null, $stories.'EventList');
		$events = $this->offsetGet('events');
		$c = 1;
		foreach($events as $ev)
		{
			$eg = new RDFGraph(null, $stories . 'EventSlot');
			$eg->{$olo.'index'}[] = $c;
			$eg->{RDF::rdfs.'label'}[] = $ev->title;
			$eg->{$stories.'item'}[] = new RDFURI($request->root . $ev->__get('instanceRelativeURI'));
			$el->{$stories.'slot'}[] = $eg;
			$c++;
		}
		$g->{$stories.'events'}[] = $el;
	}
}
