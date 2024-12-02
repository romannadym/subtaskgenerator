<?php
include('../../../inc/includes.php');

if (empty($_GET['id'])) {
    $_GET['id'] = '';
}

$container = new PluginSubtaskgeneratorContainer();

if (isset($_POST['add'])) {
    if(empty($_POST['name']))
    {
          Session::addMessageAfterRedirect(
              __('Все поля должны быть заполнены'),
              false,
              ERROR
          );
          Html::back();
    }
    $container->check(-1, CREATE, $_POST);
    $newID = $container->add($_POST);
    Html::redirect(Plugin::getPhpDir('subtaskgenerator', false) ."/front/container.form.php?id=$newID");
} elseif (isset($_REQUEST['purge'])) {
    $container->check($_REQUEST['id'], PURGE);
    $container->delete($_REQUEST, 1);
    Html::redirect(Plugin::getPhpDir('subtaskgenerator', false) .'/front/container.php');
}elseif (isset($_POST['update'])) {
    if(empty($_POST['name']))
    {
          Session::addMessageAfterRedirect(
              __('Все поля должны быть заполнены'),
              false,
              ERROR
          );
    }
    $container->check($_POST['id'], UPDATE);
    $container->update($_POST);
    Html::back();
}  else {
    Html::header(
        __('Генератор подзадач', 'subtaskgenerator'),
        $_SERVER['PHP_SELF'],
        'config',
        'pluginsubtaskgeneratormenucfg',
        'subtaskgeneratorcontainer',
    );
    $container->display(['id' => $_GET['id']]);
    Html::footer();
}
