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
}
