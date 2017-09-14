<?php

namespace App\Keychest\Queue;



use Illuminate\Queue\Jobs\RedisJob;

use Illuminate\Contracts\Queue\Job as JobContract;

class Ph4RedisJob extends RedisJob implements JobContract
{

}
