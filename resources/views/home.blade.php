@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class = "col-7">
            <map-component />
        </div>
        <div class = "col-5">
            <control-panel-component />
        </div>
        <add-turbine-modal></add-turbine-modal>
        <add-panel-modal> </add-panel-modal>
    </div>
</div>
@endsection
