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

class dcExportFlatFree extends dcIeModule {
	# Functionnal modes
	const modeDirect		= 1;
	const modeIndirect		= 2;
	const modeMultiFiles	= 3;

	# Export type
	const typeFull		= 'full';
	const typeSingle	= 'single';

	public function setInfo() {
		$this->type = 'export';
		$this->name = __('Flat file export for Free');
		$this->description = __('Exports a blog or a full Dotclear installation to flat file.');
	}

	public function process($do) {
		# Export a blog
		if ($do == 'export_blog' && $this->core->auth->check('admin',$this->core->blog->id)) {

			$blog_id = $this->core->con->escape($this->core->blog->id);

			try {
				ob_start();

				if($this->core->exportFree->settings('mode') == self::modeDirect) {
					$fullname = 'php://output';
					$exp = new flatExport($this->core->con,$fullname,$this->core->prefix);
					fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION.'|'.self::$typeSingle."\n");
				} else {
					$fullname = $this->core->blog->public_path.'/'.$this->core->exportFree->settings('directory').'/'.$_POST['file_name'];
					$exp = new flatExportExtend($this->core->con, $fullname, $this->core->prefix, self::typeSingle);
				}

				$exp->export('category',
					'SELECT * FROM '.$this->core->prefix.'category '.
					"WHERE blog_id = '".$blog_id."'"
				);
				$exp->export('link',
					'SELECT * FROM '.$this->core->prefix.'link '.
					"WHERE blog_id = '".$blog_id."'"
				);
				$exp->export('setting',
					'SELECT * FROM '.$this->core->prefix.'setting '.
					"WHERE blog_id = '".$blog_id."'"
				);
				$exp->export('post',
					'SELECT * FROM '.$this->core->prefix.'post '.
					"WHERE blog_id = '".$blog_id."'"
				);
				$exp->export('meta',
					'SELECT meta_id, meta_type, M.post_id '.
					'FROM '.$this->core->prefix.'meta M, '.$this->core->prefix.'post P '.
					'WHERE P.post_id = M.post_id '.
					"AND P.blog_id = '".$blog_id."'"
				);
				$exp->export('media',
					'SELECT * FROM '.$this->core->prefix."media WHERE media_path = '".
					$this->core->con->escape($this->core->blog->settings->system->public_path)."'"
				);
				$exp->export('post_media',
					'SELECT media_id, M.post_id '.
					'FROM '.$this->core->prefix.'post_media M, '.$this->core->prefix.'post P '.
					'WHERE P.post_id = M.post_id '.
					"AND P.blog_id = '".$blog_id."'"
				);
				$exp->export('ping',
					'SELECT ping.post_id, ping_url, ping_dt '.
					'FROM '.$this->core->prefix.'ping ping, '.$this->core->prefix.'post P '.
					'WHERE P.post_id = ping.post_id '.
					"AND P.blog_id = '".$blog_id."'"
				);
				$exp->export('comment',
					'SELECT C.* '.
					'FROM '.$this->core->prefix.'comment C, '.$this->core->prefix.'post P '.
					'WHERE P.post_id = C.post_id '.
					"AND P.blog_id = '".$blog_id."'"
				);

				# --BEHAVIOR-- exportSingle
				$this->core->callBehavior('exportSingle',$this->core,$exp,$blog_id);

				if($this->core->exportFree->settings('mode') == self::modeDirect) {
					header('Content-Disposition: attachment;filename='.$_POST['file_name']);
					header('Content-Type: text/plain; charset=UTF-8');
					ob_end_flush();
					exit;
				} elseif($this->core->exportFree->settings('mode') == self::modeIndirect) {
					$_SESSION['dcExport'] = array(
						'directory'		=> $fullname
						, 'filesCount'	=> $exp->getFilesCount()
						, 'fileBase'	=> $this->core->exportFree->settings('filePrefix')
						, 'filename'	=> $_POST['file_name']
						, 'fileZip'		=> !empty($_POST['file_zip'])
						, 'deleteFiles' => !empty($_POST['deleteFiles'])
					);
					http::redirect($this->getURL().'&do=ok');
				} elseif($this->core->exportFree->settings('mode') == self::modeMultiFiles) {
					$_SESSION['dcExport'] = array(
						'directory'		=> $fullname
						, 'filesCount'	=> $exp->getFilesCount()
						, 'fileBase'	=> $this->core->exportFree->settings('filePrefix')
						, 'filename'	=> $_POST['file_name']
						, 'fileZip'		=> !empty($_POST['file_zip'])
						, 'deleteFiles' => !empty($_POST['deleteFiles'])
					);
					http::redirect($this->getURL().'&do=ok');
				} else {
					// unknown mode
				}
			} catch (Exception $e) {
				files::deltree($fullname);
				throw $e;
			}
		}

		# Export all content
		if ($do == 'export_all' && $this->core->auth->isSuperAdmin()) {

			try {
				ob_start();

				if($this->core->exportFree->settings('mode') == self::modeDirect) {
					$fullname = 'php://output';
					$exp = new flatExport($this->core->con,$fullname,$this->core->prefix);
					fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION.'|'.self::$typeFull."\n");
				} else {
					$fullname = $this->core->blog->public_path.'/'.$this->core->exportFree->settings('directory').'/'.$_POST['file_name'];
					$exp = new flatExportExtend($this->core->con, $fullname, $this->core->prefix, self::typeFull);
				}

				$exp->exportTable('blog');
				$exp->exportTable('category');
				$exp->exportTable('link');
				$exp->exportTable('setting');
				$exp->exportTable('user');
				$exp->exportTable('pref');
				$exp->exportTable('permissions');
				$exp->exportTable('post');
				$exp->exportTable('meta');
				$exp->exportTable('media');
				$exp->exportTable('post_media');
				$exp->exportTable('log');
				$exp->exportTable('ping');
				$exp->exportTable('comment');
				$exp->exportTable('spamrule');
				$exp->exportTable('version');

				# --BEHAVIOR-- exportFull
				$this->core->callBehavior('exportFull',$this->core,$exp);

				if($this->core->exportFree->settings('mode') == self::modeDirect) {
					header('Content-Disposition: attachment;filename='.$_POST['file_name']);
					header('Content-Type: text/plain; charset=UTF-8');
					ob_end_flush();
					exit;
				} elseif($this->core->exportFree->settings('mode') == self::modeIndirect) {
					$_SESSION['dcExport'] = array(
						'directory'		=> $fullname
						, 'filesCount'	=> $exp->getFilesCount()
						, 'fileBase'	=> $this->core->exportFree->settings('filePrefix')
						, 'filename'	=> $_POST['file_name']
						, 'fileZip'		=> !empty($_POST['file_zip'])
						, 'deleteFiles' => !empty($_POST['deleteFiles'])
					);
					http::redirect($this->getURL().'&do=ok');
				} elseif($this->core->exportFree->settings('mode') == self::modeMultiFiles) {
					$_SESSION['dcExport'] = array(
						'directory'		=> $fullname
						, 'filesCount'	=> $exp->getFilesCount()
						, 'fileBase'	=> $this->core->exportFree->settings('filePrefix')
						, 'filename'	=> $_POST['file_name']
						, 'fileZip'		=> !empty($_POST['file_zip'])
						, 'deleteFiles' => !empty($_POST['deleteFiles'])
					);
					http::redirect($this->getURL().'&do=ok');
				} else {
					// unknown mode
				}
			} catch (Exception $e) {
				throw $e;
			}
		}

		# Send file content
		if ($do == 'ok') {
			if (!file_exists($_SESSION['dcExport']['directory'])) {
				throw new Exception(__('Export file not found.'));
			}

			ob_end_clean();

			if (substr($_SESSION['dcExport']['filename'],-4) == '.zip') {
				$_SESSION['dcExport']['filename'] = substr($_SESSION['dcExport']['filename'],0,-4);//.'.txt';
			}

			if (empty($_SESSION['dcExport']['fileZip'])) {
				# Flat export

				header('Content-Disposition: attachment;filename='.$_SESSION['dcExport']['filename']);
				header('Content-Type: text/plain; charset=UTF-8');
				$filebase = $_SESSION['dcExport']['directory'].'/'.$_SESSION['dcExport']['fileBase'];
				for($i = 0; $i <= $_SESSION['dcExport']['filesCount']; $i++) {
					readfile(flatExportExtend::getFilename($i, $filebase));
				}

			} else {
				# Zip export
				try {
					$file_zipname = $_SESSION['dcExport']['filename'].'.zip';

					$fp = fopen('php://output','wb');
					$zip = new fileZip($fp);
					if($this->core->exportFree->settings('mode') == self::modeIndirect) {
						$zip->addDirectory($_SESSION['dcExport']['directory'], $_SESSION['dcExport']['filename'], true);
					}elseif($this->core->exportFree->settings('mode') == self::modeMultiFiles) {
						$zip->addDirectory($_SESSION['dcExport']['directory'], $_SESSION['dcExport']['filename'], true);
					}

					header('Content-Disposition: attachment;filename='.$file_zipname);
					header('Content-Type: application/x-zip');

					$zip->write();

				} catch (Exception $e) {
					if($_SESSION['dcExport']['deleteFiles']) {
						files::deltree($_SESSION['dcExport']['directory']);
					}
					unset($_SESSION['dcExport']);
					throw new Exception(__('Failed to compress export file.'));
				}
			}
			if($_SESSION['dcExport']['deleteFiles']) {
				files::deltree($_SESSION['dcExport']['directory']);
			}
			unset($_SESSION['dcExport']);
			exit;
		}

	}

	public function gui() {

	}
}
