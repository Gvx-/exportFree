<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'Export Free',
	/* Description*/	'Export your blog for Free',
	/* Author */		'Gvx, Olivier Meunier & Contributors',
	/* Version */		'0.7.4-r0018',
	array(
		/* standard plugin options dotclear */
		'permissions'				=>	'admin'
		, 'type'					=>	'plugin'
		, 'Priority'				=>	1010
		, 'support'		/* url */	=>	'http://forum.dotclear.org/viewtopic.php?id=48599'
		, 'details' 	/* url */	=>	'https://bitbucket.org/Gvx_/dotclear-plugin-export-pour-free'
		, 'requires'	/* id(s) */	=>	array(
			array('core', '2.8')
			, 'importExport'
			, 'maintenance'
		)
		/* specific plugin options */
		, '_icon_small'			=>	'/inc/icon-small.png'
		, '_icon_large'			=>	'/inc/icon-large.png'
	)
);
