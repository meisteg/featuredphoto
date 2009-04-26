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
 * @version     $Id: help.php,v 1.2 2006/03/08 04:05:28 blindman1344 Exp $
 */

$mode = 'Block Mode';
$mode_content = 'This setting allows you to specify how you want the next featured photo selected on the next page load (assuming you have more than one featured photo).<br /><br /><b>Increment</b><br />The next photo on the list will be selected.  If currently selected photo is the last photo on the list, the next photo will be the first.<br /><br /><b>Random</b><br />The next photo will be randomly selected from the list.<br /><br /><b>Fixed on the image</b><br />The selected photo will always be displayed.';

$photo_credit = 'Photo Credit';
$photo_credit_content = 'The name of the person who took the photograph or created the image.  This field is optional, but it is always best to give credit where credit is due.  :)';

$resize = 'Resize Images';
$resize_content = 'This allows you to set the dimensions in which all featured photos will fit inside.  The photos will maintain their aspect ratio during the resize.<br /><br />Note: You need the GD library installed for this feature to work.';

?>