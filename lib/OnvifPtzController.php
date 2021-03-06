<?php

namespace Avreg;

require '../head-xhr.inc.php';
require './OnvifClient/OnvifAjaxController.php';

class OnvifPtzController extends OnvifAjaxController
{
    /**
     * Connect to ONVIF-enabled camera by camera number.
     * Returns an array of relevant stored params,
     *
     * @param array $connectionData
     * @return array
     * @throws \Exception
     */
    protected function connectCamera($connectionData = array())
    {
        global $adb;

        if (!isset($connectionData['cameraNumber'])) {
            throw new \Exception('cameraNumber not set');
        }

        $camsData = $adb->getCamParams(
            $connectionData['cameraNumber'],
            "'text_left', 'InetCam_IP', 'InetCam_http_port', 'InetCam_USER', 'InetCam_PASSWD', 'onvif_profile_token'"
        );

        $camData = array();

        foreach ($camsData as $row) {
            if ($row['CAM_NR'] === $connectionData['cameraNumber']) {
                $camData[$row['PARAM']] = $row['VALUE'];
            }
        }

        $connectionData = array(
            'origin' => 'http://' . $camData['InetCam_IP'] . ':' . '80',
            'username' => $camData['InetCam_USER'],
            'password' => $camData['InetCam_PASSWD'],
        );

        $this->connect($connectionData);

        $cameraParams = array(
            'profile_token' => $camData['onvif_profile_token']
        );

        return $cameraParams;
    }

    /**
     * Extract speed component from POST request to be used in SOAP.
     * @param array $data
     * @return array
     */
    protected function getSpeedVector($data = array())
    {
        // collect speed data
        $speed = array();

        if (isset($data['panSpeed']) || isset($data['tiltSpeed'])) {
            $speed['PanTilt'] = array();

            isset($data['panSpeed']) ? $speed['PanTilt']['x'] = $data['panSpeed'] : '';
            isset($data['tiltSpeed']) ? $speed['PanTilt']['y'] = $data['tiltSpeed'] : '';
        }

        if (isset($data['zoomSpeed'])) {
            $speed['Zoom'] = array('x' => $data['zoomSpeed']);
        }

        return $speed;
    }

    public function getPtzStatus($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $ptzStatus = $this->onvifClient->doSoapRequest(
            'ptz',
            'GetStatus',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($ptzStatus['isOk']) {
            // convert from possible scientific notation to dot notation
            $ptzStatus['result']->PTZStatus->Position->PanTilt->x =
                sprintf('%F', $ptzStatus['result']->PTZStatus->Position->PanTilt->x);
            $ptzStatus['result']->PTZStatus->Position->PanTilt->y =
                sprintf('%F', $ptzStatus['result']->PTZStatus->Position->PanTilt->y);
            $ptzStatus['result']->PTZStatus->Position->Zoom->x =
                sprintf('%F', $ptzStatus['result']->PTZStatus->Position->Zoom->x);

            $this->success(array(
                'PTZStatus' => $ptzStatus['result']->PTZStatus
            ));
        } else {
            $this->error('Could not get PTZ status.');
        }
    }

    public function getPtzPresets($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $ptzPresets = $this->onvifClient->doSoapRequest(
            'ptz',
            'GetPresets',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($ptzPresets['isOk']) {
            foreach ($ptzPresets['result']->Preset as $preset) {
                // convert from possible scientific notation to dot notation
                $preset->PTZPosition->PanTilt->x = sprintf('%F', $preset->PTZPosition->PanTilt->x);
                $preset->PTZPosition->PanTilt->y = sprintf('%F', $preset->PTZPosition->PanTilt->y);
                $preset->PTZPosition->Zoom->x = sprintf('%F', $preset->PTZPosition->Zoom->x);
            }

            $this->success(array(
                'Presets' => $ptzPresets['result']->Preset
            ));
        } else {
            $this->error();
        }
    }

    public function moveAbsolute($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['pan']) && !isset($data['tilt']) && !isset($data['zoom'])) {
            throw new \Exception('Position not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        // collect move parameters

        $position = array(
            'PanTilt' => array(),
            'Zoom' => array()
        );

        isset($data['pan']) ? $position['PanTilt']['x'] = $data['pan'] : '';
        isset($data['tilt']) ? $position['PanTilt']['y'] = $data['tilt'] : '';
        isset($data['zoom']) ? $position['Zoom']['x'] = $data['zoom'] : '';

        $speed = $this->getSpeedVector($data);

        // do the request
        $requestParams = array(
            'Position' => $position,
            'Speed' => $speed,
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $moveResponse = $this->onvifClient->doSoapRequest(
            'ptz',
            'AbsoluteMove',
            $requestParams
        );

        if ($moveResponse['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function moveStop($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $stopResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'Stop',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($stopResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function gotoPreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetToken'])) {
            throw new \Exception('presetToken not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $speed = $this->getSpeedVector($data);

        $requestParams = array(
            'PresetToken' => $data['presetToken'],
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $gotoResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'GotoPreset',
            $requestParams
        );

        if ($gotoResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function gotoHomePosition($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $speed = $this->getSpeedVector($data);

        $requestParams = array(
            'Speed' => $speed,
            'ProfileToken' => $cameraParams['profile_token']
        );

        if (!empty($speed)) {
            $requestParams['Speed'] = $speed;
        }

        $gotoResult = $this->onvifClient->doSoapRequest(
            'ptz',
            'GotoHomePosition',
            $requestParams
        );

        if ($gotoResult['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function setHomePosition($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'SetHomePosition',
            array('ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }


    public function createPreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetName'])) {
            throw new \Exception('presetName not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'SetPreset',
            array('PresetName' => $data['presetName'], 'ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function removePreset($data = array())
    {
        $cameraParams = $this->connectCamera($data);

        if (!isset($data['presetToken'])) {
            throw new \Exception('presetToken not set');
        }

        if (!$this->checkAuthData()) {
            $this->error('', 401);
            return;
        }

        $result = $this->onvifClient->doSoapRequest(
            'ptz',
            'RemovePreset',
            array('PresetToken' => $data['presetToken'], 'ProfileToken' => $cameraParams['profile_token'])
        );

        if ($result['isOk']) {
            $this->success();
        } else {
            $this->error();
        }
    }
}

$controller = new OnvifPtzController();
