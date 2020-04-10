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

# define id and class specific plugin
$pluginId = basename(dirname(__FILE__));
$pluginClassName = $core->plugins->moduleInfo($pluginId, '_class_name');

# Loadings & initialization
if(!empty($pluginClassName)) {
	$__autoload[$pluginClassName] = dirname(__FILE__).$core->plugins->moduleInfo($pluginId, '_class_path');
	$core->{$pluginClassName} = new $pluginClassName($core, $pluginId);
}
