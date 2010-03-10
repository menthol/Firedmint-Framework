<?php 
if (!defined('FM_SECURITY')) die();
?>
<div class="form form-<?php show($view->data['formName']); ?>">
<form action="<?php show($view->data['formAction'],true); ?>" method="post">

<?php foreach (form::getGroupElements($view->data['formName']) as $element): ?>

<?php part("element/{$element['type']}",array('data'=>$element)); ?>

<?php endforeach; ?>

<?php foreach (form::getGroup($view->data['formName']) as $group): ?>
<fieldset class="<?php show($group,true)?>">
<?php
$groupInfo = form::getGroupInfo($view->data['formName'],$group);
if (!empty($groupInfo['legend'])): ?><legend><?php l($groupInfo['legend']); ?></legend><?php endif; ?>
<?php if (!empty($groupInfo['info'])): ?><p class="info-<?php show($group,true)?>"><?php l($groupInfo['info']); ?></p><?php endif; ?>

<?php foreach (form::getGroupElements($view->data['formName'],$group) as $element): ?>

<?php part("element/{$element['type']}",array('data'=>$element)); ?>

<?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
</form>
</div>