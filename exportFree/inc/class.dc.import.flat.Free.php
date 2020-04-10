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

class dcImportFlatFree extends dcIeModule {
	protected $status = false;

	public function setInfo() {
		$this->type = 'import';
		$this->name = __('Import Flat files folder');
		$this->description = __('Import a blog or a full install Dotclear from a flat files folder.');
	}

	public function process($do) {
		if($do == 'single_done' || $do == 'full_done') {
			$this->status = $do;
			return;
		}

		if($do == 'full' && $this->core->auth->isSuperAdmin() && (empty($_POST['your_pwd']) || !$this->core->auth->checkPassword($this->core->auth->crypt($_POST['your_pwd'])))) {
			throw new Exception(__('Password verification failed'));
		}
		$path = null;
		if(!empty($_POST['public_single_dir'])) { $path = $_POST['public_single_dir']; }
		if(!empty($_POST['public_full_dir'])) { $path = $_POST['public_full_dir']; }

		$k = $this->core->exportFree->settings('taskTimeIncrease');

		# Single blog import
		if($do == 'single') {
			try {
				$bk = new flatImportExtend($this->core, $path);
				$bk->importSingle();
				while($bk->nextFile()) {
					$timeStart = time();
					$bk->importSingle();
					$this->core->exportFree->nextStep(array('do' => 'single'), (integer)ceil((time() - $timeStart) * $k));
				}
				unset($bk);
				unset($_SESSION['dcImport']);
				http::redirect($this->getURL().'&do=single_done');
			} catch (Exception $e) {
				unset($bk);
				unset($_SESSION['dcImport']);
				throw $e;
			}
		}

		# Full import
		if($do == 'full') {
			try {
				$bk = new flatImportExtend($this->core, $path);
				if($bk->inProgress()) { $bk->importSingle(); } else { $bk->importFull(); }
				while($bk->nextFile()) {
					$timeStart = time();
					$bk->importSingle();
					$this->core->exportFree->nextStep(array('do' => 'full'), (integer)ceil((time() - $timeStart) * $k));
				}
				unset($bk);
				unset($_SESSION['dcImport']);
				http::redirect($this->getURL().'&do=full_done');
			} catch (Exception $e) {
				unset($bk);
				unset($_SESSION['dcImport']);
				throw $e;
			}
		}

		header('content-type:text/plain');
		var_dump($_POST);
		var_dump($_SESSION['dcImport']);
		unset($_SESSION['dcImport']);
		exit;

		$this->status = true;
	}

	public function gui() {
		if ($this->status == 'single_done') {
			dcPage::success(__('Single blog successfully imported.'));
			return;
		}
		if ($this->status == 'full_done') {
			dcPage::success(__('Content successfully imported.'));
			return;
		}

		$public_files = array_merge(array('-' => ''), $this->getPublicFiles());
		$has_files = (boolean) (count($public_files) - 1);

		if ($has_files) {
			echo
				'<script type="text/javascript">'."\n".
				"//<![CDATA[\n".
				dcPage::jsVar('dotclear.msg.confirm_full_import',
					__('Are you sure you want to import a full backup file?')).
				"$(function() {".
					"$('#formfull').submit(function() { ".
						"return window.confirm(dotclear.msg.confirm_full_import); ".
					"}); ".
				"});\n".
				"//]]>\n".
				"</script>\n";

			echo
				'<form action="'.$this->getURL(true).'" method="post" enctype="multipart/form-data" class="fieldset">'.
				'<h3>'.__('Single blog').'</h3>'.
				'<p>'.sprintf(__('This will import a single blog backup as new content in the current blog: <strong>%s</strong>.'),html::escapeHTML($this->core->blog->name)).'</p>'.
				'<p><label for="public_single_dir" class="">'.__('or pick up a local file in your public directory').' </label> '.form::combo('public_single_dir', $public_files).'</p>'.
				'<p>'.$this->core->formNonce().form::hidden(array('do'), 'single').'<input type="submit" value="'.__('Import').'" /></p>'.
				'</form>';

			if ($this->core->auth->isSuperAdmin()) {
				echo
					'<form action="'.$this->getURL(true).'" method="post" enctype="multipart/form-data" id="formfull" class="fieldset">'.
					'<h3>'.__('Multiple blogs').'</h3>'.
					'<p class="warning">'.__('This will reset all the content of your database, except users.').'</p>'.
					'<p><label for="public_full_dir">'.__('or pick up a local file in your public directory').' </label>'.form::combo('public_full_dir', $public_files).'</p>'.
					'<p><label for="your_pwd" class="required"><abbr title="'.__('Required field').'">*</abbr> '.__('Your password:').'</label>'.form::password('your_pwd', 20, 255).'</p>'.
					'<p>'.$this->core->formNonce().form::hidden(array('do'), 'full').'<input type="submit" value="'.__('Import').'" /></p>'.
					'</form>';
			}
		} else {
			dcPage::warning(__('No backup directory found.'));
			return;
		}
	}

	protected function getPublicFiles() {
		$public_files = array();
		$dir = @dir($this->core->blog->public_path);
		if ($dir) {
			while (($entry = $dir->read()) !== false) {
				$entry_path = $dir->path.'/'.$entry;
				if(is_dir($entry_path)) {
					$entry_file = $entry_path.'/'.exportFree::getFilename(0, $this->core->exportFree->settings('filePrefix'));
					if(is_readable($entry_file) && self::checkFileContent($entry_file)) {
						$public_files[$entry] = $entry_path;
					}
				}
			}
		}
		if(is_dir($this->core->blog->public_path.'/'.$this->core->exportFree->settings('directory'))) {
			$dir = @dir($this->core->blog->public_path.'/'.$this->core->exportFree->settings('directory'));
			if ($dir) {
				while (($entry = $dir->read()) !== false) {
					$entry_path = $dir->path.'/'.$entry;
					if(is_dir($entry_path)) {
						$entry_file = $entry_path.'/'.exportFree::getFilename(0, $this->core->exportFree->settings('filePrefix'));
						if(is_readable($entry_file) && self::checkFileContent($entry_file)) {
							$public_files[$entry] = $entry_path;
						}
					}
				}
			}
		}
		return $public_files;
	}

	protected static function checkFileContent($entry_path) {
		$ret = false;

		$fp = fopen($entry_path,'rb');
		$ret = strpos(fgets($fp),'///DOTCLEAR|') === 0;
		fclose($fp);

		return $ret;
	}

}
