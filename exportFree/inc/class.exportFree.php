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

if(!isset($__autoload['dcPluginHelper216'])) { $__autoload['dcPluginHelper216'] = dirname(__FILE__).'/class.dcPluginHelper.php'; }

__('Export Free');									// plugin name
__('Export your blog host at Free');				// description plugin

class exportFree extends dcPluginHelper216 {

	/**
	 * MaintenanceInit
	 *
	 * @param  object $maintenance
	 * @return void
	 */
	public static function MaintenanceInit($maintenance) {
		$maintenance
			->addTask('FreeMaintenanceExportblog')
			->addTask('FreeMaintenanceExportfull')
		;
	}

	/**
	 * ieRegisterModules
	 *
	 * @param  object $modules
	 * @param  object $core
	 * @return void
	 */
	public static function ieRegisterModules($modules, $core) {
		# export modules
		$modules['export'] = array_merge($modules['export'], array('dcExportFlatFree'));
		# import modules
		$modules['import'] = array_merge($modules['import'], array('dcImportFlatFree'));
	}

	protected function setDefaultSettings() {
		# create config plugin (TODO: specific settings)
		$upload_max_filesize = (integer)str_ireplace(array('K', 'M', 'G', 'T'), array('000', '000000', '000000000', '000000000000'), ini_get('upload_max_filesize'));
		$this->debugLog('upload_max_filesize', $upload_max_filesize);
		$filesize = (substr($_SERVER['SERVER_NAME'], -8) == '.free.fr' ? 1024000 : $upload_max_filesize);
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('mode', 3, 'integer', __('Functional mode'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('directory', '.backup', 'string', __('Directory backup'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('filePrefix', 'file_', 'string', __('Prefix for temporary files'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('fileSize', $filesize, 'integer', __('Max files size (in bytes)'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('deleteFiles', true, 'boolean', __('Delete temporary files'), false, true);
		$this->core->blog->settings->{$this->plugin_id}->put('taskTimeIncrease', 1.1, 'float', __('Task time increase'), false, true);
	}

	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->check('admin', $this->core->blog->id)) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				//$this->settings('enabled', !empty($_POST['enabled']), $scope);
				$this->settings('mode', html::escapeHTML($_POST['mode']), $scope);
				$this->settings('directory', html::escapeHTML($_POST['directory']), $scope);
				$this->settings('filePrefix', html::escapeHTML($_POST['prefix']), $scope);
				$this->settings('fileSize', (integer)$_POST['filesize'], $scope);
				$this->settings('deleteFiles', !empty($_POST['delete']), $scope);
				$this->core->blog->triggerBlog();
				dcPage::addSuccessNotice(__('Configuration successfully updated.'));
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
		$modes = array(
			__('Direct mode')					=> 1,
			__('Multiple files to concatenate')	=> 2,
			__('Multiple independent files')	=> 3
		);
		echo
			$this->configBaseline($scope, false).
			'<div class="fieldset">
				<h3>'.__('Parameters').'</h3>
				<p><label class="classic" for="mode">'.__('Functional mode').'&nbsp;:&nbsp;'.form::combo('mode', $modes, $this->settings('mode', null, $scope)).'</label></p>
				<p class="form-note">'.__('Define operating mode.').'</p>
				<p><label class="classic" for="directory">'.__('Backup directory').'&nbsp;:&nbsp;'.form::field('directory', 40, 255, $this->settings('directory', null, $scope)).'</label></p>
				<p class="form-note">'.__('Select backup directory in public directory.').'</p>
				<p><label class="classic" for="prefix">'.__('Filename prefix').'&nbsp;:&nbsp;'.form::field('prefix', 40, 255, $this->settings('filePrefix', null, $scope)).'</label></p>
				<p class="form-note">'.__('Define prefix of filenames.').'</p>
				<p><label class="classic" for="filesize">'.__('File size').'&nbsp;:&nbsp;'.form::field('filesize', 40, 255, $this->settings('fileSize', null, $scope)).'</label></p>
				<p class="form-note">'.__('Define maximal files size (in bytes). - Less than or equal to zero: unlimited').'</p>
				<p><label class="classic" for="delete">'.form::checkbox('delete','1',$this->settings('deleteFiles', null, $scope)).__('Delete files in server').'</label></p>
				<p class="form-note">'.__('Delete files in directory backup.').'</p>
			</div>
			<hr />
			'.$this->adminFooterInfo();

	}

	public static function getFilename($index, $prefix='', $length=4) {
		return $prefix.str_pad($index, $length, '0', STR_PAD_LEFT).'.txt';
	}

	protected function _prepend() {
		global $__autoload;
		# common (public & admin)

		if(defined('DC_CONTEXT_ADMIN')) {
			# admin only

			# loading of plugin class
			$__autoload['flatExportExtend'] = dirname(__FILE__).'/class.flat.export.extend.php';
			$__autoload['flatImportExtend'] = dirname(__FILE__).'/class.flat.import.extend.php';
			$__autoload['dcExportFlatFree'] = dirname(__FILE__).'/class.dc.export.flat.Free.php';
			$__autoload['dcImportFlatFree'] = dirname(__FILE__).'/class.dc.import.flat.Free.php';
			$__autoload['FreeMaintenanceExportblog'] = dirname(__FILE__).'/lib.export.Free.maintenance.php';
			$__autoload['FreeMaintenanceExportfull'] = dirname(__FILE__).'/lib.export.Free.maintenance.php';

		} else {
			# public only

		}
	}

	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }

		$this->core->addBehavior('importExportModules', array('exportFree', 'ieRegisterModules'));
		$this->core->addBehavior('dcMaintenanceInit', array('exportFree', 'MaintenanceInit'));
	}

}
