<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
?>
<div class="redshopb-user-fields">
	<div class="row-fluid fields-content">
		<div class="span12">
			<?php echo RedshopbLayoutHelper::render('fields.fields',
				array(
					'form'     => $this->form,
					'formName' => 'fieldsForm',
					'scope'    => 'user',
					'task'     => 'user.saveFields',
					'itemId'   => $this->item->id,
					'action'   => RedshopbRoute::_('index.php?option=com_redshopb&view=user&layout=edit&id=' . $this->item->id),
					'return'   => base64_encode('index.php?option=com_redshopb&view=user&layout=edit&tab=fields&id=' . $this->item->id))
			);?>
		</div>
	</div>
</div>
