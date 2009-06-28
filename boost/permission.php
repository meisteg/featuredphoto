<?php
/**
 * Copyright (c) 2004-2009 Gregory Meiste
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
 * @package FeaturedPhoto
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

$use_permissions = TRUE;

$permissions['edit_photos']   = dgettext('featuredphoto', 'Add/Edit Photos');
$permissions['delete_photos'] = dgettext('featuredphoto', 'Remove Photos from Blocks');
$permissions['hide_photos']   = dgettext('featuredphoto', 'Hide Photos');

$permissions['edit_blocks']   = dgettext('featuredphoto', 'Add/Edit Photo Blocks');
$permissions['delete_blocks'] = dgettext('featuredphoto', 'Delete Photo Blocks');
$permissions['hide_blocks']   = dgettext('featuredphoto', 'Hide Photo Blocks');
$permissions['pin_blocks']    = dgettext('featuredphoto', 'Pin Photo Blocks');

$permissions['edit_settings'] = dgettext('featuredphoto', 'Edit Settings');

$item_permissions = TRUE;

?>