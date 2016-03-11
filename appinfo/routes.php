<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/** @var $this OC\Route\Router */


// Route for compile AJAX request
//$this->create('files_latexeditor_compile', '/ajax/compile.php')->actionInclude('files_latexeditor/ajax/compile.php');


namespace OCA\Files_Latexeditor\AppInfo;

$app = new Application();

$app->registerRoutes($this, array('routes' => array(
	[
		'name' => 'Compile#doCompile',
		'url' => '/ajax/compile',
		'verb' => 'POST'
	],
	[
		'name' => 'LatexStorage#updatefile',
		'url' => '/ajax/updatefile',
		'verb' => 'POST'
	]
)));