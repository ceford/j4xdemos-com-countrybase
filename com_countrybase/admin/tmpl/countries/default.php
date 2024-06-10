<?php
/**
 * @package     Countrybase.Administrator
 * @subpackage  com_countrybase
 *
 * @copyright   (C) 2022 Clifford E Ford
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$user = Factory::getApplication()->getIdentity();

?>

<form action="<?php echo Route::_('index.php?option=com_countrybase'); ?>" method="post" name="adminForm"
	id="adminForm">

	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

	<div class="table-responsive">
		<table class="table table-striped">
			<caption>
				<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_TABLE_CAPTION'); ?>
			</caption>
			<thead>
				<tr>
					<td class="w-1 text-center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</td>
					<th scope="col" class="w-1 text-center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th scope="col">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_COUNTRYBASE_COUNTRIES_COUNTRY', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th scope="col">
						<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_ISO_2'); ?>
					</th>
					<th scope="col">
						<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_ISO_3'); ?>
					</th>
					<th scope="col">
						<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_CURRENCY_TITLE'); ?>
					</th>
					<th scope="col">
						<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_CURRENCY_CODE'); ?>
					</th>
					<th scope="col">
						<?php echo Text::_('COM_COUNTRYBASE_COUNTRIES_XRATE'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item):
					$canChange = $user->authorise('core.edit.state', 'com_cuntrybase.country.' . $item->id);
					?>
					<tr>
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
						</td>
						<td class="article-status text-center">
							<?php
							$options = [
								'task_prefix' => 'countries.',
								//'disabled' => $workflow_state || !$canChange,
								'id' => 'state-' . $item->id
							];

							echo (new PublishedButton)->render((int) $item->state, $i, $options);
							?>
						</td>
						<td>
							<a href="<?php echo Route::_('index.php?option=com_countrybase&task=country.edit&id=' . $item->id); ?>"
								title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
								<?php echo $this->escape($item->title); ?></a>
						</td>
						<td>
							<?php echo $item->iso_2; ?>
						</td>
						<td>
							<?php echo $item->iso_3; ?>
						</td>
						<td>
							<?php echo $item->currency_title; ?>
						</td>
						<td>
							<?php echo $item->currency_code; ?>
						</td>
						<td>
							<?php echo $item->dollar_exchange_rate; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php echo $this->pagination->getListFooter(); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="boxchecked" value="0">
	<?php echo HTMLHelper::_('form.token'); ?>

</form>