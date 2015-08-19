<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */					'Export Free',
	/* Description*/		'Export your blog for Free',
	/* Author */				'Gvx, Olivier Meunier & Contributors',
	/* Version */				'0.6.1',
	array(
		'permissions' =>	'admin',
		'type'				=>	'plugin',
		'Priority'		=>	1010
	)
);
