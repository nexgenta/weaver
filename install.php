<?php

/*
 * Copyright 2012 Mo McRoberts.
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

class WeaverModuleInstall extends ModuleInstaller
{
	public function canBeSoleWebModule()
	{
		return true;
	}
	
	public function canCoexistWithSoleWebModule()
	{
		return false;
	}

	public function writeAppConfig($file, $isSoleWebModule = false, $chosenWebApp = null)
	{
		fwrite($file, "\$SETUP_MODULES[] = 'weaver';\n");
		$this->writeWebRoute($file, $isSoleWebModule);
		fwrite($file, "\$CLI_ROUTES['weaver'] = array('name' => 'weaver', 'class' => 'WeaverCommands', 'file' => 'cli.php', 'adjustBase' => true, 'description' => 'Weaver Commands');\n");
	}
}
