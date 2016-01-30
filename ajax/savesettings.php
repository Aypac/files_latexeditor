<?php

OCP\JSON::checkAppEnabled('files_latexeditor');
OCP\JSON::checkAdminUser();
if (OCP\Config::getSystemValue('latexsettings', 'true')=='true') {
	OCP\Config::setSystemValue('latexsettings', 'false');
} else {
	OCP\Config::setSystemValue('versions', 'true');
}

?>
