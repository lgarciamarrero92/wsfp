@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(isset(Auth::user()->details['demo']))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{__("There is already some data uploaded in this demo, run simulations and inspect the results and charts tabs to quickly interact with the tool. You are free to upload more data for testing purposes.")}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
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
