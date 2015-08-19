<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('dcMaintenanceInit', array('exportFree','MaintenanceInit'));

//$core->error->add('test erreur');
//$core->error->add('test erreur posts','posts');
//$core->error->add('test erreur comments','comments');
//$core->error->add('test erreur blogs','blogs');
//$core->error->add('test erreur users','users');
//$core->error->add('test erreur pages','pages');
