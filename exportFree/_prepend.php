<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }
# common (public & admin)

if(defined('DC_CONTEXT_ADMIN')) {
	# admin only

	# loading of plugin class
	$__autoload['dcPluginHelper023'] = dirname(__FILE__).'/inc/class.dcPluginHelper.php';
	$__autoload['exportFree'] = dirname(__FILE__).'/inc/class.export.Free.php';
	$__autoload['dcExportFlatFree'] = dirname(__FILE__).'/inc/class.dc.export.flat.Free.php';
	$__autoload['FreeMaintenanceExportblog'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';
	$__autoload['FreeMaintenanceExportfull'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';

	# initialization
	$core->exportFree = new exportFree(basename(dirname(__FILE__)));

	$core->addBehavior('importExportModules',array('exportFree','ieRegisterModules'));
} else {
	# public only
	
}
