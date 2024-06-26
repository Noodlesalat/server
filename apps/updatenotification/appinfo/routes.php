<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
return [
	'routes' => [
		['name' => 'Admin#createCredentials', 'url' => '/credentials', 'verb' => 'GET'],
		['name' => 'Admin#setChannel', 'url' => '/channel', 'verb' => 'POST'],
		// Fallback app changelog information for mobile clients
		['name' => 'Changelog#showChangelog', 'url' => '/changelog/{app}', 'verb' => 'GET'],
	],
	'ocs' => [
		['name' => 'API#getAppList', 'url' => '/api/{apiVersion}/applist/{newVersion}', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1)']],
		['name' => 'API#getAppChangelogEntry', 'url' => '/api/{apiVersion}/changelog/{appId}', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1)']],
	],
];
