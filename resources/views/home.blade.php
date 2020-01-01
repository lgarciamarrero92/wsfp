@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class = "col-8">
            <map-component />
        </div>
        <div class = "col-4">
            <control-panel-component/>
        </div>
    </div>
</div>
@endsection
