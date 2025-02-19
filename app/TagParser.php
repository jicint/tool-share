<?php

namespace App;

class TagParser
{
    public function parse(string $tags)
    {
        $split = preg_split('/[,|] ?/', $tags);
        $result = array_filter(array_map('trim', $split));
        return array_values($result);
    }
} 