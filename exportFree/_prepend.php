<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

# check PHP version
if(version_compare(PHP_VERSION, '5.2', '<')) {
	$_id = basename(dirname(__FILE__));
	if(defined('DC_CONTEXT_ADMIN')) {
		dcPage::addErrorNotice(sprintf(__('%1$s require PHP version %2$s. (your PHP version is %3$s)'), $_id, '5.2', PHP_VERSION));
	}
	$core->plugins->deactivateModule($_id);
	unset($_id);
	return;
}

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$__autoload['exportFree'] = dirname(__FILE__).'/inc/class.export.Free.php';
$__autoload['dcExportFlatFree'] = dirname(__FILE__).'/inc/class.dc.export.flat.Free.php';
$__autoload['FreeMaintenanceExportblog'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';
$__autoload['FreeMaintenanceExportfull'] = dirname(__FILE__).'/inc/lib.export.Free.maintenance.php';

# initialisation
exportFree::init(array(
	'perm'		=> 'admin',								# permissions acces page administration
	'icons'		=> array(								# icones pour menu & tableau de bord
		'small' => '/inc/icon-small.png',
		'large' => '/inc/icon-large.png'
	)
));

$core->addBehavior('importExportModules',array('exportFree','ieRegisterModules'));
