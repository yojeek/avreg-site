<?php

/**
 * @file offline/gallery/gallery.php
 * @brief класс служит для получения и обновления данных о событиях
 * инстанцируется:
 * <ol>
 * <li> в offline/gallery.php - при отображении галереи
 * <li> в cron.php - для обновления данных дерева событий
 * </ol>
 *
 * информация в таблице tree_events обновляется при открытии
 * галереи начиная с времени последнего обновления и заканчивая
 * последним событием в events.
 * Это осуществляется вызовом метода getTreeEvents($par_hash) в offline/gallery/js/main.js .
 *
 * cron.php при открытии галереи вообще не используется.
 *
 * cron.php - предоставляет возможность выполнять обновление этой
 * таблицы по некоторому расписанию, например в crontab.
 * Что это дает:
 *
 * 1. галерея открывается быстрей, поскольку для обновления надо
 * обработать меньшее кол-во данных
 * (может быть актуально при редком открытии галереи и большом кол-ве камер).
 * Выполняется вызовом метода cronUpdateTreeEvents().
 *
 * 2. после работы чистильщика, надо обновить tree_events для
 * того периода, для которого были удалены файлы.
 * (это необходимо, поскольку, как было сказано, при открытии галереи
 * таблица обновляется начиная с времени последнего обновления,
 * а не полностью с самого начала).
 * Выполняется вызовом метода updateTreeEvents($par_hash),
 * которому в качестве параметров передаются начало(start) и конец(end) временного диапазона,
 * а так же номера камер(cameras) для которых надо выполнить обновление
 *
 *
 * */

namespace Avreg;

class Gallery
{
    public $method = ''; // метод запроса
    public $result = array(); // ответ запроса
    private $cache;
    private $db = '';
    private $limit = 0;
    private $conf = array(); // настройки галереи

    // конструктор класса
    public function __construct($par_hash)
    {
        // получение параметров запроса
        foreach ($par_hash as $k => $v) {
            if (isset($this->$k) && !in_array($k, array('db', 'conf'))) {
                $this->$k = $v;
            }
        }
        // Получение глобальных настроек сайта
        global $conf;
        $this->conf = $conf;
        $this->cache = new Cache('gallery', !@empty($conf['debug']));
        if (!$this->limit) {
            $this->limit = $this->conf['gallery-limit'];
        }
        global $adb;
        $this->db = $adb;

        // Если существует запрашиваемый метод, то его выполняем с указанными параметрами
        if (!empty($this->method) && method_exists($this, $this->method)) {
            $this->{$this->method}($par_hash);
        }
    } /* __construct() */

    // Функция получения событий
    public function getEvents($par_hash)
    {
        $events = array();
        // если есть список камер, то выполняем запрос
        if (isset($par_hash['cameras']) && !empty($par_hash['cameras'])) {
            $cameras = trim($par_hash['cameras'], ',');
            $par_hash['cameras'] = explode(",", $cameras);

            $type = explode(",", trim($par_hash['type'], ','));

            // картинки
            $EVT_ID = array();
            if (in_array('image', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(15, 16, 17));
            }
            // видео
            if (in_array('video', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(23));
            }
            // аудио
            if (in_array('audio', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(32));
            }
            // видео+audio
            if (in_array('video', $type) || in_array('audio', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(12));
            }

            $p = array(
                'cameras' => $par_hash['cameras'],
                'events' => $EVT_ID,
                'date' => $par_hash['tree'] !== 'all' ? explode('_', $par_hash['tree']) : array(),
                'limit' => $this->limit,
                'offset' => $par_hash['sp'],
            );
            //$events = $this->db->galleryGetEvent($p);
            if ($this->limit > 1) {
                $events = $this->db->galleryGetEvent($p);
                // Сохранение результата
                $this->result = array('events' => $events);
            } else {
                $date = $this->db->galleryGetEventDate($p);
                // Сохранение результата
                $this->result = $date;
            }
        }
    } /* getEvents() */

    // Функция построения дерева события
    public function getTreeEvents($par_hash)
    {
        $initially = isset($par_hash['initially']);
        //если древо заблокировано, то возвращаем ошибку
        if ($this->cache->get('gallery_update')) {
            $this->result = array('status' => 'error', 'code' => '3', 'description' => 'Дерево заблокированно');
            return;
        }

        global $GCP_cams_params;
        $cameras = implode(',', array_keys($GCP_cams_params));
        // TODO make 1 select
        $count_event = $this->db->galleryGetCountEvent(array('cameras' => array_keys($GCP_cams_params)));
        $count_tree_event = $this->db->galleryGetCountTreeEvent(array('cameras' => array_keys($GCP_cams_params)));
        $last_event_date = $this->db->galleryGetLastEventDate(array('cameras' => array_keys($GCP_cams_params)));
        $last_tree_date = $this->db->galleryGetLastTreeEventDate(array('cameras' => array_keys($GCP_cams_params)));
        $oldest_event_date = $this->db->galleryGetOldestEventDate(array('cameras' => array_keys($GCP_cams_params)));
        $oldest_tree_date = $this->db->galleryGetOldestTreeEventDate(array('cameras' => array_keys($GCP_cams_params)));

        if ($count_event != $count_tree_event  ||
            $last_tree_date < $last_event_date ||
            $oldest_event_date > $oldest_tree_date) {
            /* need update tree_events */
            $access_update_tree = in_array(
                $GLOBALS['user_status'],
                array($GLOBALS['install_status'], $GLOBALS['admin_status'], $GLOBALS['arch_status'])
            );
            $this->result = array(
                    'status' => 'error',
                    'code' => '4',
                    'description' => 'Дерево не синхронизированно',
                    'count_event' => $count_event,
                    'count_tree_event' => $count_tree_event,
                    'last_event_date' => $last_event_date,
                    'last_tree_date' => $last_tree_date,
                    'oldest_event_date' => $oldest_event_date,
                    'oldest_tree_date' => $oldest_tree_date,
                    'access_update_tree' => $access_update_tree
            );
            return;
        }

        if (!$initially) {
            // возвращаем без данных и не ошибку как признак того что данные не изменились
            $this->result = array(
                'status' => 'success',
                'last_tree_date' => $last_tree_date,
                'count_event' => $count_event,
                'count_tree_event' => $count_tree_event,
                'last_event_date' => $last_event_date,
                'last_tree_date' => $last_tree_date,
                'oldest_event_date' => $oldest_event_date,
                'oldest_tree_date' => $oldest_tree_date
            );
            return;
        }
        // получаем дерево из кеша, если его нет, то из базы, результат помещаем в кеш.
        $key = md5($cameras . '-' . $last_tree_date);
        $tree_events_result = $this->cache->get($key);
        if (empty($tree_events_result)) {
            $tree_events_result = $this->db->galleryGetTreeEvents(array('cameras' => array_keys($GCP_cams_params)));
            if (!$this->cache->check($key)) {
                $this->cache->lock($key);
                $tree_events_keys = $this->cache->get('tree_events_keys');
                if (empty($tree_events_keys)) {
                    $tree_events_keys = array();
                }
                $tree_events_keys[] = $key;
                $this->cache->set($key, $tree_events_result);
                $this->cache->set('tree_events_keys', $tree_events_keys);
            }
        }

        // возвращаем результат
        if (empty($tree_events_result)) {
            $this->result = array('status' => 'error', 'code' => '0', 'description' => 'No events.', 'qtty' => 0);
            return;
        }

        $this->result = array(
            'status' => 'success',
            'tree_events' => $tree_events_result,
            'cameras' => $GCP_cams_params,
            'last_tree_date' => $last_tree_date,
            'count_event' => $count_event,
            'count_tree_event' => $count_tree_event,
            'last_event_date' => $last_event_date,
            'last_tree_date' => $last_tree_date,
            'oldest_event_date' => $oldest_event_date,
            'oldest_tree_date' => $oldest_tree_date
        );
    } /* getTreeEvents() */

    // функция обновления дерева события
    public function updateTreeEvents($par_hash)
    {
        // проверяем может идет обновление
        if ($this->cache->get('gallery_update')) {
            return 1;
        }
        $start = isset($par_hash['start']) ? $par_hash['start'] : false;
        $end = isset($par_hash['end']) ? $par_hash['end'] : false;
        $cameras = isset($par_hash['cameras']) ? $par_hash['cameras'] : false;
        // устанавливаем блокировку
        $this->cache->set('gallery_update', true);
        // обновляем
        $this->db->galleryUpdateTreeEvents($start, $end, $cameras);

        // удаляем сохраненный в мемкеше деревья
        $tree_events_keys = $this->cache->get('tree_events_keys');
        if (!empty($tree_events_keys)) {
            foreach ($tree_events_keys as $key) {
                $this->cache->delete($key);
            }
        }
        // снимаем блокировку
        $this->cache->delete('gallery_update');
        return 0;
    } /* updateTreeEvents() */

    // функция запускается по крону, чтобы обновить последние события в дереве
    public function cronUpdateTreeEvents()
    {
        // последенее событие
        $last_event_date = $this->db->galleryGetLastEventDate();
        // последнее событие в дереве
        $last_tree_date = $this->db->galleryGetLastTreeEventDate();
        // обновляем дерево если оно не полное
        if ($last_tree_date < $last_event_date) {
            $par_hash = array(
                'start' => $last_tree_date,
                'end' => $last_event_date
            );
            return $this->updateTreeEvents($par_hash);
        }
    } /* cronUpdateTreeEvents() */

    // функция полного обновления деоева события
    public function reindexTreeEvents($par_hash)
    {
        global $GCP_cams_params;
        $par_hash['cameras'] = implode(',', array_keys($GCP_cams_params));
        $this->updateTreeEvents($par_hash);
        $par_hash['initially'] = 'yes'; // чтобы getTreeEvents() возвратил данные
        $this->getTreeEvents($par_hash);
    } /* updateTreeEvents() */

    // отдача результата клиенту
    public function printResult()
    {
        if ($this->limit > 1) {
            echo json_encode($this->result);
        } else {
            if (is_array($this->result)) {
                echo 'is array result';
            } else {
                echo $this->result;
            }
        }
    } /* printResult() */
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
