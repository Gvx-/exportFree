<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Copyright (c) 2003-2012 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

class dcExportFlatFree extends dcIeModule
{
	public function setInfo()
	{
		$this->type = 'export';
		$this->name = __('Flat file export for Free');
		$this->description = __('Exports a blog or a full Dotclear installation to flat file.');
	}

	public function process($do)
	{
		# Export a blog
		if ($do == 'export_blog' && $this->core->auth->check('admin',$this->core->blog->id))
		{
			$fullname = 'php://output';
			$blog_id = $this->core->con->escape($this->core->blog->id);

			try
			{
				ob_start();

				$exp = new flatExport($this->core->con,$fullname,$this->core->prefix);
				fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION."|single\n");

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

				header('Content-Disposition: attachment;filename='.$_POST['file_name']);
				header('Content-Type: text/plain; charset=UTF-8');
				ob_end_flush();
				exit;
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}

		# Export all content
		if ($do == 'export_all' && $this->core->auth->isSuperAdmin())
		{
			$fullname = 'php://output';
			try
			{
				ob_start();
				$exp = new flatExport($this->core->con,$fullname,$this->core->prefix);
				fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION."|full\n");
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
			
				header('Content-Disposition: attachment;filename='.$_POST['file_name']);
				header('Content-Type: text/plain; charset=UTF-8');
				ob_end_flush();
				exit;
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}

	}

	public function gui()
	{
	}
}
