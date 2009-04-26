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
 * @version     $Id: layout.php,v 1.2 2004/12/29 15:13:50 blindman1344 Exp $
 */

/* Adding a content variables for display in the body */
$layout_info[] = array ('content_var'=>'CNT_featuredphoto',
			'transfer_var'=>'body');
			
$layout_info[] = array ('content_var'=>'CNT_featuredphoto_pub',
			'transfer_var'=>'right_col_top');

?>