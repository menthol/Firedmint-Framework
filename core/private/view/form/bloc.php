<?php 
if (!defined('FM_SECURITY')) die();
?>
<div class="input-bloc input-<?php show($view->data['element'],true);?>">
<label for="<?php show($view->data['id'],true);?>"><?php l($view->data['label']);?></label>
<?php show($view->data['input']);?>
</div>

