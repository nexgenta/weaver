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


uses('date', 'rdf');

require_once(dirname(__FILE__) . '/model.php');

class Thing extends Storable
{
	protected $relativeURI = null;
	protected $storableClass = 'Thing';

	public static function objectForData($data, $model = null, $className = null)
	{
		if(!$model)
		{
			$model = Weaver::getInstance();
		}
		if(!strlen($className) || $className == 'Thing')
		{
			if(!isset($data['kind']))
			{
				$data['kind'] = 'thing';
			}
			switch($data['kind'])
			{
			case 'universe':
				require_once(dirname(__FILE__) . '/universe.php');
				$className = 'Universe';
				break;
			case 'character':
				require_once(dirname(__FILE__) . '/character.php');
				$className = 'Character';
				break;
			case 'story':
				require_once(dirname(__FILE__) . '/story.php');
				$className = 'Story';
				break;
			case 'thing':
				$className = 'Thing';
				break;
			case 'event':
				require_once(dirname(__FILE__) . '/event.php');
				$className = 'Event';
				break;
			default:
				trigger_error('Thing::objectForData(): No suitable class for a "' . $data['kind'] . '" thing is available', E_USER_NOTICE);
				return null;
			}
		}
		return parent::objectForData($data, $model, $className);
	}

	public function __get($name)
	{
		if($name == 'relativeURI')
		{
			if(null === $this->relativeURI)
			{
				$this->parentRelativeURI();
				$this->relativeURI();
			}
			return $this->relativeURI;
		}
		if($name == 'instanceRelativeURI')
		{
			return $this->instanceRelativeURI();
		}
	}

	protected function parentRelativeURI()
	{
		if(isset($this->parent) && ($obj = $this->offsetGet('parent')) && is_object($obj))
		{
			/* Use __get() because depending on the class of 'obj',
			 * PHP may not invoke it magically...
			 */
			$this->relativeURI = $obj->__get('relativeURI');
		}
		else if(isset($this->universe) && ($obj = $this->offsetGet('universe')) && is_object($obj))
		{
			$this->relativeURI = $obj->__get('relativeURI');
			switch($this->kind)
			{
			case 'story':
				$this->relativeURI .= '/stories';
				break;
			case 'thing':
				$this->relativeURI .= '/things';
				break;
			case 'character':
				$this->relativeURI .= '/characters';
				break;
			case 'event':
				$this->relativeURI .= '/events';
				break;
			case 'place':
				$this->relativeURI .= '/places';
				break;
			}
		}
	}

	protected function relativeURI()
	{
		$slug = null;
		if(isset($this->slug))
		{
			$slug = $this->slug;
		}
		else if(isset($this->uuid))
		{
			$slug = $this->uuid;
		}
		if(strlen($this->relativeURI))
		{
			$this->relativeURI .= '/' . $slug;
		}
		else
		{
			$this->relativeURI = $slug;
		}
	}

	protected function instanceRelativeURI()
	{
		return $this->__get('relativeURI') . '#' . (isset($this->fragment) ? $this->fragment : $this->kind);
	}
	
	public function merge()
	{
	}

	public function verify()
	{
		$model = self::$models[get_class($this)];
		if(isset($this->universe))
		{
			if(null === ($u = $model->locateObject($this->universe, null, 'universe')))
			{
				return 'The universe "' . $this->universe . '" does not exist yet';
			}
			$this->referenceObject('universe', $u->uuid);
		}
		$this->transformProperty('link', 'links');
		$this->transformProperty('altName', 'altNames');
		$this->transformProperty('subject', 'subjects');
		$this->ensurePropertyIsAnArray('sameAs');
		$this->ensurePropertyIsAnArray('seeAlso');
		$this->ensurePropertyIsAnArray('containedIn');
		return true;
	}

	protected function mergeReplace($parent, $key)
	{
		if(!isset($this->{$key}) && isset($parent->{$key}))
		{
			$this->{$key} = $parent->{$key};
		}
	}

	protected function mergeArrays($parent, $key)
	{
		if(!isset($this->{$key}))
		{
			$this->{$key} = array();
		}
		if(isset($parent->{$key}))
		{
			if(isset($parent->_refs) && in_array($key, $parent->_refs))
			{
				if(!in_array($key, $this->_refs))
				{
					$this->_refs[] = $key;
				}
			}				
			foreach($parent->{$key} as $value)
			{
				if(!in_array($value, $this->{$key}))
				{
					$this->{$key}[] = $value;
				}
			}
		}
	}
	
	protected function transformProperty($singular, $plural, $isRef = false)
	{
		if(isset($this->{$singular}))
		{
			if(is_array($this->{$singular}) && (!count($this->{$singular}) || isset($this->{$singular}[0])))
			{
				$this->{$plural} = $this->{$singular};
			}
			else if(count($this->{$singular}))
			{
				$this->{$plural} = array($this->{$singular});
			}
			unset($this->{$singular});
		}
		if(isset($this->{$plural}) && $isRef)
		{
			$this->referenceObject($plural, $this->{$plural});
		}
	}

	protected function ensurePropertyIsAnArray($name)
	{
		if(isset($this->{$name}) && (!is_array($this->{$name}) || !isset($this->{$name}[0])))
		{
			$this->{$name} = array($this->{$name});
		}
	}

	public function rdf($doc, $request)
	{	   
		$this->rdfDocument($doc, $request);
		$this->rdfResource($doc, $request);
		$this->rdfLinks($doc, $request);
	}

	protected function rdfDocument($doc, $request)
	{
		$resourceGraph = $doc->resourceTopic();
		$resourceGraph['dct:created'] = new RDFDateTime($this->created);
		$resourceGraph['dct:modified'] = new RDFDateTime($this->modified);
		$resourceGraph['foaf:primaryTopic'] = new RDFURI($doc->primaryTopic);
		$resourceGraph['rdfs:label'] = 'Description of the ' . $this->kind . ' “' . $this->title . '”';
	}

	protected function rdfResource($doc, $request)
	{
		$g = $doc->subject($doc->primaryTopic, RDF::owl.'Thing');
		$g['rdfs:label'] = $this->title;
		if(isset($this->subjects))
		{
			foreach($this->subjects as $subj)
			{
				$g['dct:subject'] = new RDFURI($subj);
			}
		}
		if(isset($this->sameAs))
		{
			foreach($this->sameAs as $subj)
			{
				$g['owl:sameAs'] = new RDFURI($subj);
			}
		}
		if(isset($this->seeAlso))
		{
			foreach($this->seeAlso as $subj)
			{
				$g['rdfs:seeAlso'] = new RDFURI($subj);
			}
		}
	}

	protected function rdfLinks($doc, $request)	   
	{
		if(isset($this->links))
		{
			foreach($this->links as $link)
			{
				$g = $doc->subject($link['href'], 'http://xmlns.com/foaf/0.1/Document');
				if(isset($link['title']))
				{
					$g['dct:title'] = $link['title'];
				}
				if(isset($link['description']))
				{
					$g['dct:description'] = $link['description'];
				}
				$g['foaf:topic'] = new RDFURI($doc->primaryTopic);
			}
		}
	}

	protected function rdfReferenceInto(&$list, $uri, $request, $fragment = null)
	{
		$r = $this->rdfReference($uri, $request, $fragment, true);
		if(is_array($r))
		{
			foreach($r as $e)
			{
				$list[] = $e;
			}
		}
		else if($r)
		{
			$list[] = $r;
		}
	}

	protected function rdfReference($uri, $request, $fragment = null, $all = false)
	{
		if(strlen($fragment))
		{
			$fragment = '#' . $fragment;
		}
		if(null !== ($uuid = UUID::isUUID($uri)))
		{
			/* Fetch target */
			$obj = self::$models[get_class($this)]->objectForUUID($uuid);
			if($all)
			{
				$list = array();
				while($obj && $obj->kind != 'scheme')
				{
					$list[] = new RDFURI($request->base . $obj->__get('instanceRelativeURI'));
					$obj = $obj['parent'];
				}
				return $list;
			}
			return new RDFURI($request->base . $obj->__get('instanceRelativeURI'));
		}
	    if(substr($uri, 0, 1) == '/')
		{
			return new RDFURI($uri . $fragment);
		}
		return new RDFURI($uri);
	}

	public function featuredIn()
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'story', 'thing' => $this->uuid));
	}

	public function featuredInEvents()
	{
		$model = self::$models[get_class($this)];
		return $model->query(array('kind' => 'event', 'tags' => $this->uuid, 'order' => 'date'));
	}

}
