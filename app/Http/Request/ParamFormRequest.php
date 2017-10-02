<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 02.10.17
 * Time: 13:58
 */

namespace App\Http\Request;


use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class ParamFormRequest extends BaseFormRequest
{
    use ParamRequestTrait;

    public function all()
    {
        $arr = parent::all();
        return $this->augmentAllResponse($arr);
    }


}
