<?php
/**
  * This file is part of exportFree plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\exportFree
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2003-2012 Olivier Meunier & Association Dotclear
  * @copyright © 2015-2020 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if (!defined('DC_CONTEXT_ADMIN')) { return; }

class FreeMaintenanceExportblog extends dcMaintenanceTask {
	protected $perm = 'admin';
	protected $tab = 'backup';
	protected $group = 'zipblog';

	protected $export_name;
	protected $export_type;

	protected function init() {
		$this->name = __('Database export for Free website');
		$this->task = __('Download database of current blog for Free website');

		$this->export_name = html::escapeHTML($this->core->blog->id.'-backup');
		$this->export_type = 'export_blog';
	}

	public function execute() {
		// Create zip file
		if (!empty($_POST['file_name'])) {
			// This process make an http redirect
			$ie = new maintenanceDcExportFlatFree($this->core);
			$ie->setURL($this->id);
			$ie->process($this->export_type);
		}
		// Go to step and show form
		else {
			return 1;
		}
	}

	public function step() 	{
		// Download zip file
		if (isset($_SESSION['dcExport']['directory']) && file_exists($_SESSION['dcExport']['directory'])) {
			// Log task execution here as we sent file and stop script
			$this->log();

			// This process send file by http and stop script
			$ie = new maintenanceDcExportFlatFree($this->core);
			$ie->setURL($this->id);
			$ie->process('ok');
		} else {
			$res = '<p><label for="file_name">'.__('Directory name:').'</label>'.form::field('file_name', 50, 255, date('Y-m-d-H-i-').$this->export_name).'</p>';
			if($this->core->exportFree->settings('mode') != dcExportFlatFree::modeDirect) {
				$res .=	'<p><label for="deleteFiles" class="classic">'.
							form::checkbox('deleteFiles', 1, $this->core->exportFree->settings('deleteFiles')).' '.__('Delete temporary files').
						'</label></p>';
			}
			$res .=	'<p class="hidden"><label for="file_zip" class="classic">'.
						form::checkbox('file_zip', 1, $this->core->exportFree->settings('mode') != dcExportFlatFree::modeDirect).' '.__('Compress file').
					'</label></p>';
			return $res;
		}
	}
}

class FreeMaintenanceExportfull extends dcMaintenanceTask
{
	protected $tab = 'backup';
	protected $group = 'zipfull';

	protected $export_name;
	protected $export_type;

	protected function init() {
		$this->name = __('Database export for Free website');
		$this->task = __('Download database of all blogs for Free website');

		$this->export_name = 'dotclear-backup';
		$this->export_type = 'export_all';
	}

	public function execute() {
		// Create zip file
		if (!empty($_POST['file_name'])) {
			// This process make an http redirect
			$ie = new maintenanceDcExportFlatFree($this->core);
			$ie->setURL($this->id);
			$ie->process($this->export_type);
		}
		// Go to step and show form
		else {
			return 1;
		}
	}

	public function step() {
		// Download zip file
		if (isset($_SESSION['dcExport']['directory']) && file_exists($_SESSION['dcExport']['directory'])) {
			// Log task execution here as we sent file and stop script
			$this->log();

			// This process send file by http and stop script
			$ie = new maintenanceDcExportFlatFree($this->core);
			$ie->setURL($this->id);
			$ie->process('ok');
		} else {
			$res = '<p><label for="file_name">'.__('Directory name:').'</label>'.form::field('file_name', 50, 255, date('Y-m-d-H-i-').$this->export_name).'</p>';
			if($this->core->exportFree->settings('mode') != dcExportFlatFree::modeDirect) {
				$res .=	'<p><label for="deleteFiles" class="classic">'.
							form::checkbox('deleteFiles', 1, $this->core->exportFree->settings('deleteFiles')).' '.__('Delete temporary files').
						'</label></p>';
			}
			$res .=	'<p class="hidden"><label for="file_zip" class="classic">'.
						form::checkbox('file_zip', 1, $this->core->exportFree->settings('mode') != dcExportFlatFree::modeDirect).' '.__('Compress file').
					'</label></p>';
			return $res;
		}
	}
}

class maintenanceDcExportFlatFree extends dcExportFlatFree
{
	/**
	 * Set redirection URL of bakcup process.
	 *
	 * Bad hack to change redirection of dcExportFlat::process()
	 *
	 * @param	id	<b>string</b>	Task id
	 */
	public function setURL($id)
	{
		$this->url = sprintf('plugin.php?p=maintenance&task=%s', $id);
	}
}
