<?php

namespace Ouhaohan8023\IDInfo\Model;

use Ouhaohan8023\IDInfo\Service\IDInfoService;

class IDInfo
{
    public static function init()
    {
        IDInfoService::init();
    }

    public static function info($id)
    {
        return IDInfoService::info($id);

    }
}
