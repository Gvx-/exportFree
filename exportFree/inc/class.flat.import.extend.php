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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

class flatImportExtend extends flatImport {

	protected $core;
	protected $path;
	protected $prefix;
	protected $index;

	public function __construct($core, $path=null) {
		$this->core =& $core;
		$this->path = $path;
		$this->index = 0;

		# if not first step load context
		if(!empty($_SESSION['dcImport']['fileIndex'])) { $this->index = $_SESSION['dcImport']['fileIndex']; }
		if($this->index !=0 && !empty($_SESSION['dcImport']['path'])) { $this->path = $_SESSION['dcImport']['path']; }
		if(!empty($_SESSION['dcImport']['old_ids'])) { $this->old_ids = $_SESSION['dcImport']['old_ids']; }
		if(!empty($_SESSION['dcImport']['stack'])) { $this->stack = $_SESSION['dcImport']['stack']; }
		unset($_SESSION['dcImport']);

		if(!is_dir($this->path)) { throw new Exception(__('No backup directory selected')); }
		$this->prefix = $this->path.'/'.$this->core->exportFree->settings('filePrefix');
		$file = exportFree::getFilename($this->index++, $this->prefix);
		if(is_file($file) && !is_readable($file)) { throw new Exception(__("Backup file is unreadable")); }
		parent::__construct($core, $file);
		$this->core->addBehavior('beforeNextStep', array($this, 'beforeNextStep'));
		$this->core->exportFree->debugLog('Open', $file);
	}

	public function __destruct() {
		$this->closeFile();													# close current file
		parent::__destruct();
	}

	public function beforeNextStep($core, $origin, $step) {					# --BEHAVIOR-- beforeNextStep
		if($origin != $core->exportFree->info('id')) { return; }
		if(is_file(exportFree::getFilename($this->index, $this->prefix))) {
			# More files => save context
			$_SESSION['dcImport'] = array(
				'path'			=> $this->path
				, 'fileIndex'	=> $this->index
				, 'old_ids'		=> $this->old_ids
				, 'stack'		=> $this->stack
			);
		} else {
			# End import
			return false;
		}
	}

	public function inProgress() {
		return ($this->index != 0);
	}

	public function nextFile() {
		$this->closeFile();
		$file = exportFree::getFilename($this->index++, $this->prefix);
		if (is_file($file) && is_readable($file)) {
			$this->fp = fopen($file,'rb');
			$this->line_num = 1;
			$this->core->exportFree->debugLog('Open', $file);
		}
		return is_resource($this->fp);
	}

	protected function closeFile() {
		if ($this->fp) {
			fclose($this->fp);
			unset($this->fp);
			$this->core->exportFree->debugLog('Close file', '');
		}
	}

}
