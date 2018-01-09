<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 02.10.17
 * Time: 13:58
 */

namespace App\Http\Request;


use Illuminate\Http\Request as BaseRequest;

class ParamRequest extends BaseRequest
{
    use ParamRequestTrait;

    public function all($keys = null)
    {
        $arr = parent::all($keys);
        return $this->augmentAllResponse($arr);
    }


}
