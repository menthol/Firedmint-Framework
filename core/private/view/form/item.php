<?php 
if (!defined('FM_SECURITY')) die();
?>
<div class="input-item input-<?php show($view->data['element'],true);?>">
<?php show($view->data['input']);?> <label for="<?php show($view->data['id'],true);?>"><?php l($view->data['label']);?></label>
</div>

