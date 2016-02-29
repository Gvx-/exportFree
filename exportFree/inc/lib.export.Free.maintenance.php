<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015-2016 Gvx
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

class FreeMaintenanceExportblog extends dcMaintenanceTask
{
	protected $perm = 'admin';
	protected $tab = 'backup';
	protected $group = 'zipblog';

	protected $export_name;
	protected $export_type;

	protected function init()
	{
		$this->name = __('Database export for Free website');
		$this->task = __('Download database of current blog for Free website');

		$this->export_name = html::escapeHTML($this->core->blog->id.'-backup.txt');
		$this->export_type = 'export_blog';
	}

	public function execute()
	{
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

	public function step()
	{
		return
			'<p><label for="file_name">'.__('File name:').'</label>'.
			form::field('file_name', 50, 255, date('Y-m-d-H-i-').$this->export_name).
			'</p>';
	}
}

class FreeMaintenanceExportfull extends dcMaintenanceTask
{
	protected $tab = 'backup';
	protected $group = 'zipfull';

	protected $export_name;
	protected $export_type;

	protected function init()
	{
		$this->name = __('Database export for Free website');
		$this->task = __('Download database of all blogs for Free website');

		$this->export_name = 'dotclear-backup.txt';
		$this->export_type = 'export_all';
	}

	public function execute()
	{
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

	public function step()
	{
		return
			'<p><label for="file_name">'.__('File name:').'</label>'.
			form::field('file_name', 50, 255, date('Y-m-d-H-i-').$this->export_name).
			'</p>';
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
