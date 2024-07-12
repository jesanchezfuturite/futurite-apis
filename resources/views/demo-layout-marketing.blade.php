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
    <div class="container mt-5">
        <h1 class="mb-4">Google Ads Campaigns Statistics</h1>
        <div id="loadingIndicator">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Loading data, please wait...</p>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Customer Name</th>
                    <th>Campaign Name</th>
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
            <tbody id="statistics">
                <!-- Data will be appended here -->
            </tbody>
        </table>
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
                        client.customers.forEach(function(customer) {
                            customer.campaigns.forEach(function(campaign) {
                                var rowClass = campaign.indicators.impressions === 0 ? 'highlight-zero-impressions' : '';
                                html += `
                                    <tr class="${rowClass}">
                                        <td>${client.client_name}</td>
                                        <td>${customer.customer_name}</td>
                                        <td>${campaign.campaign_name}</td>
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
