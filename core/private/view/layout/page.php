<?php
if (!defined('FM_SECURITY')) die();
?>
<div id="layout">
	<div id="header">
		<?php part('layout/header'); ?>
	</div>
	<div id="menu">
		<?php part('layout/menu'); ?>
	</div>
	
	<div id="content-layout">	
		<div id="content">
			<?php part("content/$view->name"); ?>
		</div>

		<div id="content-menu">
			<?php part('content/menu'); ?>
		</div>
	</div>

	<div id="footer">
		<?php part('layout/footer'); ?>
	</div>
</div>

