<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

__('Export Free');									// plugin name
__('Export your blog for Free');		// description plugin

class exportFree {
	
	protected  $plugin_id;			// ID plugin
	
	
	public static function MaintenanceInit($maintenance) {
		$maintenance
		->addTask('FreeMaintenanceExportblog')
		->addTask('FreeMaintenanceExportfull')
		;
	}
	
	public static function registerModules($modules, $core) {
		$modules['export'] = array_merge($modules['export'], array('dcExportFlatFree'));
	}
	
	protected function setDefaultConfigs() {
		global $core;
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# config plugin (TODO: specific settings)
		//$core->blog->settings->addNamespace($this->plugin_id);
		//$core->blog->settings->$this->plugin_id->put('enabled',false,'boolean',__('Enable plugin'),false,true);
		# user config plugin (TODO: specific settings)
		//$core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$core->auth->user_prefs->$this->plugin_id->put('enabled',false,'boolean',__('Enable plugin'),false,true);
	}

	/*---------------------------------------------------------------------------
	 * Helper for dotclear version 2.7 and more
	 * Version : 0.16.0
	 * Copyright © 2008-2015 Gvx
	 *-------------------------------------------------------------------------*/
	public function __construct($root='') {
		global $core;
		# check plugin_id
		if(empty($root)) {
			$root = dirname(__FILE__);
			if(!is_file($root.'/_define.php')) { $root = dirname($root); }
		}
		if(!is_file($root.'/_define.php')) { throw new DomainException(__('Invalid plugin directory')); }
		$this->plugin_id = basename($root);
		# set default settings if empty
		if(is_callable(array($this, 'setDefaultConfigs'))) { $this->setDefaultConfigs(); }
	}
	
	public function install($dcMinVer=null, $cb_init=null) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $core;
		try {
			# check DC version
			if(!empty($dcMinVer) && is_string($dcMinVer)) {
				if (version_compare(DC_VERSION, $dcMinVer, '<')) {
					$plugin_name = $core->plugins->moduleInfo($this->plugin_id, 'name');
					throw new Exception(sprintf(__('%s require Dotclear version %s or more.'), $plugin_name, $dcMinVer));
				}
			}
			$new_version = $core->plugins->moduleInfo($this->plugin_id, 'version');
			$old_version = $core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) return;
			# installation specific Actions
			if(is_callable($cb_init)) { call_user_func($cb_init); }
			# default settings
			if(is_callable(array($this, 'setDefaultConfigs'))) { $this->setDefaultConfigs(); }
			$core->setVersion($this->plugin_id, $new_version);
			return true;
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			return false;
		}
	}
	
	public function adminMenu($options=array()) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $core, $_menu;
		$options = array_merge(
			array(
				'icon'	=> 'inc/admin/icon.png',
				'menu'	=> 'Plugins',
				'perm'	=> 'admin'
			),
			$options
		);
		$_menu[$option['menu']]->addItem(
			html::escapeHTML(__($core->plugins->moduleInfo($this->plugin_id,'name'))),		// Item menu
			$core->adminurl->get('admin.plugin.'.$this->plugin_id),												// Page admin url
			dcPage::getPF($this->plugin_id.'/'.$options['icon']),													// Icon menu
			preg_match(																																		// Pattern url
				'/'.$core->adminurl->get('admin.plugin.'.$this->plugin_id).'(&.*)?$/',
				$_SERVER['REQUEST_URI']
			),
			$core->auth->check($options['perm'],$core->blog->id)													// Permissions minimum
		);
	}

	public function adminBaseline($items=array()) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $core;
		if(empty($items)) { $items = array( $core->plugins->moduleInfo($this->plugin_id,'name') => ''); }
		echo dcPage::breadcrumb(array_merge(array(html::escapeHTML($core->blog->name) => ''),$items)).dcPage::notices()."\n";
	}

	protected function settings($key, $value=null, $global=false) {
		global $core;
		if(is_null($value)) {
			return $core->blog->settings->$this->plugin_id->$key;
		} else {
			$core->blog->settings->$this->plugin_id->put($key, $value, null, null, true, $global);
		}
	}

	protected function userSettings($key, $value=null, $global=false) {
		global $core;
		if(is_null($value)) {
			return $core->auth->user_prefs->$this->plugin_id->$key;
		} else {
			$core->auth->user_prefs->$this->plugin_id->put($key,$value, null, null, true, $global);
		}
	}
		
}
