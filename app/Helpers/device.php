<?php

use Jenssegers\Agent\Agent;

if (!function_exists('device_info')) {

    function device_info()
    {
        $agent = new Agent();

        return [
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
    }
}