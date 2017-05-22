<?php

namespace App\Keychest\Queue;


interface JsonJob {
    /**
     * Returns json interpretation
     * @return array
     */
    public function toJson();
}

