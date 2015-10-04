<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$__autoload['exportFree'] = dirname(__FILE__).'/inc/class.export.Free.php';
$__autoload['dcExportFlatFree'] = dirname(__FILE__).'/inc/class.dc.export.flat.Free.php';
$__autoload['FreeMaintenanceExportblog'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';
$__autoload['FreeMaintenanceExportfull'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';

# initialisation
try {
	if(!isset($core->exportFree)) {
		$core->exportFree = new exportFree();
	} else {
		throw new LogicException(sprintf(__('Conflict: dcCore or other plugin, and %s plugin.'), basename(dirname(__FILE__))));
	}
} catch(Exception $e) {
	$core->error->add($e->getMessage());
}

$core->addBehavior('importExportModules',array('exportFree','ieRegisterModules'));
