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

}
