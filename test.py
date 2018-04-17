# Copyright 2018 BACnet Gateway.  All rights reserved.

import requests

co2_instances = [
    3011592,
    3001201,
    3011579,
    3006672,
    3006624,
    3006586,
    3006560,
    3007275,
    3019364,
    3019557,
    3007508,
    3019083,
    3007897,
    3007636,
    3008197,
    3019600,
    3007941,
    3019415,
    3019320,
    3019269,
    3019038,
    3019173,
    3019129,
    3009878,
    3010434,
    3010484,
    3019512,
    3011369,
    3001503,
    3001628,
    3011151,
    3010277,
    3009829,
    3009780,
    3002981,
    3002985,
    3002965,
    3003003,
    3003014,
    3003025,
    3003036,
    3006131,
    3006160,
    3006197,
    3006246,
    3006355,
    3001836,
    3001810,
    3001784,
    3001758,
    3001732,
    3001706,
    3001680,
    3001654,
    3001477,
    3001451,
    3001425,
    3001399,
    3001373,
    3001347,
    3001321,
    3001295,
    3001269,
    3001243,
    3001933,
    3003056,
    3019714,
    3006439,
    3001221,
    3001888,
    3001862,
    3014200
]


temp_instances = [
    3001071,
    3011595,
    3011582,
    3006664,
    3006616,
    3006578,
    3006552,
    3007272,
    3019361,
    3019554,
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
    3019514,
    3011366,
    3001161,
    3001177,
    3001515,
    3001640,
    3011148,
    3010274,
    3009826,
    3009777,
    3002974,
    3002993,
    3002964,
    3002996,
    3003007,
    3001090,
    3003018,
    3003029,
    3006123,
    3006152,
    3006189,
    3006238,
    3006347,
    3001848,
    3001822,
    3001796,
    3001770,
    3001744,
    3001718,
    3001692,
    3001666,
    3001489,
    3001463,
    3020712,
    3001437,
    3001411,
    3001385,
    3001359,
    3001333,
    3001307,
    3001281,
    3001255,
    3001935,
    3003049,
    3019750,
    3006431,
    3019751,
    3001900,
    3001874,
    3001145,
    3001532,
    3001549,
    3001914,
    3000165
]

def test( instances ):

    for i in instances:

        target_args = {
            'address': '10.12.0.250',
            'type': 'analogInput',
            'instance': i,
            'property': 'presentValue'
        }

        #gateway_rsp = requests.post( 'http://192.168.1.195:8000/bg.php', data=target_args )

        gateway_rsp = requests.post( 'http://localhost:8000/bg.php', data=target_args )

        print( '%2d:'%i, gateway_rsp.status_code, gateway_rsp.reason, gateway_rsp.text )

test( temp_instances )
test( co2_instances )
