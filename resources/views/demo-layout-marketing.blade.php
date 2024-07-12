<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Ads Campaigns Statistics</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #loadingIndicator {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        .highlight-zero-impressions {
            background-color: #f8d7da;
        }

    </style>
</head>
<body>
    <div id="statistics" >


    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function formatCurrency(value) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
        }

        $(document).ready(function() {
            $('#loadingIndicator').show();
            console.log("Fetching data from API...");

            $.ajax({
                url: "{{ url('/ads/client-stats-json') }}",
                method: 'GET',
                success: function(data) {
                    console.log("Data fetched successfully:", data);
                    $('#loadingIndicator').hide();

                    var html = '';
                    data.forEach(function(client) {
                        html += '<div  class="card"><div class="card-body"><h5 class="card-title">'+client.client_name+'</h5>';
                        client.customers.forEach(function(customer) {
                            html += '<p class="card-text">'+customer.customer_id+'</p>';
                            html += '<p class="card-text"><strong>Campañas:</strong>';
                            html += '<table>';
                                html += '<tr>';
                                    html +='<th>Nombre</th><th>Impresiones hoy</th><th>Impresiones del mes</th><th>Impresiones mes anterior</th>';
                                    html +='<th>Clics hoy</th><th>Clics del mes</th><th>Clics mes anterior</th>';
                                    html +='<th>Costo hoy</th><th>Costo del mes</th><th>Costo mes anterior</th>';

                                html +='</tr>';
                            customer.campaigns.forEach(function(campaign) {
                                html +='<tr>';
                                    html +='<td>'+campaign.campaign_name+'</td><td>'+campaign.indicators.impressions+'</td><td>'+campaign.indicators.impressions_month+'</td><td>'+campaign.indicators.impressions_last_month+'</td>';
                                    html +='<td>'+campaign.indicators.clics+'</td><td>'+campaign.indicators.clics_month+'</td><td>'+campaign.indicators.clics_last_month+'</td>';
                                    html +='<td>'+formatCurrency(campaign.indicators.paid)+'</td><td>'+formatCurrency(campaign.indicators.paid_month)+'</td><td>'+formatCurrency(campaign.indicators.paid_last_month)+'</td>';
                                html +='</tr>';
                            });
                            html += '</table>';
                        });
                        html += '</div></div>';
                    });

                    $('#statistics').html(html);
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                    $('#loadingIndicator').hide();
                    $('#statistics').html('<tr><td colspan="12" class="text-danger">Error fetching data</td></tr>');
                }
            });
        });
    </script>
</body>
</html>
