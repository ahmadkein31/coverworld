var config = 
{
    config: 
    {
        mixins: 
        {
        	'Magento_Customer/js/action/check-email-availability':
			{
				'Anowave_Ecopz/js/action/check-email-availability':true
			},
			'Magento_Checkout/js/action/place-order': 
			{
			    'Anowave_Ecopz/js/action/place-order': true
			}
        }
    }
};