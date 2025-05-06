<?php
class PluginSubtaskgeneratorItilcategory extends CommonDBTM
{

  public static function getTypeName($nb = 0)
  {
    return __('Категории подзадач', 'subtaskgenerator');
  }
  public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
  {
      if (!$withtemplate) {
          switch ($item->getType()) {
              case __CLASS__:
                  $ong[1] = $this->getTypeName(1);

                  return $ong;
          }
      }

      return self::createTabEntry(
          __('Категории подзадач', 'subtaskgenerator'),
          countElementsInTable(
              self::getTable(),
              ['container_id' => $item->getID()],
          ),
      );
  }
  public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
  {
      $fup = new self();
      $fup->showSummary($item);

      return true;
  }
  public function showSummary($container)
  {
      /**
       * @var DBmysql $DB
       * @var array   $CFG_GLPI
       */
      global $DB, $CFG_GLPI;

      $cID = $container->fields['id'];

      // Display existing Fields
      $iterator = $DB->request([
          'SELECT' => ['id'],
          'FROM'   => self::getTable(),
          'WHERE'  => ['container_id' => $cID],
          'ORDER'  => 'id ASC',
      ]);

      $rand = mt_rand();
        self::getForm($cID);
      Session::initNavigateListItems('PluginSubtaskgeneratorItilcategory', __('Fields list'));
        echo "<div id='viewItilcategory$cID$rand'></div>";
      echo "<table class='tab_cadre_fixehov'>";
      echo '<tr>';
      echo '<th>' . __('ID') . '</th>';
      echo '<th>' . __('Category') . '</th>';
      echo '<th>' . __('Description') . '</th>';
      echo '<th>' . __('Инициатор') . '</th>';
      echo '<th>' . __('SLA') . '</th>';
      echo '<th>' . __('Action') . '</th>';
      echo '</tr>';

      foreach ($iterator as $data)
      {
          if ($this->getFromDB($data['id']))
          {
            $user = new User();
            $user->getFromDB($this->fields['requester_id']);
            $ITILCategory = new ITILCategory();
            $ITILCategory->getFromDB($this->fields['itilcategory_id']);
            $SLA = new SLA();
            $SLA->getFromDB($this->fields['slas_id']);
            echo "<tr class='tab_bg_2' style='cursor:pointer'>";
            echo '<td>' . __($this->fields['id']) . '</td>';
            echo "<td><a href='/front/itilcategory.form.php?id={$ITILCategory->getID()}'>" . __($ITILCategory->getField('name'), 'subtaskgenerator') . "</a></td>";
            echo '</td>';
            echo '<td>'  .$this->fields['description'] . '</td>';
            $user_name =  empty($user->getField('realname')) && empty($user->getField('firstname')) ? $user->getField('name') : $user->getField('realname') . ' ' . $user->getField('firstname') ;
            echo "<td><a href='/front/user.form.php?id={$user->getID()}'>" . $user_name . "</a></td>";
            echo '<td>'  . __($SLA->getField('name')) . '</td>';
            echo "<td>";
            echo "<form method='post' action='".Plugin::getWebDir('subtaskgenerator')."/front/itilcategory.form.php'>";
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('id', ['value' => $this->fields['id']]);
            echo Html::submit(__('Delete'), [
                'name'  => 'delete',
                'class' => 'btn btn-danger'
            ]);
              echo "</form>";
            echo "</td>";
            echo "</tr>";
          }
      }

      echo '</table>';
      echo '</div>';
  }
  static function getForm($id)
  {
    echo "<form method='POST' action='" . Plugin::getWebDir('subtaskgenerator') . "/front/itilcategory.form.php'>";
    echo Html::hidden('container_id', ['value' => $id]);
    echo "<table class='tab_cadre_fixe'>";
    echo "<thead><tr class='tab_bg_1'>";
    echo "<th colspan='4'>" . __('Параметры') . "</th>";
    echo "</tr></thead>";

    echo "<tbody>";
    echo "<tr class='tab_bg_1'>";
    echo "<td><label for='user_id'>" . __('Category') . ":</label></td>";
    echo "<td>";
    $excluded_itilcategories = [];
    $glpi_plugin_subtaskgenerator_containers = new PluginSubtaskgeneratorContainer();
    $cat = $glpi_plugin_subtaskgenerator_containers->find();
    foreach ($cat as $c) {
        $excluded_itilcategories[] = $c['itilcategory_id'];
    }
    $glpi_plugin_subtaskgenerator_itilcategories = new self();
    $cat = $glpi_plugin_subtaskgenerator_itilcategories->find();
    foreach ($cat as $c) {
        $excluded_itilcategories[] = $c['itilcategory_id'];
    }
    ITILCategory::dropdown([
        'name'       => 'itilcategory_id',
        'value'      => 0,
        'entity'     => $_SESSION['glpiactive_entity'],
        'required'   => true,
        'right'      => 'all',
        'used' => $excluded_itilcategories //исключаем уже выбранных пользователей из выпадающего списка выбора пользователей
    ]);
    echo "</td>";
    echo "<td rowspan='3'><label for='description'>" . __('Description') . ":</label></td>";
    echo "<td rowspan='3'>";
    Html::textarea([
       'name'  => 'description',
       'value' => '', // Значение по умолчанию
       'rows'  => 6,
       'cols'  => 50,
   ]);
    echo "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td><label for='slas_id'>" . __('Time to resolve') . ":</label></td>";
    echo "<td colspan='2'>";
    SLA::dropdown([
        'name'       => 'slas_id',
        'value'      => 0,
        'entity'     => $_SESSION['glpiactive_entity'],
        'right'      => 'all',
        //'used' => $excluded_users //исключаем уже выбранных пользователей из выпадающего списка выбора пользователей
    ]);
    echo "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td><label for='user_id'>" . __('Requester') . ":</label></td>";
    echo "<td colspan='2'>";
    User::dropdown([
        'name'       => 'requester_id',
        'value'      => 0,
        'entity'     => $_SESSION['glpiactive_entity'],
        'right'      => 'all',
        //'used' => $excluded_users //исключаем уже выбранных пользователей из выпадающего списка выбора пользователей
    ]);
    echo "</td>";
    echo "</tr>";

    echo "</tbody>";

    echo "<tfooter><tr><td colspan='4' align ='center'>";
    echo Html::submit(__('Add'), [
        'name' => 'add',
        'class' => 'btn btn-secondary ml-2 ms-5 mt-5 mb-5'
    ]);
    echo "</td></tr></tfooter>";
    echo "</table>";
    Html::closeForm();
  }

}
