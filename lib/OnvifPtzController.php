<?php

namespace Avreg;

require './OnvifClient/OnvifAjaxController.php';

class OnvifPtzController extends OnvifAjaxController
{
    protected function connectCamera($data = array())
    {
        if (!isset($data['cameraNumber'])) {
            throw new \Exception('cameraNumber not set');
        }

        // start of mess
        // todo - refactor, implement more efficient way to get camera params
        require('utils.php');
        require('/etc/avreg/site-defaults.php');

        $res = confparse($conf, 'avreg-site');
        if (!$res) {
            die();
        } else {
            $conf = array_merge($conf, $res);
        }

        if (!empty($profile) && $res = confparse($conf, 'avreg-site', $conf['profiles-dir'] . '/' . $profile)) {
            $conf = array_merge($conf, $res);
        }

        $link = null;
        require_once($conf['site-dir'] . '/offline/gallery/memcache.php');
        $non_config = true;
        require_once($conf['site-dir'] . '/lib/adb.php');

        $camsData = $adb->getCamParams(
            $data['cameraNumber'],
            "'text_left', 'InetCam_IP', 'InetCam_http_port', 'InetCam_USER', 'InetCam_PASSWD'"
        );

        $camData = array();

        foreach ($camsData as $row) {
            if ($row['CAM_NR'] === $data['cameraNumber']) {
                $camData[$row['PARAM']] = $row['VALUE'];
            }
        }
        // end of mess

        $data = array_merge($data, array(
            'origin' => 'http://' . $camData['InetCam_IP'] . ':' . '80',
            'username' => $camData['InetCam_USER'],
            'password' => $camData['InetCam_PASSWD'],
        ));

        $this->connect($data);
    }

    public function getPtzStatus($data = array())
    {
        $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $ptzStatus = $this->onvifClient->doSoapRequest('ptz', 'GetStatus', array('ProfileToken' => 'balanced_jpeg'));

        if ($ptzStatus['isOk']) {
            $this->success(array(
                'PTZStatus' => $ptzStatus['result']->PTZStatus
            ));
        } else {
            $this->error();
        }
    }

    /**
     * Expected movement data:
     * pan : 0..1
     * tilt : 0..1
     * zoom : 0..1
     *
     * @param array $data
     * @throws \Exception
     */
    public function moveAbsolute($data = array())
    {
        $this->connectCamera($data);

        if (!isset($data['pan']) && !isset($data['tilt']) && !isset($data['zoom'])) {
            throw new \Exception('Position not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        // collect move coordinates
        $position = array(
            'PanTilt' => array(),
            'Zoom' => array()
        );

        isset($data['pan']) ? $position['PanTilt']['x'] = $data['pan'] : '';
        isset($data['tilt']) ? $position['PanTilt']['y'] = $data['tilt'] : '';
        isset($data['zoom']) ? $position['Zoom']['x'] = $data['zoom'] : '';

        $moveResponse = $this->onvifClient->doSoapRequest(
            'ptz',
            'AbsoluteMove',
            array('Position' => $position, 'ProfileToken' => 'balanced_jpeg')
        );

        if ($moveResponse['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }
}

$controller = new OnvifPtzController();
