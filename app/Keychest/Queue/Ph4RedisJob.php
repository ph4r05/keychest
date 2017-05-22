<?php

namespace App\Keychest\Queue;

use App\Keychest\Queue\Ph4RedisQueue;
use Illuminate\Support\Arr;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;

class Ph4RedisJob extends RedisJob implements JobContract
{

}
