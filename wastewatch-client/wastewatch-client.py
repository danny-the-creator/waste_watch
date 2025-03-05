import warnings
from gpiozero.exc import PWMSoftwareFallback
warnings.filterwarnings("ignore", category=PWMSoftwareFallback)     # Suppress GPIOZero warnings

from gpiozero import DistanceSensor, Button, LED
from time import sleep, time
import os
import board
import busio
import adafruit_ads1x15.ads1115 as ADS
from adafruit_ads1x15.analog_in import AnalogIn
import gpiod
import math
import paho.mqtt.client as mqtt
import ssl
from nacl.public import PrivateKey
from nacl.signing import SigningKey, VerifyKey
from nacl.exceptions import BadSignatureError
from nacl.encoding import Base64Encoder
import uuid
import random 
import string
import json


# Tweakable Constants
TRASH_LEVELS = [80, 52, 32]         # Threshold distances in cm for the trash levels (also serve as unique identifiers for logic)
LID_OPEN_TIME = 7                   # Time (sec) for the lid to be open
LID_CLOSING_WARNING_TIME = 3        # Time (sec) to warn user (with blinking LED) before closing lid
REGISTER_TIMEOUT = 10               # Timeout (sec) to send a new hello request if not yet registered
UPDATE_RETRY_TIMEOUT = 3            # Timeout (sec) to retry a failed update request

# Print color codes for nice debugging
GREEN = '\033[32m'
RED = '\033[31m'
ORANGE = '\033[33m'  # Actually yellow, as orange is not a standard ANSI color
RESET = '\033[0m'    # Reset to default color

# Constants for the gpiod library, logic and the servo's
CHIP = 'gpiochip0'  # The gpio chip of the RPi
PERIOD = 0.02       # 20ms  period time for Servo PWM timing
MIN_DUTY = 0.0005   # 0.5ms min duty time for Servo PWM timing
MAX_DUTY = 0.0025   # 2.5ms max duty time for Servo PWM timing

# Angles of Service Lid Servo's and Lid Servo when opened and closed (also serve as unique identifiers for logic)
LID_OPEN = 0
LID_CLOSED = 125
SERVICE_UNLOCKED = 90
SERVICE_LOCKED = 180

# Define the GPIO pins for the sensors and servo's
TRIG_PIN = 23
ECHO_PIN = 24
PROX_PIN = 17
LED_PIN = 27
LIDSERVO_PIN = 18
SERVICESERVO_PIN = 19      # Two servo's will be connected to this pin, moving identically

# Request the GPIO lines for the Lid servo and Service Lid servo's
chip = gpiod.Chip(CHIP)
lidServo = chip.get_line(LIDSERVO_PIN)
lidServo.request(consumer='Lid Servo Control', type=gpiod.LINE_REQ_DIR_OUT)
serviceServo = chip.get_line(SERVICESERVO_PIN)
serviceServo.request(consumer='Service Servo Control', type=gpiod.LINE_REQ_DIR_OUT)

# Create instances for the sensors
proxSens = Button(PROX_PIN)                                     # Lid proximity sensor (physically adjustable)
led = LED(LED_PIN)                                              # Lid blocking indication LED
usDistSens = DistanceSensor(echo=ECHO_PIN, trigger=TRIG_PIN)    # Ultrasonic Distance Sensor                    
ads = ADS.ADS1115(busio.I2C(board.SCL, board.SDA))              # Connect to ADC to read IR Distance Sensor Voltage
irDistSens = AnalogIn(ads, ADS.P0)                              # Infrared Distance Sensor, which is connected to the ADC

# Converts 2 given distances (cm) to a trash level
def getTrashLevel(distance1, distance2):
    avg = (distance1 + distance2) / 2
    return TRASH_LEVELS[2] if avg <= TRASH_LEVELS[2] else (TRASH_LEVELS[1] if avg <= TRASH_LEVELS[1] else TRASH_LEVELS[0])

# Sets the angle of a given servo to a given value [0, 180]
def setServo(servoLine, angle):
    # Calculate the target pulse for the given angle
    duty = MIN_DUTY + (MAX_DUTY - MIN_DUTY) * angle / 180
    on_time = duty
    off_time = PERIOD - on_time
    # Send the target pulse for about 1sec to the given line
    for _ in range(50):
        servoLine.set_value(1)
        sleep(on_time)
        servoLine.set_value(0)
        sleep(off_time)

# Convert IR Distance Sensor voltage to distance in cm
def getIRDistance(voltage):
    if voltage < 0.4:
        return 100
    elif voltage > 3.3:
        return -1
    distance = 27.86 * math.pow(voltage, -1.15)
    return round(distance, 2)

# Clear the terminal screen
def clearScreen():
    os.system('cls' if os.name == 'nt' else 'clear')

# Return the first line of a given file or None when it non-existent
def loadKey(keyFile):
    if not os.path.exists(keyFile):
        return None
    with open(keyFile, 'rb') as file:
        for line in file:
            return line

# Store given key in a given file
def storeKey(keyFile, keyBytes):
    with open(keyFile, 'wb') as file:
        file.write(keyBytes)

# Print what we are about to send to a given server endpoint, convert the json to string and send it
def publish(topic, json_message):
    message = json.dumps(json_message)
    print("\n[" + RED + "OUT" + RESET + "] " + topic + "\n\t" + ORANGE + message + RESET)
    client.publish(topic, message)

# Create a new random id and a corresponding signature, this pair can be to the server to verify our identity
def createSignaturePair():
        requestId = ''.join(random.choice(string.ascii_letters + string.digits + string.punctuation) for _ in range(128))
        signature = signing_key.sign(requestId.encode('utf-8'))
        encoded_signature = Base64Encoder.encode(signature.signature).decode('utf-8')
        return (requestId, encoded_signature)

# Check if the given id and signature are valid for our keys, if not return error code 403 and print error, else return code 200
def checkSignaturePair(id, signature):
    try:
        server_verify_key.verify(id.encode('utf-8'), Base64Encoder.decode(signature))
        return 200
    except BadSignatureError:
        print("Bad signature")
        return 403

# Check if received message has the expected parameters, if not return error code 400, else return code 200
def checkParams(data, expectedParams):
    for expectedParam in expectedParams:
        if (expectedParam not in data):
            print("Bad request (Too few arguments)")
            return 400
    return 200

# Check if we are registered, if not return error code 401 and print error, else return code 200
def checkRegistered():
    if not registered:
        print("Not registered")
    return 200 if registered else 401

# Send hello request with our public key to server, start listening on hello response endpoint
def sendHello():
    client.subscribe((f"wastewatch/{CLIENT_ID}/response/hello", 2))
    data = {"device_id": CLIENT_ID, 
            "public_key": CLT_PUB_KEY.decode(), 
            "response_on": f"wastewatch/{CLIENT_ID}/response/hello"}
    publish("wastewatch/hello", data)

# Handle server response on the request sent by sendHello(): If code is good, save server public key and create a signature verify object
def helloResponse(data):
    if registered:
        print("Receiving hello response but already registered, ignoring response")
        return
    code = checkParams(data, ['public_key', 'code'])
    if data['code'] == 200 and code == 200:
        global SRV_KEY
        global server_verify_key
        SRV_KEY = data['public_key']
        server_verify_key = VerifyKey(SRV_KEY, encoder=Base64Encoder)

# Handle the server notification that we are registered by the server: If valid, store server key. Send result code back
def handleRegistered(data):
    global registered
    if registered:
        print("Received registered but already registered, ignoring notification")
        return
    if SRV_KEY == None:
        print("Received registered notification but haven't received hello response yet, ignoring notification")
        return
    (response_id, signature) = createSignaturePair()
    code = checkParams(data, ['request_id', 'signature', 'response_on'])
    code = checkSignaturePair(data['request_id'], data['signature'])
    response_data = {"code": code, "device_id": CLIENT_ID, "response_id": response_id, "signature": signature}
    publish(data['response_on'], response_data)
    if code == 200:
        registered = True
        storeKey('serverKey.pub', SRV_KEY.encode())
        print("Client registered")

# Handle the server request to execute an action: If valid, change variables read by main. Send result code back
def handleAction(data):
    (response_id, signature) = createSignaturePair()
    code = checkParams(data, ['request_id', 'signature', 'response_on', 'body'])
    code = checkRegistered()
    code = checkSignaturePair(data['request_id'], data['signature'])
    response_data = {"code": code, "device_id": CLIENT_ID, "response_id": response_id, "signature": signature}
    publish(data['response_on'], response_data)

    if code == 200:
        global lidBlockedOverride
        global serviceLocked
        lidBlockedOverride = data['body']['block_lid']
        serviceLocked = data['body']['lock_service_lid']
        print(f"Lid blocked: {data['body']['block_lid']}, Service lid locked: {data['body']['lock_service_lid']}")

# Handle the server request to forget the server: If valid, delete all keys and exit
def handleForget(data):
    code = checkParams(data, ['request_id', 'signature'])
    code = checkRegistered()
    code = checkSignaturePair(data['request_id'], data['signature'])

    if code == 200:
        print("Forgetting server connection and removing all keys...")
        os.remove("serverKey.pub")
        os.remove("clientPublicKey.pub")
        os.remove("clientPrivateKey.pub")
        
        client.loop_stop()
        client.disconnect()
        print("Stopped")
        exit()

# Send trash level and whether the lid is jammed to the server and start listening on the update response endpoint
def sendUpdate(trashLevel, lidJammed):
    client.subscribe(f"wastewatch/{CLIENT_ID}/response/update")
    (request_id, signature) = createSignaturePair()
    body = {"trash_level": trashLevel, "lid_jammed": lidJammed}
    data = {"body": body, "device_id": CLIENT_ID, "request_id": request_id, "signature": signature, "response_on": f"wastewatch/{CLIENT_ID}/response/update"}
    publish(f"wastewatch/update", data)

# Handle the server response on the update request in sendUpdate(): If failed, retry
def updateResponse(data):
    (response_id, signature) = createSignaturePair()
    code = checkParams(data, ['response_id', 'signature', 'code'])
    code = checkRegistered()
    code = checkSignaturePair(data['response_id'], data['signature'])
    if data['code'] == 401:
        print("Update request failed (server doesn't see us as registered)")
    elif data['code'] == 403:
        print("Update request failed (our signature pair didn't match)")
    if code != 200 or data['code'] != 200:
        print(f"Resending update request in {UPDATE_RETRY_TIMEOUT}sec...")
        sleep(UPDATE_RETRY_TIMEOUT)
        sendUpdate(TRASH_LEVELS.index(trashLevel), lidJammed)

# Set device and key constants
SRV_KEY = loadKey('serverKey.pub')
CLT_PRV_KEY = loadKey('clientPrivateKey.pub')
CLT_PUB_KEY = loadKey('clientPublicKey.pub')
CLIENT_ID = "%012X" % uuid.getnode()        # Set the serial number of this trashcan to its MAC address value
registered = (SRV_KEY != None)

# Create new client keys if non-existent
if (CLT_PRV_KEY == None or CLT_PUB_KEY == None):
    privateKey = PrivateKey.generate()
    CLT_PRV_KEY = privateKey.encode(encoder=Base64Encoder)
    CLT_PUB_KEY = privateKey.public_key.encode(encoder=Base64Encoder)
    storeKey('clientPrivateKey.pub', CLT_PRV_KEY)
    storeKey('clientPublicKey.pub', CLT_PUB_KEY)

# Create objects to sign and verify signatures
signing_key = SigningKey(CLT_PRV_KEY, encoder=Base64Encoder)
server_verify_key = VerifyKey(SRV_KEY, encoder=Base64Encoder) if registered else None


#####################[ MQTT Setup ]#####################

# Stop waiting when connected to broker
def on_connect(client, userdata, flags, rc, properties):
    global brokerConnected
    brokerConnected = True
    print("Connected to broker with result code " + str(rc))

# When receiving a message, print it and handle its contents
def on_message(client, userdata, message):
    print("[" + GREEN + "IN" + RESET + " ] " + message.topic + "\n\t" + ORANGE + message.payload.decode() + RESET + "\n")
    data = json.loads(message.payload.decode())
    if message.topic == f"wastewatch/{CLIENT_ID}/response/hello":
        helloResponse(data)
    elif message.topic == f"wastewatch/{CLIENT_ID}/response/update":
        updateResponse(data)
    elif message.topic == f"wastewatch/{CLIENT_ID}/registered":
        handleRegistered(data)
    elif message.topic == f"wastewatch/{CLIENT_ID}/action":
        handleAction(data)
    elif message.topic == f"wastewatch/{CLIENT_ID}/forget":
        handleForget(data)
    else:
        print("Request or response not understood, ignoring message")

# Connect to the broker (Communication manager between 'server' and 'client', having a public IP)
brokerConnected = False
client = mqtt.Client(callback_api_version=mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = on_connect
client.on_message = on_message
client.username_pw_set('maarten', 'maarten')
client.tls_set(
    cert_reqs=ssl.CERT_NONE,
    tls_version=ssl.PROTOCOL_TLSv1_2
)
client.tls_insecure_set(False) # Set to true if tls problems occur
client.connect("maarten.familievandort.nl", 8883, 60)
client.loop_start()

# These variables will be changed by incoming server requests
lidBlockedOverride = False
serviceLocked = True
serviceWasLocked = False                 # This variable will ensure lid (un)locking will only happen on change

# These variables wil be sent to the server
lidJammed = False
trashLevel = TRASH_LEVELS[0]
lastTrashLevel = TRASH_LEVELS[1]        # This variable will ensure information will only be sent on change

########################################################


# Set lid related variables to default state
lidBlocked = False
userDetected = False
lidOpen = False
lidTimestamp = 0
ledBlinking = False

# Start the main loop 
try:
    # Wait until we are connected to broker
    while not brokerConnected:
        sleep(0.1)

    # If not registered, start listening on registered endpoint and keep sending hello to server until registered
    if not registered:
        client.subscribe((f"wastewatch/{CLIENT_ID}/registered", 2))
    while not registered:
        sendHello()
        if (not registered):
            print(f"Not yet registered by server, resending hello request in {REGISTER_TIMEOUT}sec...")
            sleep(REGISTER_TIMEOUT)
    
    # We are registered, stop listening on registered and hello-response endpoints and start listening on action and forget endpoints
    client.unsubscribe([f"wastewatch/{CLIENT_ID}/registered", f"wastewatch/{CLIENT_ID}/response/hello"])
    client.subscribe((f"wastewatch/{CLIENT_ID}/action", 2), (f"wastewatch/{CLIENT_ID}/forget", 2))

    # Close the lid
    setServo(lidServo, LID_CLOSED)

    while True:
        # If the lid is not blocking the sensors, measure the distance of both distance sensors and get the corresponding trash level
        if not lidOpen:
            trashLevel = getTrashLevel(usDistSens.distance * 100, getIRDistance(irDistSens.voltage))

        # If the trashlevel changed, send the updated information to the server (First iteration will always send)
        if trashLevel != lastTrashLevel:
            sendUpdate(TRASH_LEVELS.index(trashLevel), lidJammed)
            lastTrashLevel = trashLevel

        # Block the lid if the trash level is full and the lid is not open or if it is overridden by server request
        lidBlocked = (trashLevel == TRASH_LEVELS[2])
        lidBlocked = True if lidBlockedOverride else lidBlocked
        
        # If user is present and lid is not blocked, turn off the led and open lid
        # else, if user is present and lid is blocked, indicate with LED
        # else, (if the lid is going to close within 3 seconds, blink LED. Else turn off the led)
        if (not lidBlocked) and proxSens.is_pressed:
            led.off()
            ledBlinking = False
            if not lidOpen:
                setServo(lidServo, LID_OPEN)
                lidOpen = True
            lidTimestamp = time()
        elif proxSens.is_pressed and not ledBlinking:
            led.blink(0.3)
            ledBlinking = True
        else:
            if (time() - lidTimestamp <= LID_OPEN_TIME) and (time() - lidTimestamp >= LID_OPEN_TIME - LID_CLOSING_WARNING_TIME) and not ledBlinking:
                led.blink(0.3)
                ledBlinking = True
            else:
                led.off()
                ledBlinking = False
                
        # Close lid if the lid open timeout has expired
        if lidOpen and (time() - lidTimestamp >= LID_OPEN_TIME):
            setServo(lidServo, LID_CLOSED)
            lidOpen = False

        # Lock or unlock service lid if the state requested by the server changed (First iteration will always lock)
        if serviceLocked and not serviceWasLocked:
            setServo(serviceServo, SERVICE_LOCKED)
            serviceWasLocked = serviceLocked
        elif not serviceLocked and serviceWasLocked:
            setServo(serviceServo, SERVICE_UNLOCKED)
            serviceWasLocked = serviceLocked

        # # Display system status
        # clearScreen()
        # print("WasteWatch Hardware Demo")
        # print("Press Ctrl+C to exit\n")
        # print("Trash Level: ", "Full" if trashLevel == TRASH_LEVELS[2] else ("Half empty" if trashLevel == TRASH_LEVELS[1] else "Empty"))
        # print("User Detected: ", proxSens.is_pressed)
        # timeoutStatus = "" if (time() - lidTimestamp >= LID_OPEN_TIME) else "\t[ " + str(round(7 - (time() - lidTimestamp), 1)) + "s ]"
        # if lidBlockedOverride:
        #     print("Lid Status: ", "Blocked (overridden by admin)")
        # else:
        #     print("Lid Status: ", "Blocked (trash full)" if  lidBlocked else ("Open" if lidOpen else "Closed"), timeoutStatus)
        # print("Service Lid: ", "Locked" if serviceLocked else "Unlocked")
        
        # Wait so the loop doesn't run too fast
        sleep(0.10)
except KeyboardInterrupt:
    print("Stopping (User interrupt)...")
finally:
    # Stop the mqtt connection and exit
    client.loop_stop()
    client.disconnect()
    print("Stopped")

