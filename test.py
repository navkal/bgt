# Copyright 2018 BACnet Gateway.  All rights reserved.

import requests

for i in range( 1, 10 ):

    gateway_rsp = requests.post( 'http://192.168.1.195:8000/bg.php', data={ 'caller': 'Python' } )

    print( '%2d:'%i, gateway_rsp.status_code, gateway_rsp.reason, gateway_rsp.text )
