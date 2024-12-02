<?php
include('../../../inc/includes.php');

if (empty($_GET['id'])) {
    $_GET['id'] = '';
}

Session::checkRight('entity', READ);

$field = new PluginSubtaskgeneratorItilcategory();
if (isset($_POST["add"]) && isset($_POST['container_id']))
{

    if($_POST['requester_id'] != 0 && $_POST['itilcategory_id'] != 0 && !empty($_POST['description']))
    {
      $field->add([
            'container_id' => $_POST['container_id'],
            'requester_id' => $_POST['requester_id'],
            'itilcategory_id' => $_POST['itilcategory_id'],
            'description' => $_POST['description'],
        ]);
    }
    else
    {
        Session::addMessageAfterRedirect(
            __('Все поля должны быть заполнены'),
            false,
            ERROR
        );
    }
    Html::back();
}
elseif (isset($_POST["delete"]) && isset($_POST['id']))
{
      $field->delete(['id' => $_POST['id']]);
      Html::back();
}
elseif (isset($_GET['id']))
{
    $field->check($_GET['id'], READ);

    Html::header(PluginSubtaskgeneratorItilcategory::getTypeName(1), $_SERVER['PHP_SELF']);

    $field->getFromDB($_GET['id']);
    $field->display(['id' => $_GET['id'],
        'parent_id'       => $field->fields['container_id'],
    ]);

    Html::footer();
}
