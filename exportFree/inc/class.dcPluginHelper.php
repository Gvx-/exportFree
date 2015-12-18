<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * Plugin helper for dotclear version 2.8 and more
 * Version : 0.23.0
 * Copyright © 2008-2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

if (!defined('NL')) { define('NL',"\n"); }    // New Line

abstract class dcPluginHelper023 {

	### Specific functions to overload ###

	protected function setDefaultSettings() {
		# create config plugin (TODO: specific settings)
		//$this->core->blog->settings->addNamespace($this->plugin_id);
		//$this->core->blog->settings->{$this->plugin_id}->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
		# create user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->put('enabled', false, 'boolean', __('Enable plugin'), false, true);
	}

	protected function installActions($old_version) {
		# upgrade previous versions
		if(!empty($old_version)) {
			
		}
	}

	protected function uninstallActions() {
		# erase config plugin (TODO: specific settings)
		//$this->core->blog->settings->addNamespace($this->plugin_id);
		//$this->core->blog->settings->{$this->plugin_id}->drop('enabled');
		# erase user config plugin (TODO: specific settings)
		//$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
		//$this->core->auth->user_prefs->$this->plugin_id->drop('enabled');
	}

	### Standard functions ###

	protected $plugin_id;				// ID plugin
	protected $admin_url;				// admin url plugin
	protected $icon_small;				// small icon file
	protected $icon_large;				// large icon file

	public function __construct($id) {
		global $core;
		$this->core = &$core;
		# set plugin id and admin url
		$this->plugin_id = $id;
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# set icons
		$this->icon_small = $this->plugin_id.$this->core->plugins->moduleInfo($this->plugin_id, '_icon_small');
		$this->icon_large = $this->plugin_id.$this->core->plugins->moduleInfo($this->plugin_id, '_icon_large');

		# set default settings if empty
		$this->setDefaultSettings();

		# uninstall plugin procedure
		if($this->core->auth->isSuperAdmin()) { $this->core->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }
	}

	### Admin functions ###

	public final function install() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check DC version
			$dcMinVer = $this->core->plugins->moduleInfo($this->plugin_id, '_dc_min_version');
			if(!empty($dcMinVer)) {
				if (version_compare(DC_VERSION, $dcMinVer, '<')) {
					$this->core->plugins->deactivateModule($this->plugin_id);
					throw new Exception(sprintf(__('%s require Dotclear version %s or more.'), $this->core->plugins->moduleInfo($this->plugin_id, 'name'), $dcMinVer));
				}
			}
			# check PHP version
			$phpMinVer = $this->core->plugins->moduleInfo($this->plugin_id, '_php_min_version');
			if(!empty($phpMinVer)) {
				if(version_compare(PHP_VERSION, $phpMinVer, '<')) {
					$this->core->plugins->deactivateModule($this->plugin_id);
					throw new Exception(sprintf(__('%1$s require PHP version %2$s. (your PHP version is %3$s)'), $this->core->plugins->moduleInfo($this->plugin_id, 'name'), $phpMinVer, PHP_VERSION));
				}
			}
			# check plugin versions
			$new_version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }
			# default settings
			$this->setDefaultSettings();
			# specifics install actions
			$this->installActions($old_version);
			# valid install
			$this->core->setVersion($this->plugin_id, $new_version);
			return true;
		} catch (Exception $e) {
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	public final function uninstall() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# specifics uninstall actions
		$this->uninstallActions();
		# delete settings and version
		$this->core->blog->settings->{$this->plugin_id}->dropAll(true);
		$this->core->delVersion($this->plugin_id);
	}

	public final function configLink($label, $redir=null, $prefix='', $suffix='') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if($this->core->auth->isSuperAdmin() && is_file(path::real($this->core->plugins->moduleInfo($this->plugin_id, 'root').'/_config.php'))) {
			$redir = $this->core->adminurl->get(empty($redir) ? $this->admin_url : $redir);
			$href = $this->core->adminurl->get('admin.plugins', array('module' => $this->plugin_id,'conf' => 1, 'redir' => $redir));
			return $prefix.'<a href="'.$href.'">'.$label.'</a>'.$suffix;
		}
	}

	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		if(array_key_exists($menu, $_menu)) {
			$_menu[$menu]->addItem(
				html::escapeHTML(__($this->core->plugins->moduleInfo($this->plugin_id,'name'))),		// Item menu
				$this->core->adminurl->get($this->admin_url),											// Page admin url
				dcPage::getPF($this->icon_small),														// Icon menu
				preg_match(																																		// Pattern url
					'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				$this->core->auth->check($this->core->plugins->moduleInfo($this->plugin_id, 'permissions'), $this->core->blog->id)	// Permissions minimum
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
			'small-icon'	=> dcPage::getPF($this->icon_small),
			'large-icon'	=> dcPage::getPF($this->icon_large),
			'permissions'	=> $core->plugins->moduleInfo($this->plugin_id, 'permissions')
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
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->icon_small).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					$this->configLink(__('Settings'), $this->admin_url, '', '&nbsp;-&nbsp;').
					html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->core->plugins->moduleInfo($this->plugin_id, 'author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	### Widget functions ###

	protected static function widgetHeader(&$w, $title) {
		$w->setting('title', __('Title (optional)').' :', $title);
	}

	protected static function widgetFooter(&$w, $context=true, $class='') {
		if($context) { $w->setting('homeonly', __('Display on:'), 0, 'combo', array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2)); }
		$w->setting('content_only', __('Content only'), 0, 'check');
		$w->setting('class', __('CSS class:'), $class);
		$w->setting('offline', __('To put off line'), false, 'check');
	}

	protected static function widgetAddBasic(&$w, $id, $name, $callback, $help, $title) {
		$w->create($id, $name, $callback, null, $help);
		self::widgetHeader($w->{$id}, $title);
		self::widgetFooter($w->{$id});
	}

	protected static function widgetRender($w, $content) {
		global $core;
		if (($w->homeonly == 1 && $core->url->type != 'default') || ($w->homeonly == 2 && $core->url->type == 'default') || $w->offline || empty($content)) {
			return;
		}
		$content = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').$content;
		return $w->renderDiv($w->content_only, $w->class,'',$content);
	}
	
	### Common functions ###

	public final function settings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->blog->settings->{$this->plugin_id}->$key;
		} else {
			$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
		}
	}

	public final function userSettings($key, $value=null, $global=false) {
		if(is_null($value)) {
			return $this->core->auth->user_prefs->{$this->plugin_id}->$key;
		} else {
			$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
		}
	}

	public final function info($item=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} else {
			return $this->core->plugins->moduleInfo($this->plugin_id, $item);
		}
	}

	public final function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$href = $this->core->blog->getQmarkURL().'pf='.html::escapeHTML($file).(strpos($file, '?') === false ? '?' : '&amp;').'v='.$version;
				return '<script type="text/javascript" src="'.$href.'"></script>'."\n";
			} else {
				return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
			}
		}
	}

	public final function cssLoad($src, $media='screen') {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->core->plugins->moduleInfo($this->plugin_id, 'version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
		} else {
			if(version_compare(DC_VERSION, '2.9', '<')) {
				$href = $this->core->blog->getQmarkURL().'pf='.html::escapeHTML($file).(strpos($file, '?') === false ? '?' : '&amp;').'v='.$version;
				return '<link rel="stylesheet" href="'.$href.'" type="text/css" media="'.$media.'" />'."\n";
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
		}
	}

}
