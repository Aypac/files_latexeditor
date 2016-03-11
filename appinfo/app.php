<?php

/* check if we need this

$output=Array();                                                                                                                            

$return=1;                                                                                                                                  

exec('which latex',$output,$return);

*/

// only load text editor if the user is logged in

if (\OCP\User::isLoggedIn()) {

        OCP\Util::callRegister();
	$eventDispatcher = \OC::$server->getEventDispatcher();
	$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {

		OCP\Util::addscript('files_latexeditor', 'livequery');
		OCP\Util::addscript('files_latexeditor', 'latexeditor');
		

	});

}



