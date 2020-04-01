@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class = "col-6">
            <map-component />
        </div>
        <div class = "col-6">
            <control-panel-component />
        </div>
        <add-turbine-modal></add-turbine-modal>
        <add-panel-modal> </add-panel-modal>
        <add-edit-zone-modal></add-edit-zone-modal>
    </div>
</div>
@endsection
