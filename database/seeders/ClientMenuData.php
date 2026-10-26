<?php

namespace Database\Seeders;

class ClientMenuData
{
    public static function document(): array
    {
        return json_decode(file_get_contents(database_path('data/client-menu.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function services(): array
    {
        return self::document()['services'];
    }

    public static function images(): array
    {
        return array_map(fn (int $number) => [
            'key' => 'menu-brow-'.$number,
            'source' => 'menu-brow-'.$number.'.jpg',
            'caption' => 'Brow styling '.$number,
            'alt' => 'Brow styling example from the salon service menu, photo '.$number,
        ], [1, 2, 3]);
    }
}
