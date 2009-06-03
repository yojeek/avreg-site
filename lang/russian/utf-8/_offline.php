<?php

$strCamChoiseHelp='Можно выбрать несколько камер сразу.\nДля этого, выбирая камеры мышью,\nудерживайте клавиши SHIFT или CTRL';

$strFilterHelp='Определите какие события и файлы искать в базе данных.\n\n'.
'Состав реально выбранных файлов зависит от формата записи.\nНапример, при записи в видеофильмы AVI,\nкадры с движением, предзаписанные и послезаписанные находятся внутри AVI.\n\nМожно выбрать несколько типов сразу. Для этого,\nотмечая позиции мышью, удерживайте клавиши SHIFT или CTRL';

$strActionHelp='Последовательность работы с архивом:\nсформировали или изменили условия поиска - жмите кнопку \"Показать\".\nВ фрейме справа отобразится список событий и ссылок на сохранённые медиа-файлы,\nудовлетворяющий параметрам Вашего запроса.\n\nЧисло (выше кнопок) ограничивает количество элементов списка для вывода на одной странице.';

$strScaleTitle='Относительное масштабирование при выводе видео или кадра JPEG';

$strOptionHelp='Дополнительные опции.\nДля получения краткой справки по конкретной опции,\nнаведите (не кликая) курсор мыши на элемент и подожите 1-2 секунды.';

$str_row_maxTitle='Ссылок на одну страницу';
$str_embed_Title='Попытаться вопроизводить видео прямо на странице';

$strScale = 'Масштаб';
$strEmbdedVideo='Встр.видео';
$strCamName = 'Название видеокамеры';

$strTimeMode  = 'время';
$strBreak = 'интервалы';
$strUnBreak = 'сплошное';
$strTimeModeHelp = 'Если выбрано \"'.$strUnBreak.'\", то будут выбраны все события или файлы,\nвозникшие или сохранённые в интервале с начального до конечного значений времени\nт.е. из диапазона [год\/месяц\/день час:минута - год\/месяц\/день час:минута]\n\n'.
'Если выбрано \"'.$strBreak.'\", то \nв выбранном диапазоне ДНЕЙ [ год\/месяц\/день - год\/месяц\/день ] за ОДИН запрос\nможно будет получить все события\/файлы, записанные,\nнапример, во все Пн,Ср,Пт и только с 17:35 до 17:59\n\nЭтот режим несколько медленнее, но очень удобен для поиска в  некоторых жизненных ситуациях.';
$strTimeModeHelp2 = 'Используется только когда параметр \"'.$strTimeMode.'\" установлен как \"'.$strBreak.'\".\n\nМожно выбрать несколько дней недели сразу. \nДля этого, при выборе, удерживайте клавиши SHIFT или CTRL.';

$sFromDate = 'с этой даты';
$sToDate = 'по эту дату';
$sFromTime = 'с этого времени';
$sToTime = 'по это время';


/* view-image.php */
$strViewFrame1 = 'В этом фрейме (часть окна) отображаются видеокадры JPEG и фрагменты видеофильмов.';
$strViewFrame2 = 
'Для просмотра видеофильмов рекомендуем пользоваться свободно-распространяемым кросс-платформеннымы медиа-проигрывателеми <a href="http://www.videolan.org/" target="_blank">VLC</a> (win32, *nix), MPlayer (*nix), и т.п.
Или же, для просмотра другими (не VLC) медиа-плеерами на win32 платформах, вам возможно потребуется установить свободно-распространяемый кодек <a href="http://www.free-codecs.com/download/FFDShow.htm" TITLE="популярный и быстрый кодек" target="_blank">FFDSOW</a>, в настройках которого нужно включить поддержку форматов MJPG и MPEG4 (fourcc).';

/* result.php */
$strNotCamsChoice = 'Сформируйте минимальные данные для запроса:<ol>'.
'<li>видеокамеры;</li>'.
'<li>интервалы времени (год, месяц и т.д.);</li>'.
'<li>фильтры, хотя бы один;</li>'.
'<li>др. параметры на форме снизу;</li>'.
'</ol>и нажмите один раз на кнопку &#171;Показать&#187;.';
$strNotSavedPict = 'В базе данных не найдено ни одного события и(или) файла, удовлетворяющим вашим параметрам поиска.';
$fmtSavedPict = 'Диапазон: c %s по %s<br />Показана страница #%d из %d файлов/ссылок';

$strMaxPict = 'Уже был показан последний видеокадр в текущем списке.';
$strMinPict = 'Уже был показан первый видеокадр в текущем списке.';
$fmtResult = 'Выбрано %d  кадров  начиная с %d.';


/* playlist.php */
$PlaylistTitle='Архив::Плейлист';
$GetPlaylistStr='Открыть плейлист';
$strXSPF = '<strong>XSPF</strong> - современный продвинутый свободный формат, основанный на XML.';
$strM3U  = '<strong>M3U</strong> - формат компании Nullsoft (Winamp), &quot;понимается&quot; большинством медиа-проигрывателей.';

$strPlFmtTitle = 'Формат файла плейлиста';
$strTimeRangeSelect = 'Ограничение по времени';

$strBrowserHandlers = 'Будет ли файл плейлиста автоматически открыт в медиа-плеере (и каком конкретно) или сохранён на диске (и в каком каталоге) зависит от настроек &quot;привязки типов файлов&quot; в вашем интернет-браузере(IE, FF, Opera, ...) и, возможно, в графической среде окружения (GNOME, KDE, ...) или даже в системе (Win, Linux, Mac). Например, в Mozilla Firefox: меню &quot;Правка&quot; &#8250; &quot;Настройки&quot;, далее вкладка &quot;Содержимое&quot; &#8250; &quot;Типы файлов&quot; &#8250; кнопка &quot;Управление&quot;, форма &quot;Действие при загрузке&quot;.';

?>
