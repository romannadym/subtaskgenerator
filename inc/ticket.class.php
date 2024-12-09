<?php
class PluginSubtaskgeneratorTicket extends CommonDBTM
{

  public static function getTypeName($nb = 0)
  {
    return _n('Ticket item', 'Ticket items', 0);
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
          _n('Ticket item', 'Ticket items', 0),
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
          'SELECT' => ['ticket_id'],
          'FROM'   => self::getTable(),
          'WHERE'  => ['container_id' => $cID],
          'ORDER'  => 'id DESC',
      ]);

      $rand = mt_rand();
      //Session::initNavigateListItems('PluginSubtaskgeneratorItilcategory', __('Fields list'));
        echo "<div id='viewItilcategory$cID$rand'></div>";
      echo "<table class='search-results table card-table table-hover table-striped'>";
      echo '<tr>';
      echo '<th>' . __('ID') . '</th>';
      echo '<th>' . __('Заголовок') . '</th>';
      echo '<th>' . __('Статус') . '</th>';
      echo '<th>' . __('Инициатор запроса') . '</th>';
      echo '<th>' . __('Дата поступления') . '</th>';
      echo '<th width="20%">' . __('Category') . '</th>';
      echo '<th>' . __('SLA') . '</th>';
      echo '<th>' . __('Исполнитель') . '</th>';
      echo '</tr>';

      echo '<tbody>';
      foreach($iterator as $row) 
        {
            $ticket = new Ticket();
            $ticket->getFromDB($row['ticket_id']);
            $class = '';
            if ($ticket->isDeleted())
            {
                $class = 'table-danger';
            }
            $initiator_id = $ticket->getField('users_id_recipient');
            // Инициализация объекта пользователя
            $initiator_name = '';
            if (!empty($initiator_id)) {
                $user = new User();
                if ($user->getFromDB($initiator_id)) {
                    $initiator_name = $user->getLink('name');
                }
            }       

            // Получение ID категории
            $category_id = $ticket->getField('itilcategories_id');

            // Получение названия категории
            $category_name = '';
            if (!empty($category_id)) {
                $category_name = Dropdown::getDropdownName('glpi_itilcategories', $category_id);
            }

            // Получение исполнителя
            $executor_name = '';
            $ticket_id = $ticket->getID();
            $query = "SELECT users_id FROM glpi_tickets_users WHERE tickets_id = $ticket_id AND type = 2";
            $result = $DB->query($query);
        
            if ($result && $DB->numrows($result) > 0) {
                $data = $DB->fetch_assoc($result);
                $user = new User();
                if ($user->getFromDB($data['users_id'])) {
                    $executor_name = $user->getLink('name');
                }
            }

            // Получение ID SLA
            $sla_id = $ticket->getField('slas_id_ttr');

            // Получение названия SLA
            $sla_name = '';
            if (!empty($sla_id)) {
                $sla_name = Dropdown::getDropdownName('glpi_slas', $sla_id);
            }
            echo "<tr class='$class'>";
            echo '<td>' . $ticket->getID() . '</td>';
            echo '<td>' . $ticket->getLink() . '</td>';
            echo '<td><span class="text-nowrap">' . Ticket::getStatusIcon($ticket->getField('status')) . ' ' . Ticket::getStatus($ticket->getField('status')) . '</span></td>'; // Отображение названия статуса
            echo '<td>' . $initiator_name . '</td>';
            echo '<td>' . $ticket->getField('date_creation') . '</td>';
            echo '<td>' . $category_name . '</td>';
            echo '<td>' . $sla_name . '</td>';
            echo '<td>'. $executor_name .'</td>';
            echo '</tr>';
                    
                }
    echo '</tbody>';
    echo '</table>';
    }

}
