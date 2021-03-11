import nmap
import pandas as pd
import os
import time

CSV_FILE_NAME = 'address_book.csv'
RUN_INTERVAL = 30 # min

def is_uaa(mac_str):
    try:
        mac_bin = str(bin(int(mac_str.replace(':', ''), 16))).split('b')[-1]
        is_UAA = True if mac_bin[6] == '0' else False
        return is_UAA
    except:
        return False

def update_address_book():
    # Get all currently connected peers
    nm = nmap.PortScanner()
    print("Scanning network for peers")
    res = nm.scan('192.168.20.0/24', arguments='-sn')
    print("Finished")

    discovered_hosts = []
    for h in nm.all_hosts():
        if 'mac' in nm[h]['addresses']:
            discovered_hosts.append({'mac' : nm[h]['addresses']['mac'], 'ipv4' : nm[h]['addresses']['ipv4'], 'hostname' : nm[h]['hostnames'][0]['name'], 'is_uaa' : is_uaa(nm[h]['addresses']['mac']) })

    # Add any new peers to address book
    if(os.path.isfile(CSV_FILE_NAME)):
        df = pd.read_csv(CSV_FILE_NAME, index_col=0)
    else:
        df = pd.DataFrame(columns=['mac','ipv4', 'hostname', 'is_uaa'])

    for dh in discovered_hosts:
        # Check if file to write exists
        if(dh['mac'] not in df.values):
            print("Entry for {} doesn't exists. Adding..".format(dh['mac']))
            df = df.append(dh, ignore_index=True)
        else:
            print("Entry for {} already exists".format(dh['mac']))

    df.to_csv(CSV_FILE_NAME)

while True:
    print("Updating address book")
    update_address_book()
    time.sleep(RUN_INTERVAL * 60.0)
