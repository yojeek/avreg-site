<?php
$r_cam_list = 'Сконфигурированные видеокамеры на сервере &#171;<font color="Red">%s</font>&#187;.';
$r_cam_tips_installers = 'Советы:<br/>
<ul>
<li>Камеры в конфигурацию (в этот список) автоматически не добавляются и не настраиваются.</li>
<li>Не допускайте пропусков в номерах камер, первая активная камера должна имеить номер 1.</li>
<li>Чтобы не настраивать одни и те же параметры для каждой камеры, определите <em>общие</em> для всех или большинства камер значения параметров один раз по ссылке &#171;<a href="'.$conf['prefix'].'/admin/cam-tune.php?cam_nr=0">параметры&nbsp;для&nbsp;всех</a>&#187;.</li>
<li>Активными (&quot;включенными&quot;) должны быть только те камеры, которые физически подключены или доступны. В противном случае сервер или не запустится или будет работать медленно из-за обращения к несуществующим камерам.</li>
</ul>';

$r_cam_tips_admins = 'Советы:<br/>
<ul>
<li>Полный доступ к настройкам камер (включая их добавление и удаление) имеют только пользователи группы  &#171;Инсталляторы&#187;.</li>
<li>Активными (&quot;включенными&quot;) должны быть только те камеры, которые физически подключены или доступны. В противном случае сервер или не запустится или будет работать медленно из-за обращения к несуществующим камерам.</li>
</ul>';

$r_cam_defaults = 'Параметры %s видеокамер на сервере &#171;<font color="Red">%s</font>&#187;.';

$r_cam_addnew = 'Добавление новой видеокамеры <font color="red">%u</font> на видеосервере &#171;%s&#187; [%s].';
$r_cam_addnew_ok1 = 'Добавлена видеокамера номер %u с названием &#171;%s&#187;.';
$r_cam_addnew_ok2 = 'Для настройки её параметров откройте список камер (меню слева) и выберите нужную видеокамеру.';

$strDeleteCam = "Все настройки видеокамеры #%d [%s] удалены.\n";
$strDeleteCamConfirm = "Вы уверены что хотите удалить все настройки<br>для видеокамеры #%d [%s] на сервере &#171;%s&#187; [%s] ?\n";
$strNotCamsDef = "Нет ни одной сконфигурированной видеокамеры.<br>Воспользуйтесь командой меню &#171;$l_cam_addnew&#187;.";
$strNotCamsDef2 = 'Нет ни одной сконфигурированной видеокамеры.
<br>Для просмотра архива вы должны иметь хотя бы одну настроенную видеокамеру.
<br>Попросите настроить видеокамеры установщика или администратора системы.';

$fmtEINVAL = 'Значение &#171;%s&#187; - недопустимо, исправляйте!'
?>
