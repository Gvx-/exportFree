<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'Export Free',
	/* Description*/	'Export your blog for Free',
	/* Author */		'Gvx, Olivier Meunier & Contributors',
	/* Version */		'0.7.4-r0011',
	array(
		/* standard plugin options dotclear */
		'permissions'			=>	'admin',
		'type'					=>	'plugin',
		'Priority'				=>	1010,
		'support'	/* url */	=>	'http://forum.dotclear.org/viewtopic.php?id=48599',
		'details' 	/* url */	=>	'https://bitbucket.org/Gvx_/dotclear-plugin-export-pour-free',
		'requires'	/* id */	=>	array(
			'importExport',
			'maintenance'
		),
		/* specific plugin options */
		'_dc_min_version'		=> '2.8',
		'_php_min_version'		=> '5.2',
		'_icon_small'			=> '/inc/icon-small.png',
		'_icon_large'			=> '/inc/icon-large.png'
	)
);
