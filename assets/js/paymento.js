jQuery(function($) {
        var data = {
            'Api-Key': paymento_vars.api_key // Use the localized variable from PHP
        };
    
        var paymento_helth_check = document.getElementById("paymento_helth_check");
        var req = $.get({
            url: paymento_vars.rest_url + 'paymento/health', // Use dynamic REST API URL
            data,
            headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': 'true'
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                paymento_helth_check.innerHTML = '<span style="padding:5px 10px; background-color:#f52f57; color:#fff;border-radius:5px;">Error</span>';
            },
            success: function(response) {
                if (response.success)
                    paymento_helth_check.innerHTML = '<span style="padding:5px 10px; background-color:#83f28f;border-radius:5px;">Good</span>';
                else
                    paymento_helth_check.innerHTML = '<span style="padding:5px 10px; background-color:#f52f57;color:#fff;border-radius:5px;">Bad</span>';
            }
        });
    
        // Merchant name is now loaded via PHP, no JavaScript call needed
			
					var handle_description = (data) => {
						var desc_to_change = document.getElementById("woocommerce_paymento_gateway_confirmation_description");
						if(data == 0)
								desc_to_change.innerHTML = '</br>Users will be redirected to your site immediately after making the payment. The invoice status will be set to "On Hold" until the transaction is confirmed. ';
							else if(data == 1)
								desc_to_change.innerHTML = '</br>Users will remain on the Paymento page until the transaction is confirmed. They will be redirected to your site once the payment is verified. ';
							else if(data == 2)
								desc_to_change.innerHTML = '</br>Users will be redirected to your site immediately after making the payment. The invoice status will be marked as "Paid" once the transaction is broadcasted before confirmation.';
							else
								desc_to_change.innerHTML = 'else';   
					}

					var x = document.getElementById("woocommerce_paymento_gateway_confirmation").parentElement;
					x.innerHTML += '<div id="woocommerce_paymento_gateway_confirmation_description" style="width: 50%;text-align: justify;"></div>';
					var data= $('select#woocommerce_paymento_gateway_confirmation').val();
					handle_description(data); 

					$('select#woocommerce_paymento_gateway_confirmation').change(function(){
						var data= $(this).val();
						handle_description(data); 
					});

                });
