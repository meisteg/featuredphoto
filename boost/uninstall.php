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
 * @version     $Id: uninstall.php,v 1.2 2004/12/29 15:13:49 blindman1344 Exp $
 */

/* Make sure the user is a deity before running this script */
if(!$_SESSION['OBJ_user']->isDeity()){
  header('location:index.php');
  exit();
}

require_once(PHPWS_SOURCE_DIR . 'core/File.php');

/* Import the uninstall database file and dump the result into the status variable */
if($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/featuredphoto/boost/uninstall.sql', 1, 1)) {
  $content .= 'All featuredphoto tables successfully removed!<br /><br />';

  /* Check for images directory and remove if it exists */
  if(is_dir($GLOBALS['core']->home_dir . 'images/featuredphoto')) {
    $content .= 'Removing featuredphoto images directory at:<br />' . $GLOBALS['core']->home_dir . 'images/featuredphoto<br /><br />';
    PHPWS_File::rmdir($GLOBALS['core']->home_dir . 'images/featuredphoto/');
  } else {
    $content .= 'No images directory found for removal.<br /><br />';
  }

} else {
  $content .= 'There was a problem accessing the database.<br /><br />';
}

?>