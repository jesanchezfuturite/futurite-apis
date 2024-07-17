@extends('layouts.system')

@section('app_content')
<!--begin::Row-->
<div class="row gx-5 gx-xl-10">
    <div class="col-xxl-12 mb-5 mb-xl-10">
        <!--begin::Table widget 9-->
        <div id="clients-container">
            <!-- Data will be appended here by JavaScript -->
        </div>
        <!--end::Table Widget 9-->
    </div>
</div>
<!--end::Row-->
@endsection

@section('app_scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#loadingIndicator').show();
        console.log("Fetching data from API...");

        $.ajax({
            url: "{{ url('/ads/client-stats-json') }}",
            method: 'GET',
            success: function(data) {
                console.log("Data fetched successfully:", data);
                $('#loadingIndicator').hide();

                var clientsHtml = '';
                data.forEach(function(client, index) {

                    if(client.percentage_spent === client.percentage_month){
                        icon = `<i class="las la-thumbs-up"></i>`;
                        colr = `bg-primary`;
                    }else{
                        icon = `<i class="las la-thumbs-up"></i>`;
                        colr = `bg-danger`;
                    }


                    var html = `
                        <div id="client-${index}" class="card card-flush h-xl-100 mb-5" style="display: none;">
                            <div class="card-header ribbon ribbon-top ribbon-vertical pt-5">
                                <div class="ribbon-label ${colr}">

                                    ${icon} " " ${client.percentage_spent} / ${client.percentage_month}%
                                </div>
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800">${replaceDimex(client.client_name)}</span>
                                    <span class="text-primary pt-1 fw-semibold fs-6">Presupuesto <strong>${formatCurrency(client.client_budget)}</strong></span>
                                    <span class="text-info pt-1 fw-semibold fs-6">Costo Actual <strong>${formatCurrency(client.current_cost)}</strong></span>
                                    <span class="text-muted pt-1 fw-semibold fs-6">Costo Mes Anterior <strong>${formatCurrency(client.last_cost)}</strong></span>
                                </h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fs-7 fw-bold border-0 text-gray-500">
                                                <th>Customer</th>
                                                <th>Campaign</th>
                                                <th>Impressions</th>
                                                <th>Impressions Current Month</th>
                                                <th>Impressions Last Month</th>
                                                <th>Clicks</th>
                                                <th>Clicks Current Month</th>
                                                <th>Clicks Last Month</th>
                                                <th>Cost</th>
                                                <th>Cost Current Month</th>
                                                <th>Cost Last Month</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;
                    client.customers.forEach(function(customer) {
                        customer.campaigns.forEach(function(campaign) {
                            html += `
                                <tr${campaign.indicators.impressions == 0 ? ' class="bg-danger text-white"' : ''}>
                                    <td>${replaceDimex(customer.customer_name)}</td>
                                    <td>${replaceDimex(campaign.campaign_name)}</td>
                                    <td>${campaign.indicators.impressions}</td>
                                    <td>${campaign.indicators.impressions_month}</td>
                                    <td>${campaign.indicators.impressions_last_month}</td>
                                    <td>${campaign.indicators.clics}</td>
                                    <td>${campaign.indicators.clics_month}</td>
                                    <td>${campaign.indicators.clics_last_month}</td>
                                    <td>${formatCurrency(campaign.indicators.paid)}</td>
                                    <td>${formatCurrency(campaign.indicators.paid_month)}</td>
                                    <td>${formatCurrency(campaign.indicators.paid_last_month)}</td>
                                </tr>
                            `;
                        });
                    });
                    html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    clientsHtml += html;
                });

                $('#clients-container').html(clientsHtml);
                showClient(0);
                setInterval(changeClient, 20000, data.length); // Cambia de cliente cada minuto
            },
            error: function(error) {
                console.error('Error fetching data:', error);
                $('#loadingIndicator').hide();
                $('#clients-container').html('<div class="alert alert-danger">Error fetching data</div>');
            }
        });

        var currentIndex = 0;

        function showClient(index) {
            $('#clients-container .card').hide();
            $(`#client-${index}`).show();
        }

        function changeClient(totalClients) {
            currentIndex = (currentIndex + 1) % totalClients;
            showClient(currentIndex);
        }

        // Recargar la página cada 90 minutos (5400000 ms)
        setInterval(function() {
            location.reload();
        }, 5400000);
    });

    function formatCurrency(value) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
    }

    function replaceDimex(text) {
        return text.replace(/dimex/gi, 'DIM');
    }
</script>
@endsection
