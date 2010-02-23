<?php
if (!defined('FM_SECURITY')) die();
?>
<div id="layout">
	<div id="header">
		<?php $view->part('html/part/header',$this->data); ?>
	</div>
	<div id="menu">
		<?php $view->part('html/part/menu',$this->data); ?>
	</div>
	
	<div id="content-layout">	
		<div id="content">
			<?php $view->part('html/content',$this->data); ?>
		</div>

		<div id="content-menu">
			<?php $view->part('html/part/content-menu',$this->data); ?>
		</div>
	</div>

	<div id="footer">
		<?php $view->part('html/part/footer',$this->data); ?>
	</div>
</div>