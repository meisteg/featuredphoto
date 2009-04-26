<?php

/**
 * featuredphoto
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: update.php,v 1.8 2006/03/06 03:05:35 blindman1344 Exp $
 */

if (!$_SESSION['OBJ_user']->isDeity()){
    header('location:index.php');
    exit();
}

if (version_compare($GLOBALS['core']->version, '0.10.1') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.10.1 or greater to install.<br />';
    $content .= 'You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

// Update Language
require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
PHPWS_Language::uninstallLanguages('featuredphoto');
PHPWS_Language::installLanguages('featuredphoto');


if (version_compare($currentVersion, '0.1.1') < 0) {
    $content .= 'Featured Photo updates for version 0.1.1 <br />';
    $content .= '-------------------------------------------<br />';
    $content .= '+ Now compatible with upcoming version of phpWebSite<br />';
}

if (version_compare($currentVersion, '0.2.0') < 0) {
    $sql = 'ALTER TABLE mod_featuredphoto_photos ADD credit text AFTER caption';
    $GLOBALS['core']->query($sql, TRUE);

    $content .= 'Featured Photo updates for version 0.2.0 <br />';
    $content .= '-------------------------------------------<br />';
    $content .= '+ Photo credit added<br />';
    $content .= '+ Improved permissions<br />';
}

if (version_compare($currentVersion, '0.3.0') < 0) {
    $sql = "ALTER TABLE mod_featuredphoto_settings ADD (resize_width int(4) unsigned NOT NULL default '600', resize_height int(4) unsigned NOT NULL default '600')";
    $GLOBALS['core']->query($sql, TRUE);

    // Need to force the manager sesssion to reload so it picks up new settings.
    $_SESSION['FEATUREDPHOTO_Manager'] = NULL;

    $content .= 'Featured Photo updates for version 0.3.0 <br />';
    $content .= '-------------------------------------------<br />';
    $content .= '+ Now prevents against overwriting when multiple tabs/windows are open.<br />';
    $content .= '+ Fixed JavaScript error in add/edit form when invoking Spell Check<br />';
    $content .= '+ Automatic resize<br />';
}

if (version_compare($currentVersion, '0.4.0') < 0) {
    // Install Help
    require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
    CLS_help::setup_help('featuredphoto');
}

$status = 1;

?>