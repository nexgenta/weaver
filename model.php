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

uses('store', 'uri');

URI::registerPrefix('po', 'http://purl.org/ontology/po/');
URI::registerPrefix('stories', 'http://contextus.net/stories/');
URI::registerPrefix('ev', 'http://purl.org/NET/c4dm/event.owl#');
URI::registerPrefix('olo', 'http://purl.org/ontology/olo/core#');
URI::registerPrefix('tl', 'http://purl.org/NET/c4dm/timeline.owl#');

require_once(dirname(__FILE__) . '/thing.php');

if(!defined('WEAVER_IRI')) define('WEAVER_IRI', null);

class Weaver extends Store
{
	protected $storableClass = 'Thing';
	protected $queriesCalcRows = true;	

	public static function getInstance($args = null)
	{
		if(!isset($args['class'])) $args['class'] = 'Weaver';
		if(!isset($args['db'])) $args['db'] = WEAVER_IRI;
		return parent::getInstance($args);
	}

	public function locateObject($slug, $parent = null, $kind = null, $universe = null)
	{
		$query = array();
		$query['limit'] = 1;
		if($parent !== false)
		{
			$query['parent'] = $parent;
		}
		if($kind !== null)
		{
			$query['kind'] = $kind;
		}
		if($universe !== null)
		{
			$query['universe'] = $universe;
		}
		if(null !== ($uuid = UUID::isUUID($slug)))
		{
			$query['uuid'] = $uuid;
		}
		else if(strpos($slug, ':') !== false)
		{
			$query['iri'] = $slug;
		}
		else
		{
			$query['tag'] = $slug;
		}
		$rs = $this->query($query);
		foreach($rs as $obj)
		{
			return $obj;
		}
		return null;
	}

	public /*callback*/ function storedTransaction($db, $args)
	{
		$uuid = $args['uuid'];
		$json = $args['json'];
		$lazy = $args['lazy'];
		$data = $args['data'];
		$this->db->exec('DELETE FROM {weaver_core} WHERE "uuid" = ?', $uuid);
		$this->db->exec('DELETE FROM {weaver_storyref} WHERE "story" = ?', $uuid);
		if(isset($data['slug']))
		{
			$data['tag'] = $data['slug'];
		}
		if(!isset($data['tags'])) $data['tags'] = array();
		$coreinfo = array('parent' => null);
		$storyrefs = array();
		if(isset($data['title']))
		{
			$title = preg_replace('![^a-z0-9]!i', '-', strtolower(trim($data['title'])));
			while(substr($title, 0, 1) == '-') $title = substr($title, 1);
			while(substr($title, -1) == '-') $title = substr($title, 0, -1);
			while(strstr($title, '--') !== false) $title = str_replace('--', '-', $title);
			if(strlen($title))
			{
				$coreinfo['title'] = $title;
				if(ctype_alpha($title[0]))
				{
					$coreinfo['title_firstchar'] = $title[0];
				}
				else
				{
					$coreinfo['title_firstchar'] = '*';
				}
			}
		}
		if(isset($data['universe']))
		{
			$coreinfo['universe'] = $data['universe'];
		}
		if($data['kind'] == 'story' && isset($data['events']))
		{
			foreach($data['events'] as $ev)
			{
				if(null !== ($edata = $this->dataForUUID($ev)))
				{
					if(isset($edata['places']))
					{
						foreach($edata['places'] as $thing)
						{
							$storyrefs[] = array('uuid' => $ev, 'thing' => $thing, 'story' => $uuid);
						}
					}
					if(isset($edata['agents']))
					{
						foreach($edata['agents'] as $thing)
						{
							$storyrefs[] = array('uuid' => $ev, 'thing' => $thing, 'story' => $uuid);
						}
					}
					if(isset($edata['factors']))
					{
						foreach($edata['factors'] as $thing)
						{
							$storyrefs[] = array('uuid' => $ev, 'thing' => $thing, 'story' => $uuid);
						}
					}
					$data['tags'][] = $ev;
				}
			}
		}
		if(isset($data['factors']))
		{
			foreach($data['factors'] as $factor)
			{
				$data['tags'][] = $factor;
			}
		}
		if(isset($data['agents']))
		{
			foreach($data['agents'] as $agent)
			{
				$data['tags'][] = $agent;
			}
		}
		if(isset($data['places']))
		{
			foreach($data['places'] as $place)
			{
				$data['tags'][] = $place;
			}
		}
		if(isset($data['notionalDate']))
		{
			$coreinfo['notional_date'] = $data['notionalDate'];
		}
		if(count($coreinfo))
		{
			$coreinfo['uuid'] = $uuid;
			$this->db->insert('weaver_core', $coreinfo);
		}
		if(count($storyrefs))
		{
			foreach($storyrefs as $ref)
			{
				$this->db->insert('weaver_storyref', $ref);
			}
		}
		$args['data'] = $data;
		return parent::storedTransaction($db, $args);
	}

	protected function buildQuery(&$qlist, &$tables, &$query)
	{
		if(!isset($tables['weaver_core'])) $tables['weaver_core'] = 'weaver_core';
		if(!isset($tables['weaver_storyref']))
		{
			$tables['weaver_storyref'] = array('name' => 'weaver_storyref', 'clause' => '"weaver_storyref"."story" = "obj"."uuid"');
		}

		foreach($query as $k => $v)
		{
			$value = $v;
			switch($k)
			{
			case 'parent':
				unset($query[$k]);
				if($v === null)
				{
					$qlist['weaver_core'][] = '"weaver_core"."parent" IS NULL';
				}
				else
				{
					$qlist['weaver_core'][] = '"weaver_core"."parent" = ' . $this->db->quote($v);
				}
				break;
			case 'universe':
				unset($query[$k]);
				if($v === null)
				{
					$qlist['weaver_core'][] = '"weaver_core"."universe" IS NULL';
				}
				else
				{
					$qlist['weaver_core'][] = '"weaver_core"."universe" = ' . $this->db->quote($v);
				}
				break;				
			case 'title_firstchar':
				unset($query[$k]);
				$qlist['weaver_core'][] = '"weaver_core"."title_firstchar" = ' . $this->db->quote($v);
				break;
			case 'thing':
				unset($query[$k]);
				$qlist['weaver_storyref'][] = '"weaver_storyref"."thing" = ' . $this->db->quote($v);
				break;
			}
		}
		return parent::buildQuery($qlist, $tables, $query);
	}

	protected function parseOrder(&$order, $key, $desc = true)
	{
		$dir = $desc ? 'DESC' : 'ASC';
		switch($key)
		{
		case 'title':
			$order['weaver_core'][] = '"weaver_core"."title" ' . $dir;
			return true;
		case 'date':
			$order['weaver_core'][] = '"weaver_core"."notional_date" ' . $dir;
			return true;			
		}
		return parent::parseOrder($order, $key, $desc);
	}
}
