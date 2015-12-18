<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_CONTEXT_ADMIN')) { return; }

__('Export Free');									// plugin name
__('Export your blog for Free');					// description plugin

class exportFree extends dcPluginHelper023 {

	public static function MaintenanceInit($maintenance) {
		$maintenance
			->addTask('FreeMaintenanceExportblog')
			->addTask('FreeMaintenanceExportfull')
		;
	}

	public static function ieRegisterModules($modules, $core) {
		$modules['export'] = array_merge($modules['export'], array('dcExportFlatFree'));
	}

}
