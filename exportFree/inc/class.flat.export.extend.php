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

if (!defined('DC_RC_PATH')) { return; }

class flatExportExtend extends flatExport {

	protected $file_prefix;
	protected $index = 0;
	protected $filesize = 0;
	protected $table_header;
	protected $con;

	public function __construct($con, $directory, $prefix=null, $type='full') {
		global $core;

		if(empty($directory)) {
			throw new Exception(__('Export directory not defined.'));
		}

		if(is_dir($directory)) { files::deltree($directory); }
		@mkdir($directory, 0755, true);
		if(!is_dir($directory)) {
			throw new Exception(__('Export directory not create.'));
		}

		$this->file_prefix = $directory.'/'.$core->exportFree->settings('filePrefix');
		parent::__construct($con, exportFree::getFilename($this->index++, $this->file_prefix), $prefix);
		$this->con =& $con;

		$this->fileWrite('///DOTCLEAR|'.DC_VERSION.'|'.$type."\n");
	}

	public function export($name, $sql) {

		$rs = $this->con->select($sql);

		if(!$rs->isEmpty()) {
				$this->table_header = "\n[".$name.' '.implode(',',$rs->columns())."]\n";
				$this->fileWrite($this->table_header);
			while ($rs->fetch()) {
				$this->fileWrite($this->getLine($rs));
			}
			fflush($this->fp);
		}
	}

	public function getFilesCount() {
		return $this->index - 1;
	}

	protected function fileWrite($value) {
		global $core;
		$filesize = $core->exportFree->settings('fileSize');
		if(is_resource($this->fp) && ($filesize > 0) && (($this->filesize + strlen($value)) >= $filesize)) {
			fflush($this->fp);
			fclose($this->fp);
			$this->fp = null;
		}
		if(!is_resource($this->fp)) {
			$this->filesize = 0;
			if(($this->fp = fopen(exportFree::getFilename($this->index++, $this->file_prefix), 'w')) === false) {
				throw new Exception(__('Export file not create.'));
			}
			if($core->exportFree->settings('mode') == dcExportFlatFree::modeMultiFiles && $this->index > 1) {
				$this->fileWrite('///DOTCLEAR|'.DC_VERSION."|single\n");
				$this->fileWrite($this->table_header);
			}
		}
		fwrite($this->fp, $value);
		$this->filesize += strlen($value);
	}

}
