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

class Event extends Thing
{
	public function stories()
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'story', 'tags' => $this->uuid));
	}

	public function verify()
	{
		$model = self::$models[get_class($this)];
		if(true !== ($r = parent::verify()))
		{
			return $r;
		}
		if(isset($this->notionalDate))
		{
			if(isset($this->date))
			{
				$this->notionalDate = $this->date;
			}
			else
			{
				return "Events must at least have a notional date";
			}
		}
		$this->transformProperty('factor', 'factors', true);
		$this->transformProperty('agent', 'agents', true);
		$this->transformProperty('place', 'places', true);
		if(isset($this->factors))
		{
			foreach($this->factors as $k => $factor)
			{
				if(null === ($obj = $model->locateObject($factor, null, 'thing', $this->universe)))
				{
					return 'Factor "' . $factor . '" does not exist yet';
				}
				$this->factors[$k] = $obj->uuid;
			}
		}
		if(isset($this->agents))
		{
			foreach($this->agents as $k => $character)
			{
				if(null === ($obj = $model->locateObject($character, null, 'character', $this->universe)))
				{
					return 'Agent "' . $character . '" does not exist yet';
				}
				$this->agents[$k] = $obj->uuid;
			}
		}
		if(isset($this->places))
		{
			foreach($this->places as $k => $place)
			{
				if(null === ($obj = $model->locateObject($place, null, 'place', $this->universe)))
				{
					return 'Place "' . $place . '" does not exist yet';
				}
				$this->places[$k] = $obj->uuid;
			}
		}
		return  true;
	}

	protected function rdfResource($doc, $request)
	{
		$events = 'http://purl.org/NET/c4dm/event.owl#';
		$g = $doc->subject($doc->primaryTopic, $events.'Event');
		$stories = $this->stories();
		foreach($stories as $story)
		{
			$g['dct:subject'] = new RDFURI($request->base . $story->instanceRelativeURI);
		}
		if(isset($this->title))
		{
			$g['dct:title'] = $this->title;
		}
		if(isset($this->factors) && $a = $this->offsetGet('factors'))
		{
			foreach($a as $obj)
			{
				$g['ev:factor'] = new RDFURI($request->base . $obj->instanceRelativeURI);
			}		   
		}
		if(isset($this->agents) && $a = $this->offsetGet('agents'))
		{
			foreach($a as $obj)
			{
				$g['ev:agent'] = new RDFURI($request->base . $obj->instanceRelativeURI);
			}
		}
		if(isset($this->places) && $a = $this->offsetGet('places'))
		{
			foreach($a as $obj)
			{
				$g['ev:place'] = new RDFURI($request->base . $obj->instanceRelativeURI);
			}
		}
	}
}
