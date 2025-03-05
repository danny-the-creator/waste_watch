import requests
from flask import Flask, request, jsonify
from time import sleep
from nacl.public import PrivateKey
from nacl.signing import SigningKey, VerifyKey
from nacl.exceptions import BadSignatureError
from nacl.encoding import Base64Encoder
import uuid
import random 
import string
import os
import threading


# Create a small Flask webserver to answer api calls
app = Flask(__name__)


# Endpoint to let client know they are registered by the server
@app.route('/api/registered', methods=['GET'])
def registeredApi():
    # Verify the request signature
    request_id = request.headers.get("X-Request-Id")
    signature = request.headers.get("X-Signature")
    try:
        server_verify_key.verify(request_id, signature)
    except BadSignatureError:
        print("[ IN  ]: Bad signature (Registered message)")
        return jsonify({"message": "Failed: Bad signature"})
    
    # Save the server public key persistently
    global registered
    registered = True
    storeKey('serverKey.pub', SRV_KEY)
    print("[ IN  ]: Client registered")
    return jsonify({"message": "Registered confirmation accepted"})


# Endpoint to make the client forcefully block or unblock the lid
@app.route('/api/set-lid', methods=['GET'])
def lidAPi():
    # Check if we are registered by the server
    if not registered:
        print("[ IN  ]: Not registered (Set lid request)")
        return jsonify({"message": "Failed: Not registered"})

    # Verify the request signature
    request_id = request.headers.get("X-Request-Id")
    signature = request.headers.get("X-Signature")
    try:
        server_verify_key.verify(request_id, signature)
    except BadSignatureError:
        print("[ IN  ]: Bad signature (Set lid request)")
        return jsonify({"message": "Failed: Bad signature"})
    
    # Check if the params are correct, if so, block or unblock the lid (override full/empty block)
    if ("Block-Lid" in request.params):
        blockLid = request.params.get("Block-Lid")
        
        # 
        # TODO: Block or unblock the lid (override block)
        # 

        print("[ IN  ]: Success (Set lid request)")
        return jsonify({"message": "Success"})
    else:
        print("[ IN  ]: Invalid params (Set lid request)")
        return jsonify({"message": "Failed: Invalid params"})


# Endpoint to make the client lock or unlock the service lid
@app.route('/api/set-service-lid', methods=['GET'])
def serviceLidApi():
    # Check if we are registered by the server
    if not registered:
        print("[ IN  ]: Not registered (Set service lid request)")
        return jsonify({"message": "Failed: Not registered"})

    # Verify the request signature
    request_id = request.headers.get("X-Request-Id")
    signature = request.headers.get("X-Signature")
    try:
        server_verify_key.verify(request_id, signature)
    except BadSignatureError:
        print("[ IN  ]: Bad signature (Set service lid request)")
        return jsonify({"message": "Failed: Bad signature"})
    
    # Check if the params are correct, if so, lock or unlock the service lid
    if ("Block-Lid" in request.params):
        lockServiceLid = request.params.get("Block-Lid")
        
        # 
        # TODO: Lock or unlock the service lid
        # 

        print("[ IN  ]: Success (Set service lid request)")
        return jsonify({"message": "Success"})
    else:
        print("[ IN  ]: Invalid params (Set service lid request)")
        return jsonify({"message": "Failed: Invalid params"})


# Return the first line of a given file or None when it non-existent
def loadKey(keyFile):
    if not os.path.exists(keyFile):
        return None
    with open(keyFile, 'rb') as file:
        for line in file:
            return line.strip()


# Store given text in a given file
def storeKey(keyFile, key):
    with open(keyFile, 'wb') as file:
        file.write(key.encode(encoder=Base64Encoder))


# Executes a GET api call with give url, headers and parameters
def make_api_call(url, params=None, headers=None):
    try:
        response = requests.get(url, params=params, headers=headers)
        response.raise_for_status()
        print(f"[ IN  ]: Response Content: {response.text}")
        return response
    # Handle any exceptions that might occur
    except requests.exceptions.HTTPError as errh:
        print(f"HTTP Error: {errh}")
    except requests.exceptions.ConnectionError as errc:
        print(f"Error Connecting: {errc}")
    except requests.exceptions.Timeout as errt:
        print(f"Timeout Error: {errt}")
    except requests.exceptions.RequestException as err:
        print(f"Something went wrong: {err}")


# Sends connection request to server by exchanging public keys and id
def sendHello():
    # Send a request to the api and receive the server public key, try again if failed
    statusCode = 0
    while (statusCode != 200):
        headers = {
            "X-Device-Id": CLIENT_ID,
            "X-Public-Key": CLT_PUB_KEY
        }
        print("[ OUT ]: Sent Hello request")
        response = make_api_call(f"{SRV_API}/hello", headers=headers)   
        if (response == None or response.status_code != 200):
            print("Retrying send Hello request in 2s...")
            sleep(2)
        else:
            global SRV_KEY 
            global server_verify_key
            SRV_KEY = response.headers.get('X-Public-Key')
            server_verify_key = VerifyKey(SRV_KEY, encoder=Base64Encoder)
        if (response != None):
            statusCode = response.status_code
    print("Handshake done!")


# Send the current status of a provided component to the server, if failed, try again
def sendStatus(type, status):
    if not registered:
        return False
    statusCode = 0
    while (statusCode != 200):
        params = { type: status }

        # Create a random unique request id and sign it with our sign key
        requestId = ''.join(random.choice(string.ascii_letters + string.digits + string.punctuation) for _ in range(128))
        signature = signing_key.sign(requestId.encode('utf-8'))
        encoded_signature = Base64Encoder.encode(signature.signature).decode('utf-8')

        headers = {
            "X-Device-Id": CLIENT_ID,
            "X-Request-Id": requestId,
            "X-Signature": encoded_signature
        }

        response = make_api_call(f"{SRV_API}/update", params=params, headers=headers)
        if (response == None or statusCode != 200):
            print(f"Retrying send {type} status in 2s...")
            sleep(2)
        if (response != None):
            statusCode = response.status_code

# Start the api server to handle incoming api calls
def run_flask():
    app.run(host='0.0.0.0', port=80)


# Set API and Signature constants
SRV_API = "https://maarten.familievandort.nl/api"
SRV_KEY = loadKey('serverKey.pub')
CLT_PRV_KEY = loadKey('clientPrivateKey.pub')
CLT_PUB_KEY = loadKey('clientPublicKey.pub')
CLIENT_ID = "%012X" % uuid.getnode()        # Set the serial number of this trashcan to its MAC address value
REGISTER_TIMEOUT = 10                       # Timeout in sec to send a new hello request if not yet registered


# Create new client keys if non-existent
if (CLT_PRV_KEY == None or CLT_PUB_KEY == None):
    new_private_key = PrivateKey.generate()
    new_public_key = new_private_key.public_key
    storeKey('clientPrivateKey.pub', new_private_key)
    storeKey('clientPublicKey.pub', new_public_key)


# Determine wether the client already is registered by the server
registered = (SRV_KEY != None)


# Create objects to sign and verify signatures
signing_key = SigningKey(CLT_PRV_KEY, encoder=Base64Encoder)
server_verify_key = VerifyKey(SRV_KEY, encoder=Base64Encoder) if SRV_KEY != None else None
client_verify_key = VerifyKey(CLT_PUB_KEY, encoder=Base64Encoder)


# Start the program
if __name__ == "__main__":
    # Start the api server in a separate thread
    api_thread = threading.Thread(target=run_flask)
    api_thread.daemon = True  # This allows the thread to exit when the main program exits
    api_thread.start()

    # If not registered, send a new hello request every specified time period until registered
    while not registered:
        sendHello()
        sleep(REGISTER_TIMEOUT)