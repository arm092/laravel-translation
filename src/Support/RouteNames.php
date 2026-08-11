<?php

namespace Arm092\Translation\Support;

final class RouteNames
{
    public function get(string $name): string
    {
        return (string) config('translation.route_group_config.as', '').$name;
    }
}
