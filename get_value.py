import requests
import json


# Get BACnet property
def get_present_value( instance ):

    if instance:
        # Caller supplied non-empty instance

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
                result = str( int( dc_data['presentValue'] ) ) + ',' + dc_data['units']
            else:
                result = dc_data['message'] + ','

        else:
            result = dc_bn_rsp['message'] + ','

    else:
        # Caller supplied empty instance
        result = ','

    return result
