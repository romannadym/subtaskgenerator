<?php

/**
 * -------------------------------------------------------------------------
 * SubtaskGenerator plugin for GLPI
 * Copyright (C) 2024 by the SubtaskGenerator Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_subtaskgenerator_install()
{
  global $DB;
  $version = plugin_version_subtaskgenerator();
  //создать экземпляр миграции с версией
      $migration = new Migration($version['version']);
      //Create table only if it does not exists yet!
      if (!$DB->tableExists('glpi_plugin_subtaskgenerator_containers')) {
        // Запрос на создание таблицы с исправлениями
        $query = 'CREATE TABLE IF NOT EXISTS `glpi_plugin_subtaskgenerator_containers` (
              `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name`         VARCHAR(255)   DEFAULT NULL,
              `label`        VARCHAR(255)   DEFAULT NULL,
              `itemtypes`    LONGTEXT       DEFAULT NULL,
              `type`         VARCHAR(255)   DEFAULT NULL,
              `subtype`      VARCHAR(255)   DEFAULT NULL,
              `entities_id`  INT UNSIGNED   NOT NULL DEFAULT 0,
              `is_recursive` TINYINT  UNSIGNED      NOT NULL DEFAULT 0,
              `itilcategory_id` TINYINT  UNSIGNED      NOT NULL DEFAULT 0,
              `assign_id` TINYINT(10) UNSIGNED NULL DEFAULT NULL,
              `is_active`    TINYINT  UNSIGNED      NOT NULL DEFAULT 0,
              PRIMARY KEY    (`id`),
              CONSTRAINT unique_itilcategory_id UNIQUE (itilcategory_id),
              KEY            `entities_id`  (`entities_id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;';

        $DB->queryOrDie($query, $DB->error());
    }

    if (!$DB->tableExists('glpi_plugin_subtaskgenerator_itilcategories')) {
      // Запрос на создание таблицы с исправлениями
      $query = 'CREATE TABLE IF NOT EXISTS `glpi_plugin_subtaskgenerator_itilcategories` (
            `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `label`        VARCHAR(255)   DEFAULT NULL,
            `description`    TEXT   NOT NULL,
            `container_id`  INT UNSIGNED   NOT NULL DEFAULT 0,
            `itilcategory_id`  INT UNSIGNED   NOT NULL DEFAULT 0,
            `slas_id`  INT UNSIGNED   NOT NULL DEFAULT 0,
            `requester_id`    INT  UNSIGNED      NOT NULL DEFAULT 0,
            PRIMARY KEY    (`id`),
            CONSTRAINT unique_container_id_itilcategory_id UNIQUE (container_id, itilcategory_id)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;';

      $DB->queryOrDie($query, $DB->error());
  }


      //Create table only if it does not exists yet!
      if (!$DB->tableExists('glpi_plugin_subtaskgenerator_tickets')) {
        // Запрос на создание таблицы с исправлениями
        $query = 'CREATE TABLE IF NOT EXISTS `glpi_plugin_subtaskgenerator_tickets` (
              `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `ticket_id`  INT UNSIGNED   NOT NULL DEFAULT 0,
              `container_id` INT  UNSIGNED      NOT NULL DEFAULT 0,
              PRIMARY KEY    (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;';

        $DB->queryOrDie($query, $DB->error());
    }
    //создать экземпляр миграции с версией
    $migration = new Migration($version['version']);
    //execute the whole migration
    $migration->executeMigration();
    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_subtaskgenerator_uninstall()
{
    return true;
}
