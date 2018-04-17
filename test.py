# Copyright 2018 BACnet Gateway.  All rights reserved.

import requests

instances = [
    3006672,
    3006624,
    3006586,
    3006560,
    3006664,
    3006616,
    3006578,
    3006552,
    3007505,
    3019080,
    3007894,
    3007633,
    3008194,
    3019597,
    3007938,
    3019412,
    3019317,
    3019266,
    3019035,
    3019170,
    3019126,
    3009875,
    3010431,
    3010481,
    # 3019514,
    3011366,
    3001161,
    3001177,
    3001515,
    3001640,
    3011148,
    3010274,
    3009826,
    3009777,
    3001914,
    3001532,
    3001145
]


for i in instances:

    target_args = {
        'address': '10.12.0.250',
        'type': 'analogInput',
        'instance': i,
        'property': 'units'
    }

    #gateway_rsp = requests.post( 'http://192.168.1.195:8000/bg.php', data=target_args )

    gateway_rsp = requests.post( 'http://localhost:8000/bg.php', data=target_args )

    print( '%2d:'%i, gateway_rsp.status_code, gateway_rsp.reason, gateway_rsp.text )
