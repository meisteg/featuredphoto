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
 * @version     $Id: boost.php,v 1.6 2006/03/06 03:05:38 blindman1344 Exp $
 */

$version = '0.4.0';
$mod_title = 'featuredphoto';
$mod_pname = 'Featured Photo';
$allow_view = array('home'=>1, 'featuredphoto'=>1);
$priority = 50;
$active = 'on';
$mod_class_files = array('manager.php');
$mod_directory = 'featuredphoto';
$mod_filename = 'index.php';
$admin_mod = TRUE;

?>