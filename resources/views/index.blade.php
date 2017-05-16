@extends('layouts.app')

@section('content')
    <div class="container">
    <div class="row search">
        <div class="col-sm-8 col-sm-offset-2">
            <form role="form" method="post">
                {{ csrf_field() }}
                <div class="input-group">
                    <input type="text" class="form-control input-sm" placeholder="Enter your server name"
                           name="server-name">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-sm" type="submit">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection

