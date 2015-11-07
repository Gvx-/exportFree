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

	public static function MaintenanceInit($maintenance) {
		$maintenance
			->addTask('FreeMaintenanceExportblog')
			->addTask('FreeMaintenanceExportfull')
		;
	}

	public static function ieRegisterModules($modules, $core) {
		$modules['export'] = array_merge($modules['export'], array('dcExportFlatFree'));
	}

	/*---------------------------------------------------------------------------
	 * Helper for dotclear version 2.8 and more
	 * Version : 0.20.8
	 * Copyright © 2008-2015 Gvx
	 * Licensed under the GPL version 2.0 license.
	 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
	 *-------------------------------------------------------------------------*/

	/* --== SPECIFIQUE FUNCTIONS ==-- */

	protected function setDefaultSettings() {
		# config plugin (TODO: specific settings)
		//$this->core->blog->settings->addNamespace($this->plugin_id);
		//$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		# user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
	}

	/* --== STANDARD FUNCTIONS ==-- */

	protected $plugin_id;				// ID plugin
	protected $admin_url;				// admin url plugin
	protected $options = array();		// options plugin

	public static function init($options=array(), $instanceName=__CLASS__) {
		global $core;
		try {
			if(!isset($core->{$instanceName})) {
				$core->{$instanceName} = new self($options);
			} else {
				throw new LogicException(sprintf(__('Conflict: dcCore or other plugin, and %s plugin.'), $instanceName));
			}
		} catch(Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	public function __construct($options=array()) {
		global $core;
		$this->core = &$core;
		# check plugin_id and admin url
		if(!array_key_exists('root', $options) || !is_file($options['root'].'/_define.php')) {
			$options['root'] = dirname(__FILE__);
			if(!is_file($options['root'].'/_define.php')) { $options['root'] = dirname($options['root']); }
		}
		if(!is_file($options['root'].'/_define.php')) { throw new DomainException(__('Invalid plugin directory')); }
		$this->plugin_id = basename($options['root']);
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# default options
		if(!is_array($options)) { $options = array(); }
		$options['icons'] = array_merge(
			array(
				'small' => '/inc/icon-small.png',
				'large' => '/inc/icon-large.png'
			),
			(array_key_exists('icons', $options) && is_array($options['icons']) ? $options['icons'] : array())
		);
		$this->options = array_merge(
			array(
				'perm'		=> 'admin'
			),
			$options
		);

		# set default settings if empty
		if(is_callable(array($this, 'setDefaultSettings'))) { $this->setDefaultSettings(); }
	}

	public function install($dcMinVer=null) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check DC version
			if(!empty($dcMinVer) && is_string($dcMinVer)) {
				if (version_compare(DC_VERSION, $dcMinVer, '<')) {
					$this->core->plugins->deactivateModule($this->plugin_id);
					throw new Exception(sprintf(__('%s require Dotclear version %s or more.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name'), $dcMinVer));
				}
			}
			# check plugin versions
			$new_version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }

			# default settings
			if(is_callable(array($this, 'setDefaultSettings'))) { $this->setDefaultSettings(); }

			# --BEHAVIOR-- pluginInstallActions
			if($this->core->callBehavior('pluginInstallActions', $this->plugin_id, $old_version) === false) {
				throw new Exception(sprintf(__('[Plugin %s] Unknown error in installation.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name')));
			}

			$this->core->setVersion($this->plugin_id, $new_version);
			return true;
		} catch (Exception $e) {
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		if(array_key_exists($menu, $_menu)) {
			$_menu[$menu]->addItem(
				html::escapeHTML(__($this->core->plugins->moduleInfo($this->plugin_id,'name'))),		// Item menu
				$this->core->adminurl->get($this->admin_url),											// Page admin url
				dcPage::getPF($this->plugin_id.$this->options['icons']['small']),						// Icon menu
				preg_match(																																		// Pattern url
					'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				$this->core->auth->check($this->options['perm'], $this->core->blog->id)					// Permissions minimum
			);
		} else {
			throw new ErrorException(sprinf(__('%s menu not present.'), $menu), 0, E_USER_NOTICE, __FILE__, __LINE__);
		}
	}

	public function adminDashboardFavs($core, $favs) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$favs->register($this->plugin_id, array(
			'title'			=> $core->plugins->moduleInfo($this->plugin_id, 'name'),
			'url'			=> $core->adminurl->get($this->admin_url),
			'small-icon'	=> dcPage::getPF($this->plugin_id.$this->options['icons']['small']),
			'large-icon'	=> dcPage::getPF($this->plugin_id.$this->options['icons']['large']),
			'permissions'	=> $this->options['perm']
		));
	}

	public function adminBaseline($items=array()) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if(empty($items)) { $items = array( $this->core->plugins->moduleInfo($this->plugin_id,'name') => ''); }
		return dcPage::breadcrumb(array_merge(array(html::escapeHTML($this->core->blog->name) => ''),$items)).dcPage::notices()."\n";
	}

	public function adminFooterInfo() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		$support = $this->core->plugins->moduleInfo($this->plugin_id, 'support');
		$details = $this->core->plugins->moduleInfo($this->plugin_id, 'details');
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->plugin_id.$this->options['icons']['small']).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					$this->configLink(__('Settings'), $this->admin_url).
					html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	public function settings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->blog->settings->{$this->plugin_id}->$key;
		} else {
			$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
		}
	}

	public function userSettings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->auth->user_prefs->{$this->plugin_id}->$key;
		} else {
			$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
		}
	}

	public function info($item=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} else {
			return $this->core->plugins->moduleInfo($this->plugin_id, $item);
		}
	}

	public function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$file = html::escapeHTML($file).(strpos($file,'?') === false ? '?' : '&amp;').'v='.$version;
				return '<script type="text/javascript" src="'.$this->core->blog->getQmarkURL().'pf='.$file.'"></script>'."\n";
			} else {
				return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
			}
		}
	}

	public function cssLoad($src, $media='screen') {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$file = html::escapeHTML($file).(strpos($file,'?') === false ? '?' : '&amp;').'v='.$version;
				return '<link rel="stylesheet" href="'.$this->core->blog->getQmarkURL().'pf='.$file.'" type="text/css" media="'.$media.'" />'."\n";
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
		}
	}

	public function checkConfig() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if(is_file(path::real($this->core->plugins->moduleInfo($this->plugin_id, 'root').'/_config.php'))) {
			return $this->core->auth->isSuperAdmin() || $this->settings('enabled');
		} else {
			return true;
		}
	}

	public function configLink($label, $redir=null) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if($this->core->auth->isSuperAdmin() && is_file(path::real($this->core->plugins->moduleInfo($this->plugin_id, 'root').'/_config.php'))) {
			$redir = $this->core->adminurl->get(empty($redir) ? $this->admin_url : $redir);
			$href = $this->core->adminurl->get('admin.plugins', array('module' => $this->plugin_id,'conf' => 1, 'redir' => $redir));
			return '<a href="'.$href.'">'.$label.'</a>&nbsp;-&nbsp;';
		}
	}
	
}
