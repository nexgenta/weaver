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

uses('module');

if(!defined('WEAVER_IRI')) define('WEAVER_IRI', null);

class WeaverModule extends Module
{
	public $moduleId = 'com.nexgenta.weaver';
	public $latestVersion = 4;

	public static function getInstance($args = null)
	{
		if(!isset($args['class'])) $args['class'] = 'WeaverModule';
		if(!isset($args['db'])) $args['db'] = WEAVER_IRI;
		return parent::getInstance($args);
	}
	
	protected function dependencies()
	{
		$this->depend('com.nexgenta.eregansu.store');
	}

	public function updateSchema($targetVersion)
	{
		if($targetVersion == 1)
		{
			$t = $this->db->schema->tableWithOptions('weaver_core', DBTable::CREATE_ALWAYS);
			$t->columnWithSpec('uuid', DBType::UUID, null, DBCol::NOT_NULL, null, 'Object UUID');
			$t->columnWithSpec('title', DBType::VARCHAR, 64, DBCol::NULLS, null, 'Normalised title of the item');
			$t->columnWithSpec('title_firstchar', DBType::CHAR, 1, DBCol::NULLS, null, 'First character of the title');
			$t->columnWithSpec('parent', DBType::UUID, null, DBCol::NULLS, null, 'Parent object, excluding brands');
			$t->indexWithSpec(null, DBIndex::PRIMARY, 'uuid');
			$t->indexWithSpec('title', DBIndex::INDEX, 'title');
			$t->indexWithSpec('title_firstchar', DBIndex::INDEX, 'title_firstchar');
			$t->indexWithSpec('parent', DBIndex::INDEX, 'parent');
			return $t->apply();
		}
		if($targetVersion == 2)
		{
			$t = $this->db->schema->table('weaver_core');
			$t->columnWithSpec('universe', DBType::UUID, null, DBCol::NULLS, null, 'Universe UUID');
			$t->indexWithSpec('universe', DBIndex::INDEX, 'universe');
			return $t->apply();
		}
		if($targetVersion == 3)
		{
			$t = $this->db->schema->tableWithOptions('weaver_storyref', DBTable::CREATE_ALWAYS);
			$t->columnWithSpec('uuid', DBType::UUID, null, DBCol::NOT_NULL, null, 'Event UUID');
			$t->columnWithSpec('thing', DBType::UUID, null, DBCol::NOT_NULL, null, 'Referenced thing UUID');
			$t->columnWithSpec('story', DBType::UUID, null, DBCol::NOT_NULL, null, 'Story UUID');
			$t->indexWithSpec('uuid', DBIndex::INDEX, 'uuid');
			$t->indexWithSpec('thing', DBIndex::INDEX, 'thing');
			$t->indexWithSpec('story', DBIndex::INDEX, 'story');
			return $t->apply();
		}
		if($targetVersion == 4)
		{
			$t = $this->db->schema->table('weaver_core');
			$t->columnWithSpec('notional_date', DBType::DATETIME, null, DBCol::NULLS, null, 'Notional event date');
			$t->indexWithSpec('notional_date', DBIndex::INDEX, 'notional_date');
			return $t->apply();
		}
		return false;		
	}

}

