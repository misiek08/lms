<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2013 LMS Developers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 *  USA.
 *
 *  $Id$
 */

if (isset($_GET['nodegroups'])) {
	$nodegroups = $LMS->GetNodeGroupNamesByNode(intval($_GET['id']));

	$SMARTY->assign('nodegroups', $nodegroups);
	$SMARTY->assign('total', sizeof($nodegroups));
	$SMARTY->display('nodegrouplistshort.html');
	die;
}

if (!preg_match('/^[0-9]+$/', $_GET['id'])) {
	$SESSION->redirect('?m=nodelist');
}
else
	$nodeid = $_GET['id'];

if (!$LMS->NodeExists($nodeid)) {
	if (isset($_GET['ownerid']))
		$SESSION->redirect('?m=customerinfo&id=' . $_GET['ownerid']);
	else if ($DB->GetOne('SELECT 1 FROM nodes WHERE id = ? AND ownerid = 0', array($nodeid)))
		$SESSION->redirect('?m=netdevinfo&ip=' . $nodeid . '&id=' . $LMS->GetNetDevIDByNode($nodeid));
	else
		$SESSION->redirect('?m=nodelist');
}

if (isset($_GET['devid'])) {
	$error['netdev'] = trans('It scans for free ports in selected device!');
	$SMARTY->assign('error', $error);
	$SMARTY->assign('netdevice', $_GET['devid']);
}

$nodeinfo = $LMS->GetNode($nodeid);
$nodegroups = $LMS->GetNodeGroupNamesByNode($nodeid);
$othernodegroups = $LMS->GetNodeGroupNamesWithoutNode($nodeid);
$customerid = $nodeinfo['ownerid'];

include(MODULES_DIR . '/customer.inc.php');

$SESSION->save('backto', $_SERVER['QUERY_STRING']);

if (!isset($_GET['ownerid']))
	$SESSION->save('backto', $SESSION->get('backto') . '&ownerid=' . $customerid);

if ($nodeinfo['netdev'] == 0)
	$netdevices = $LMS->GetNetDevNames();
else
	$netdevices = $LMS->GetNetDev($nodeinfo['netdev']);

$layout['pagetitle'] = trans('Node Info: $a', $nodeinfo['name']);

include(MODULES_DIR . '/nodexajax.inc.php');

$nodeinfo = $LMS->ExecHook('node_info_init', $nodeinfo);

$SMARTY->assign('xajax', $LMS->RunXajax());

$SMARTY->assign('nodesessions', $LMS->GetNodeSessions($nodeid));
$SMARTY->assign('netdevices', $netdevices);
$SMARTY->assign('nodegroups', $nodegroups);
$SMARTY->assign('othernodegroups', $othernodegroups);
$SMARTY->assign('nodeinfo', $nodeinfo);
$SMARTY->assign('objectid', $nodeinfo['id']);
$SMARTY->display('nodeinfo.html');

?>
