@extends('layouts.system')

@section('app_content')

<ol class="breadcrumb text-muted fs-6 fw-semibold">
    <li class="breadcrumb-item"><a href="#" class="">Home</a></li>
    <li class="breadcrumb-item"><a href="#" class="">Google Ads</a></li>
    <li class="breadcrumb-item text-muted">Configuración</li>
</ol>
<br>
<div class="row">
    <!--begin::Alert-->
    <div class="alert alert-primary d-flex align-items-center p-5">
        <!--begin::Icon-->
        <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>
        <!--end::Icon-->

        <!--begin::Wrapper-->
        <div class="d-flex flex-column">
            <!--begin::Title-->
            <h4 class="mb-1 text-dark">Importante</h4>
            <!--end::Title-->

            <!--begin::Content-->
            <span>Para que un cliente se enliste aqui, es necesario que tenga un <strong>servicio de ADS configurado en Ongoing</strong></span>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
</div>

<div class="row">
    <div class="card card-p-0 card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <span class="svg-icon fs-1 position-absolute ms-4">...</span>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Buscar..." />
                </div>
                <!--end::Search-->
                <!--begin::Export buttons-->
                <div id="kt_datatable_example_1_export" class="d-none"></div>
                <!--end::Export buttons-->
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle border rounded table-row-dashed fs-6 g-5" id="kt_datatable_example">
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase">
                        <th class="min-w-100px">Cliente</th>
                        <th class="min-w-100px">Customers</th>
                        <th class="text-end min-w-75px">Inversión</th>
                        <th class="text-end min-w-75px">Fecha seguimiento</th>
                        <th class="text-end min-w-75px"></th>
                    </tr>
                    <!--end::Table row-->
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($clientes as $cc => $c)
                    <tr class="odd">
                        <td>
                            {{ $c["name"] }}
                        </td>
                        <td>
                            {{ $c["customers"] }}
                        </td>

                        <td class="text-end pe-0">$ {{ number_format($c["ammount"],2) }}</td>
                        <td class="text-end pe-0">{{ $c["starting"] }}</td>
                        <td class="text-end pe-0">
                            <a href="#"
                                class="btn btn-icon btn-primary"
                                data-bs-toggle="modal" data-bs-target="#kt_modal_config"
                                onclick="getInfo({{ $c['id'] }},'{{ $c['name']}}')">
                                    <i class="las la-tools fs-2 me-2"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Desvinculación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas desvincular este cliente?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Desvincular</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar los customers -->
<div class="modal fade" tabindex="-1" id="kt_modal_config">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="title_modal">Modal title</h3>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Alert -->
                    <div class="alert alert-primary d-flex align-items-center p-5">
                        <!-- Icon -->
                        <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>
                        <!-- Wrapper -->
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Instrucciones</h4>
                            <span>
                                Asocia el Google ADS Customer a nuestros clientes para medir su desempeño.
                                Los customer que se muestran tienen estatus <code>ENABLED</code> en la plataforma ADS.
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="d-flex bd-highlight">
                        <div class="p-2 flex-grow-1 bd-highlight">
                            Aquí va la info de las campañas.
                        </div>
                        <div class="p-2 bd-highlight">
                            <div class="d-flex flex-column bd-highlight mb-3">
                                <div class="p-2 bd-highlight">
                                    <select id="customerSelectModal" class="form-select form-select-transparent" aria-label="Customer ADS">
                                        <option value="">Customers list</option>
                                    </select>
                                    <button id="customerAddButton" disabled onclick="relateToClient()" class="btn btn-light-primary">Asociar</button>
                                </div>
                                <hr>
                                <div class="p-2 bd-highlight">
                                    <h3 id="customerAssociatedTitle" style="display: none;">Customers asociados:</h3>
                                </div>
                                <ul id="customerAssociatedList" class="p-2 bd-highlight list-group" style="display: none;"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- hidden fields to pivot info -->
<input type="hidden" id="customersHiddenList" value=""/>
<input type="hidden" id="clientIdSelected" value=""/>

@endsection

@section('app_scripts')

<script>

"use strict";

// Class definition
var KTDatatablesExample = function () {
    // Shared variables
    var table;
    var datatable;

    // Private functions
    var initDatatable = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        tableRows.forEach(row => {
            const dateRow = row.querySelectorAll('td');
            const realDate = moment(dateRow[3].innerHTML, "DD MMM YYYY, LT").format(); // select date from 4th column in table
            dateRow[3].setAttribute('data-order', realDate);
        });

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
        });
    }

    // Hook export buttons
    var exportButtons = () => {
        const documentTitle = 'Customer Orders Report';
        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: documentTitle
                },
                {
                    extend: 'excelHtml5',
                    title: documentTitle
                },
                {
                    extend: 'csvHtml5',
                    title: documentTitle
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle
                }
            ]
        }).container().appendTo($('#kt_datatable_example_buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();

                // Get clicked export value
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                // Trigger click event on hidden datatable export buttons
                target.click();
            });
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search/
    var handleSearchDatatable = () => {
        const filterSearch = document.querySelector('[data-kt-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_datatable_example');

            if ( !table ) {
                return;
            }

            initDatatable();
            exportButtons();
            handleSearchDatatable();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesExample.init();
});

/* funciones para interactuar con el modal */
function getInfo(id, name) {
    const title = "Configuración de " + name;
    $("#title_modal").empty();
    $("#title_modal").append(title);
    $("#clientIdSelected").val(id);

    //  get info customers not assigned
    getADSCustomers(id);
}

/* este metodo actualiza la informacion de los customers que no estan asociados a algun cliente */
function getADSCustomers(id) {
    $.ajax({
        method: "POST",
        url: "{{ url('/ads/config-customers-json') }}",
        data: {
            "_token": "{{ csrf_token() }}",
            "id": id
        }
    })
    .done(function(info) {
        if (info.count > 0) {
            // Actualizamos el hidden input
            $('#customersHiddenList').val(JSON.stringify(info.info));

            // Limpiamos el select y la lista de clientes asociados
            $("#customerSelectModal").empty();
            $("#customerAssociatedList").empty();
            $("#customerAssociatedTitle").empty();

            // Agregamos las opciones al select
            $("#customerSelectModal").append("<option value='0'>Selecciona...</option>");
            for (const [key, value] of Object.entries(info.info)) {
                $("#customerSelectModal").append("<option value='" + value.customer_id + "'>" + value.descriptive_name + "</option>");
            }

            // En tu función getADSCustomers, al agregar los elementos de la lista
            if (info.associated_count > 0) {
                $("#customerAssociatedTitle").text("Customers asociados:");
                $("#customerAssociatedTitle").show(); // Mostrar el título si hay elementos
                $("#customerAssociatedList").show(); // Mostrar la lista si hay elementos

                for (const [key, value] of Object.entries(info.associated)) {
                    const listItem = `
                        <li class="list-group-item list-group-item-light" data-customer-id="${value.customer_id}">
                            <span class="d-flex flex-column align-items-start">
                                <span class="text-primary">
                                    <strong>${value.descriptive_name}</strong>
                                    <a href="#" class="disconnect-link" data-customer-id="${value.customer_id}" data-cliente-id="${id}">
                                        <i class="ki-duotone ki-disconnect">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    </a>
                                </span>
                                <span class="text-gray-600">${value.customer_id}</span>
                            </span>
                        </li>
                    `;
                    $("#customerAssociatedList").append(listItem);
                }

                // Añadir evento de clic para abrir el modal de confirmación
                $(".disconnect-link").click(function (e) {
                    e.preventDefault();
                    const customerId = $(this).data('customer-id');
                    const clienteId = $(this).data('cliente-id'); // Asegúrate de que este dato esté disponible
                    $('#confirmButton').data('customer-id', customerId); // Guardar el ID del customer en el botón de confirmación
                    $('#confirmButton').data('cliente-id', clienteId); // Guardar el ID del cliente en el botón de confirmación
                    $('#kt_modal_config').modal('hide'); // Cerrar el modal principal
                    $('#confirmModal').modal('show');
                });
            } else {
                $("#customerAssociatedTitle").hide();
                $("#customerAssociatedList").hide();
            }
        }
    });
}

/* metodo para habilitar o deshabilitar el boton */
$("#customerSelectModal").change(function() {
    // obtenemos la seleccion
    const customer = $("#customerSelectModal").val();

    if (customer != 0) {
        $("#customerAddButton").removeAttr('disabled');
    } else {
        $("#customerAddButton").attr('disabled', 'disabled');
    }
});

/* este metodo guarda la relacion customer - cliente en la base de datos */
function relateToClient() {
    // obtenemos la seleccion
    const customer  = $("#customerSelectModal").val();
    const clienteId = $("#clientIdSelected").val(); // Obtener el cliente ID almacenado

    if (customer != 0) {
        $("#customerAddButton").removeAttr('disabled');
    }

    $.ajax({
        method: "POST",
        url: "{{ url('/ads/relate-customer') }}", // Asegúrate de que esta URL apunte a tu ruta backend
        data: {
            "_token": "{{ csrf_token() }}", // Token CSRF para seguridad
            "cliente_id": clienteId,
            "customer_id": customer
        },
        success: function(response) {
            // Manejar la respuesta del backend aquí
            if (response.success) {
                // Actualizar la lista de clientes asociados y el select
                getADSCustomers(response.clienteId);
            } else {
                console.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            // Manejar errores aquí
            console.error(error);
        }
    });
}

// Evento para el botón de confirmación
$('#confirmButton').click(function() {
    const customerId = $(this).data('customer-id');
    const clienteId = $(this).data('cliente-id');

    // Aquí se hace el llamado AJAX para desvincular el cliente
    $.ajax({
        method: "POST",
        url: "{{ url('/ads/unlink-customer') }}", // Asegúrate de que esta URL apunte a tu ruta backend
        data: {
            "_token": "{{ csrf_token() }}", // Token CSRF para seguridad
            "cliente_id": clienteId,
            "customer_id": customerId
        },
        success: function(response) {
            // Manejar la respuesta del backend aquí
            if(response.success) {
                // Eliminar el elemento de la lista
                $('#customerAssociatedList').find(`li[data-customer-id="${customerId}"]`).remove();

                // Ocultar la lista si está vacía
                if($('#customerAssociatedList').children().length === 0) {
                    $('#customerAssociatedTitle').hide();
                    $('#customerAssociatedList').hide();
                }

                $('#confirmModal').modal('hide');
                $('#kt_modal_config').modal('show'); // Volver a abrir el modal principal si es necesario
                getADSCustomers(response.clienteId); // Actualizar la lista de clientes asociados
            } else {
                console.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            // Manejar errores aquí
            console.error(error);
        }
    });
});

</script>

@endsection
