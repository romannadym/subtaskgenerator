<?php
include_once ("../../../inc/includes.php");
// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('subtaskgenerator') || !$plugin->isActivated('subtaskgenerator')) {
   Html::displayNotFoundError();
}

  Html::header(
      __("Генератор подзадач", "subtaskgenerator"),
      $_SERVER['PHP_SELF'],
    "config",
    "pluginsubtaskgeneratormenucfg",
    "subtaskgeneratorcontainer"
  );
Session::checkRight('config', READ);
Search::show('PluginSubtaskgeneratorContainer');
Html::footer();
