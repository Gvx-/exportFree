<?php
/**
  * Plugin dcHelper for dotclear version 2.16 or hegher
  *
  * @package Dotclear\plungin\dcPluginHelper
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2008-2020 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if(!defined('DC_RC_PATH')) { return; }

if(!defined('NL')) { define('NL', "\n"); }									# New Line


if(!function_exists('getInstance')) {										# get class instance in $core
	function getInstance($plugin) { return $GLOBALS['core']->{$plugin}; }
}

/**
 * dcPluginHelper216
 */
abstract class dcPluginHelper216 {

	/** @var string version  */
	const VERSION = '2020.04.08';											# class version

	/**
	 * setDefaultSettings
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	protected function setDefaultSettings() {
		# debug mode
		$this->debugDisplay('Not default settings for this plugin.');
	}

	/**
	 *  installActions
	 *
	 * @todo overloaded
	 *
	 * @param string $old_version
	 *
	 * @return void
	 */
	protected function installActions($old_version) {
		# debug mode
		$this->debugDisplay('Not install actions for this plugin.');
	}

	/**
	 * uninstallActions
	 *
	 * @todo overloaded
	 *
	 * @return boolean
	 */
	protected function uninstallActions() {
		# debug mode
		$this->debugDisplay('Not uninstall actions for this plugin.');
		return true;
	}

	/**
	 * _config
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->check('admin', $this->core->blog->id)) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				//$this->settings('enabled', !empty($_POST['enabled']), $scope);
				$this->core->blog->triggerBlog();
				$this->core->notices->addSuccessNotice( __('Configuration successfully updated.'));
			} catch(exception $e) {
				//$this->core->error->add($e->getMessage());
				$this->core->error->add(__('Unable to save the configuration'));
			}
			if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$this->info('id')) === false) {
				$this->core->error->add(__('Redirection not found'));
				$this->core->adminurl->redirect('admin.home');
			}
			http::redirect($_REQUEST['redir']);
		}
		# debug mode
		$this->debugDisplay('Not config page for this plugin.');
	}

	/**
	 * index
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function index() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		if(!$this->settings('enabled') && is_file(path::real($this->info('root').'/_config.php'))) {
			if($this->core->auth->check('admin', $this->core->blog->id)) {
				$this->core->adminurl->redirect('admin.plugins', array(
					'module' => $this->info('id'),'conf' => 1, 'redir' => $this->core->adminurl->get($this->info('adminUrl'))
				));
			} else {
				$this->core->notices->addNotice('message', sprintf(__('%s plugin is not configured.'), $this->info('name')));
				$this->core->adminurl->redirect('admin.home');
			}
		}
		try {
			if (isset($_POST['save'])) {
				// TODO HERE inputs check
			}
		} catch(exception $e) {
			//$this->core->error->add($e->getMessage());
			$this->core->error->add(__('Unable to save the code'));
		}
		# debug mode
		$this->debugDisplay('Not index page for this plugin.');
	}

	/**
	 * _prepend
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	protected function _prepend() {
		# common (public & admin)

		if(defined('DC_CONTEXT_ADMIN')) {
			# admin only

			# services

		} else {
			# public only

		}
	}

	/**
	 * _admin
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }

	}

	/**
	 * _public
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _public() {

	}

	/**
	 * _xmlrpc
	 *
	 * @todo overloaded
	 *
	 * @return void
	 */
	public function _xmlrpc() {

	}

	### Standard functions ###

	protected $plugin_id;													# ID plugin
	protected $admin_url;													# admin url plugin
	protected $icon_small;													# small icon file
	protected $icon_large;													# large icon file
	protected $core;														# dcCore instance

	private $debug_mode = false;											# debug mode for plugin
	private $debug_log = false;												# debug Log for plugin
	private $debug_log_reset = false;										# debug logfile reset for plugin
	private $debug_logfile;													# debug logfilename for plugin

	/**
	 * __construct
	 *
	 * @param object $core
	 * @param string $id
	 *
	 * @return void
	 */
	public function __construct($core, $id) {
		$this->core =& $core;

		# set plugin id and admin url
		$this->plugin_id = $id;
		$this->admin_url = 'admin.plugin.'.$this->plugin_id;

		# set debug mode
		$debug_options = dirname(__FILE__).'/../.debug.php';
		if(is_file($debug_options)) { require_once($debug_options); }

		# start logfile
		$this->debugLog('START_DEBUG');
		$this->debugLog('Version', $this->core->getVersion($this->plugin_id));
		$this->debugLog('Page', $_SERVER['REQUEST_URI']);

		# Set admin context
		if(defined('DC_CONTEXT_ADMIN')) {
			# register self url
			$urls = $this->core->adminurl->dumpUrls();
			if(!array_key_exists('admin.self', $urls)) {
				$url = http::getSelfURI();
				$url = str_replace('?'.parse_url($url, PHP_URL_QUERY), '', $url);		// delete query
				$url = substr($url, 1 + strrpos($url, '/'));							// keep page name
				if(in_array($url, array_column((array)$urls, 'url'))) {					// Register checked
					$this->core->adminurl->register('admin.self', $url, (empty($_GET) ? array(): $_GET));
				}
			}

			# set icons
			$this->icon_small = $this->plugin_id.$this->info('_icon_small');
			$this->icon_large = $this->plugin_id.$this->info('_icon_large');

			# uninstall plugin procedure
			if($this->core->auth->isSuperAdmin()) { $this->core->addBehavior('pluginBeforeDelete', array($this, 'uninstall')); }
		}

		# set default settings if empty
		$this->setDefaultSettings();

		# debug
		//$this->debugDisplay('Debug mode actived for '.$this->plugin_id.' plugin');

		$this->_prepend();
	}

	/**
	 * __destruct
	 *
	 * @return void
	 */
	public function __destruct() {
		# end logfile
		$this->debugLog('END_DEBUG');
	}

	### Admin functions ###

	/**
	 * _install
	 *
	 * @return void
	 */
	public final function _install() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		try {
			# check plugin versions
			$new_version = $this->info('version');
			$old_version = $this->core->getVersion($this->plugin_id);
			if (version_compare($old_version, $new_version, '>=')) { return; }
			# specifics install actions
			$this->installActions($old_version);
			# valid install
			$this->core->setVersion($this->plugin_id, $new_version);
			$this->debugLog('Update', 'version '.$new_version);
			return true;
		} catch (Exception $e) {
			$this->debugDisplay('[Install] : '.$e->getMessage());
			$this->core->error->add($e->getMessage());
		}
		return false;
	}

	/**
	 * uninstall
	 *
	 * @param object $plugin
	 *
	 * @return void
	 */
	public final function uninstall($plugin) {
		$this->debugLog('uninstall', 'version '.$this->core->getVersion($this->plugin_id));
		# specifics uninstall actions
		if($plugin['id'] == $this->plugin_id) {
			if($this->uninstallActions()) {
				# clean DC_VAR
				if(self::getVarDir($this->plugin_id)) { files::deltree(self::getVarDir($this->plugin_id)); }
				# delete all users prefs
				$this->core->auth->user_prefs->delWorkSpace($this->plugin_id);
				# delete all blogs settings
				$this->core->blog->settings->delNamespace($this->plugin_id);
				# delete version
				$this->core->delVersion($this->plugin_id);
			}
		}
	}

	/**
	 * configScope
	 *
	 * @return string
	 */
	protected final function configScope() {
		return (isset($_POST['scope']) ? $_POST['scope'] : ($this->core->auth->isSuperAdmin() ? 'global' : 'default'));
	}

	/**
	 * configBaseline
	 *
	 * @param mixed $scope
	 * @param boolean $activate
	 *
	 * @return string
	 */
	protected function configBaseline($scope=null, $activate=true) {
		if($this->core->auth->isSuperAdmin()) {
			if(empty($scope)) { $scope = $this->configScope(); }
			$html =	'<p class="anchor-nav">
						<label class="classic">'.__('Scope').'&nbsp;:&nbsp;
							'.form::combo('scope', array(__('Global settings') => 'global', sprintf(__('Settings for %s'), html::escapeHTML($this->core->blog->name)) => 'default'), $scope).'
							<input id="scope_go" name="scope_go" type="submit" value="'.__('Go').'" />
						</label>
						&nbsp;&nbsp;<span class="form-note">'.__('Select the blog in which parameters apply').'</span>
						'.($scope == 'global' ? '&nbsp;&nbsp;<span class="warning">'.__('Update global options').'</span': '').'
					</p>';
		} else {
			$html = '';
		}
		if($activate) {
			$html .= '
				<div class="fieldset clear">
					<h3>'.__('Activation').'</h3>
					<p>
						'.form::checkbox('enabled','1',$this->settings('enabled', null, $scope)).
						'<label class="classic" for="enabled">
							'.sprintf(__('Enable %s on this blog'), html::escapeHTML(__($this->info('name')))).'&nbsp;&nbsp;&nbsp;
						</label>
						<span class="form-note">'.__('Enable the plugin on this blog.').'</span>
					</p>
				</div>'.NL;
		}
		return NL.$this->jsLoad('/inc/config.js').$this->cssLoad('/inc/config.css', 'all', true).dcPage::jsConfirmClose('module_config').$html;
	}

	/**
	 * adminMenu
	 *
	 * @param string $menu
	 *
	 * @return void
	 */
	public function adminMenu($menu='Plugins') {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		global $_menu;
		if(array_key_exists($menu, $_menu)) {
			$_menu[$menu]->addItem(
				html::escapeHTML(__($this->info('name'))),									# Item menu
				$this->core->adminurl->get($this->admin_url),								# Page admin url
				dcPage::getPF($this->icon_small),											# Icon menu
				preg_match(																	# Pattern url
					'/'.$this->core->adminurl->get($this->admin_url).'(&.*)?$/',
					$_SERVER['REQUEST_URI']
				),
				$this->core->auth->check($this->info('permissions'), $this->core->blog->id)	# Permissions minimum
			);
		} else {
			$this->debugDisplay('menu not present.');
			throw new ErrorException(sprinf(__('%s menu not present.'), $menu), 0, E_USER_NOTICE, __FILE__, __LINE__);
		}
	}

	/**
	 * adminDashboardFavs
	 *
	 * @param object $core
	 * @param object $favs
	 *
	 * @return void
	 */
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

	/**
	 * adminBaseline
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	protected function adminBaseline($items=array()) {
		if(empty($items)) { $items = array( $this->info('name') => ''); }
		return dcPage::breadcrumb(array_merge(array(html::escapeHTML($this->core->blog->name) => ''), $items)).$this->core->notices->getNotices()."\n";
	}

	/**
	 * adminFooterInfo
	 *
	 * @return string
	 */
	protected function adminFooterInfo() {
		$support = $this->info('support');
		$details = $this->info('details');
		return '<p class="right">
					<img style="vertical-align: middle;" src="'.dcPage::getPF($this->icon_small).'" alt="'.__('icon plugin').'"/>&nbsp;&nbsp;'.
					html::escapeHTML($this->info('name')).'&nbsp;'.
					__('Version').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('version')).'&nbsp;-&nbsp;'.
					__('Author(s)').'&nbsp;:&nbsp;'.html::escapeHTML($this->info('author')).
					($details ? '&nbsp;-&nbsp;<a href="'.$details.'">'.__('Details').'</a>' : '').
					($support ? '&nbsp;-&nbsp;<a href="'.$support.'">'.__('Support').'</a>' : '').'
				</p>
		';
	}

	### Widget functions ###

	/**
	 * widgetHeader
	 *
	 * @param object $w
	 * @param string $title
	 *
	 * @return void
	 */
	protected static function widgetHeader(&$w, $title) {
		$w->setting('title', __('Title (optional)').' :', $title);
	}

	/**
	 * widgetFooter
	 *
	 * @param object $w
	 * @param boolean $context
	 * @param string $class
	 *
	 * @return void
	 */
	protected static function widgetFooter(&$w, $context=true, $class='') {
		if($context) { $w->setting('homeonly', __('Display on:'), 0, 'combo', array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2)); }
		$w->setting('content_only', __('Content only'), 0, 'check');
		$w->setting('class', __('CSS class:'), $class);
		$w->setting('offline', __('Offline'), false, 'check');
	}

	/**
	 * widgetAddBasic
	 *
	 * @param object $w
	 * @param string $id
	 * @param string $name
	 * @param callback $callback
	 * @param mixed $help
	 * @param string $title
	 *
	 * @return void
	 */
	protected static function widgetAddBasic(&$w, $id, $name, $callback, $help, $title) {
		$w->create($id, $name, $callback, null, $help);
		self::widgetHeader($w->{$id}, $title);
		self::widgetFooter($w->{$id});
	}

	/**
	 * widgetRender
	 *
	 * @param object $w
	 * @param string $content
	 * @param string $class
	 * @param string $attr
	 *
	 * @return string
	 */
	protected static function widgetRender($w, $content, $class='', $attr='') {
		global $core;
		if (($w->homeonly == 1 && $core->url->type != 'default') || ($w->homeonly == 2 && $core->url->type == 'default') || $w->offline || empty($content)) {
			return;
		}
		$content = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').$content;
		return $w->renderDiv($w->content_only, trim(trim($class).' '.$w->class), trim($attr), $content);
	}

	### Common functions ###

	/**
	 * settings
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $scope
	 *
	 * @return mixed
	 */
	public final function settings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				if($scope == 'global' || $scope === true) {
					return $this->core->blog->settings->{$this->plugin_id}->getGlobal($key);
				} elseif($scope == 'local') {
					return $this->core->blog->settings->{$this->plugin_id}->getLocal($key);
				}
				return $this->core->blog->settings->{$this->plugin_id}->$key;
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings read error.('.$key.')');
				return null;
			}
		} else {
			try {
				$global = ($scope == 'global' || $scope === true);
				$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('Blog settings write error (namespace not exist).('.$key.')');
				$this->core->blog->settings->addNamespace($this->plugin_id);
				$this->core->blog->settings->{$this->plugin_id}->put($key, $value, null, null, true, $global);
			}
		}
	}

	/**
	 * userSettings
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $scope
	 *
	 * @return mixed
	 */
	public final function userSettings($key, $value=null, $scope='default') {
		if(is_null($value)) {
			try {
				if($scope == 'global' || $scope === true) {
					return $this->core->auth->user_prefs->{$this->plugin_id}->getGlobal($key);
				} elseif($scope == 'local') {
					return $this->core->auth->user_prefs->{$this->plugin_id}->getLocal($key);
				}
				return $this->core->auth->user_prefs->{$this->plugin_id}->$key;
			} catch(Exception $e) {
				$this->debugDisplay('User settings read error.('.$key.')');
				return null;
			}
		} else {
			try {
				$global = ($scope == 'global' || $scope === true);
				$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			} catch(Exception $e) {
				$this->debugDisplay('User settings write error (namespace not exist).('.$key.')');
				$this->core->auth->user_prefs->addWorkSpace($this->plugin_id);
				$this->core->auth->user_prefs->{$this->plugin_id}->put($key,$value, null, null, true, $global);
			}
		}
	}

	/**
	 * settingDrop
	 *
	 * @param mixed $key
	 *
	 * @return void
	 */
	protected final function settingDrop($key) {
		$s = new dcNamespace($this->core, null, $this->plugin_id);
		$s->drop($key);
		unset($s);
	}

	/**
	 * userSettingDrop
	 *
	 * @param mixed $key
	 *
	 * @return void
	 */
	protected final function userSettingDrop($key) {
		$s = new dcWorkspace($this->core, $this->core->auth->userID(), $this->plugin_id);
		$s->drop($key);
		unset($s);
	}

	/**
	 * info
	 *
	 * @param string $item
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public final function info($item=null, $default=null) {
		if(empty($item) || $item == 'id') {
			return $this->plugin_id;
		} elseif($item == 'adminUrl') {
			return (defined('DC_CONTEXT_ADMIN') ? $this->admin_url : null);
		} elseif($item == 'helperVersion') {
			return self::VERSION;
		} else {
			$res = $this->core->plugins->moduleInfo($this->plugin_id, $item);
			return $res === null ? $default : $res;
		}
	}

	/**
	 * nextStep
	 *
	 * @param string $step
	 * @param integer $delay
	 *
	 * @return void
	 */
	public function nextStep($step, $delay=0) {
		$timeout = $_SERVER['REQUEST_TIME'] + ini_get('max_execution_time') - 1;
		//$timeout = $_SERVER['REQUEST_TIME'] + 30 - 1;								# for debug
		if($delay > 0 && ($timeout - $delay) < time()) { return; }					# if timeout > next task delay
		if($delay < 0 && ($timeout + $delay) > time()) { return; }					# if timeout - delay < now

		# --BEHAVIOR-- beforeNextStep
		if($this->core->callBehavior('beforeNextStep', $this->core, $this->plugin_id, $step) === false) { return; }

		if(is_array($step)) {
			foreach($step as $k => $v) { $_GET[$k] = $v; }
		} elseif(!empty($step)) {
			$_GET['step'] = $step;
		}
		$url = basename(parse_url(http::getSelfURI(), PHP_URL_PATH)).'?'.http_build_query($_GET,'','&');
		$this->debugLog('nextStep', $url);
		http::redirect($url);
	}

	/**
	 * getVarDir
	 *
	 * @param string $dir
	 * @param boolean $create
	 *
	 * @return string
	 */
	protected static function getVarDir($dir='', $create=false) {
		$dir = trim($dir, '\\/');
		$var_dir = path::real(DC_VAR.(empty($dir) ? '' : '/'.$dir), false);
		if(strpos($var_dir, path::real(DC_VAR, false)) === false) { $GLOBALS['core']->error->add(__('The folder is not in the var directory')); }
		if(!is_dir($var_dir)) {
			if(!$create) { return false; }
			@files::makeDir($var_dir, true);
			if(!is_dir($var_dir)) { $GLOBALS['core']->error->add(__('Creating a var directory failed')); }
		}
		return $var_dir;
	}

	/**
	 * getVF
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	protected final function getVF($file) {
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::getVF($file);
		} else {
			return $this->core->blog->getVF($file);
		}
	}

	/**
	 * jsLoad
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	protected final function jsLoad($src) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsLoad(dcPage::getPF($file), $version);
		} else {
			return dcUtils::jsLoad($this->core->blog->getPF($file), $version);
		}
	}

	/**
	 * cssLoad
	 *
	 * @param string $src
	 * @param string $media
	 * @param boolean $import
	 *
	 * @return string
	 */
	protected final function cssLoad($src, $media='all', $import=false) {
		$file = $this->plugin_id.'/'.ltrim($src, '/');
		$version = $this->info('version');
		if(defined('DC_CONTEXT_ADMIN')) {
			if($import) {
				return	'<style type="text/css">@import url('.dcPage::getPF($file).') '.$media.';</style>'.NL;
			} else {
				return dcPage::cssLoad(dcPage::getPF($file), $media, $version);
			}
		} else {
			if($import) {
				return	'<style type="text/css">@import url('.$this->core->blog->getPF($file).') '.$media.';</style>'.NL;
			} else {
				return dcUtils::cssLoad($this->core->blog->getPF($file), $media, $version);
			}
		}
	}

	/**
	 * jsJson
	 *
	 * @param array $vars
	 *
	 * @return string
	 *
	 * @see https://open-time.net/post/2018/11/07/PHP-Javascript-CSS
	 */
	public final function jsJson($vars) {
		if(defined('DC_CONTEXT_ADMIN')) {
			return dcPage::jsJson($this->plugin_id, $vars);
		} else {
			return dcUtils::jsJson($this->plugin_id, $vars);
		}
	}

	### debug functions ###

	/**
	 * debugDisplay
	 *
	 * @param string $msg
	 *
	 * @return void
	 */
	protected final function debugDisplay($msg) {
		if($this->debug_mode && !empty($msg)) {
			if(defined('DC_CONTEXT_ADMIN')) { $this->core->notices->addWarningNotice('<strong>DEBUG - '.$this->plugin_id.'</strong>&nbsp;:&nbsp;'.$msg); }
			$this->debugLog('[Debug display]', $msg);
		}
	}

	/**
	 * debugLog
	 *
	 * @param string $text
	 * @param mixed $value
	 *
	 * @return void
	 */
	public final function debugLog($text, $value=null) {
		if($this->debug_log && !empty($text)) {
			if(empty($this->debug_logfile)) { $this->setDebugFilename(); }				# initialization
			$cmd = array('START_DEBUG', 'END_DEBUG');
			if(in_array(strtoupper($text), $cmd)) {
				$text = str_pad('**'.strtoupper($text), 66,'*');
			} elseif(is_bool($value)) {
				$text .= ' : '.($value ? 'True' : 'False');
			} elseif(is_numeric($value)) {
				$text .= ' : '.$value;
			} elseif(is_string($value)) {
				if(strpos($value, NL) === false) {
					$text .= ' : '.$value;
				} else {
					$text .= ' :'.NL.$value.NL.str_pad('END_VALUE', 66, '*');
				}
			} elseif(is_null($value)) {
				$text .= ' : <null>';
			} elseif(empty($value)) {
				$text .= ' : <empty>';
			} else {
				$text .= ' :'.NL.print_r($value, true).NL.str_pad('END_VALUE', 66, '*');
			}
			@file_put_contents ($this->debug_logfile, NL.'['.date('YmdHis').'-'.$this->plugin_id.'-'.$this->core->blog->id.'] '.$text, FILE_APPEND);
		}
	}

	/**
	 * setDebugFilename
	 *
	 * @param mixed $filename
	 * @param boolean $reset_file
	 *
	 * @return void
	 */
	public final function setDebugFilename($filename=null, $reset_file=false) {
		if(empty($filename)) { $filename = self::getVarDir('logs', true).'/log_'.$this->plugin_id.'.txt'; }
		if(!empty($this->debug_logfile)) { $this->debugLog('Change to file', $filename); }
		if(is_dir(dirname($filename))) {
			$this->debug_logfile = $filename;
		} else {
			$this->debug_logfile = self::getVarDir('logs', true).'/'.basename($filename);
		}
		if($this->debug_log) {
			if($this->debug_log_reset && $reset_file && is_file($this->debug_logfile)) {
				@unlink($this->debug_logfile);
			} else {
				@file_put_contents ($this->debug_logfile, NL, FILE_APPEND);
			}
		}
	}

}
