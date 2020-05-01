<?php
/**
  * This file is part of exportFree plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\exportFree
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2015-2020 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'Export Free',
	/* Description*/	'Export your blog host at Free',
	/* Author */		'Gvx, Olivier Meunier & Contributors',
	/* Version */		'0.9.0-dev-r0079',
	array(
		/* standard plugin options dotclear */
		'permissions'				=>	'admin',
		'type'						=>	'plugin',
		'priority'					=>	1010,
		'support'	/* url */		=>	'http://forum.dotclear.org/viewtopic.php?id=48599',
		'details' 	/* url */		=>	'https://github.com/Gvx-/exportFree',
		'requires'	/* id(s) */		=>	array(
			array('core', '2.16'),
			'importExport',
			'maintenance'
		),
		/* depuis dc 2.11 */
		'settings'					=> array(
			//'self'				=> ''
			//, 'blog'				=> '#params.id'
			//, 'pref'				=> '#user-options.id'
		),
		/* specific plugin options */
		'_class_name'				=> 'exportFree',							// Required: plugin master class name
		'_class_path'				=> '/inc/class.exportFree.php',				// Required: plugin master class path (relative)
		'_icon_small'				=>	'/inc/icon-small.png',					// Optional: plugin small icon path (16*16 px) (relative)
		'_icon_large'				=>	'/inc/icon-large.png'					// Optional: plugin large icon path (64*64 px) (relative)
	)
);
