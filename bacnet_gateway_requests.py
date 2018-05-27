import requests
import json


# Request present value and units for the supplied instance
def get_value_and_units( instance ):

    value = None
    units = None

    if str( instance ).isdigit() and int( instance ) > 0:
        # Instance appears to be valid

        # Set up request arguments
        args = {
            'facility': 'ahs',
            'instance': instance
        }

        # Issue request to HTTP service
        #host = '192.168.1.186'
        #host = '192.168.1.169'
        host = 'localhost'
        url = 'http://' + host + ':8000/bg.php'
        gateway_rsp = requests.post( url, data=args )

        # Convert JSON response to Python dictionary
        dc_rsp = json.loads( gateway_rsp.text )

        # Extract BACnet response from the dictionary
        dc_bn_rsp = dc_rsp['bacnet_response']

        # Extract result from BACnet response
        if ( dc_bn_rsp['success'] ):

            dc_data = dc_bn_rsp['data']

            if dc_data['success']:
                value = dc_data['presentValue']
                units = dc_data['units']

    return value, units