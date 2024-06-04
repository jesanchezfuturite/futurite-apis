@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>
                <div class="card-body">
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <th>Id</th>
                        <th>Customer</th>
                        <th>Campaigns</th>
                        <th>Cliente Futurite</th>
                    </thead>
                    @foreach($customerList as $cL)
                        <tr>
                            <td>{{$cL->internal_id}}</td>
                            <td>{{$cL->descriptive_name}}</td>
                            <td>0</td>
                            <td>
                                <select name="" id="">
                                    
                                </select>
                            </td>
                        </tr>
                    @endforeach

                    <tfoot>
                        <th>Id</th>
                        <th>Customer</th>
                        <th>Campaigns</th>
                        <th>Cliente Futurite</th>
                    </tfoot>
                <!-- Puedes agregar más filas según lo necesites -->
                </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
$(document).ready( function () {
    new DataTable("#example")
} );
</script>


@endsection
