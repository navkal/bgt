import os

for i in range( 1, 26 ):
    print( i )
    os.system('python save_baselines.py -h localhost -p 8000')
