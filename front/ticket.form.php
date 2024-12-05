<?php
include('../../../inc/includes.php');

if (empty($_GET['id'])) {
    $_GET['id'] = '';
}

Session::checkRight('entity', READ);

$field = new PluginSubtaskgeneratorTicket();
if (isset($_GET['id']))
{
    $field->check($_GET['id'], READ);

    Html::header(PluginSubtaskgeneratorTicket::getTypeName(1), $_SERVER['PHP_SELF']);

    $field->getFromDB($_GET['id']);
    $field->display(['id' => $_GET['id'],
        'parent_id'       => $field->fields['container_id'],
    ]);

    Html::footer();
}
