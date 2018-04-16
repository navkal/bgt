# Copyright 2018 BACnet Gateway.  All rights reserved.

import requests

for i in range( 1, 11 ):

    target_args = {
        'address': '10.12.0.250',
        'type': 'analogInput',
        'instance': 3006238,
        'property': 'presentValue'
    }

    #gateway_rsp = requests.post( 'http://192.168.1.195:8000/bg.php', data=target_args )

    gateway_rsp = requests.post( 'http://localhost:8000/bg.php', data=target_args )

    print( '%2d:'%i, gateway_rsp.status_code, gateway_rsp.reason, gateway_rsp.text )
