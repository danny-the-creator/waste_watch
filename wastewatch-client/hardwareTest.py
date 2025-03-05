from gpiozero import DistanceSensor, Button, LED
from time import sleep, time
import os
import select
import sys
import board
import busio
import adafruit_ads1x15.ads1115 as ADS
from adafruit_ads1x15.analog_in import AnalogIn
import gpiod
import math

# Threshold distances in cm for the trash levels (also serve as unique identifiers for logic)
TRASH_LEVELS = [80, 52, 32]
LID_OPEN_TIME = 7

# Constants for the gpiod library, logic and the servo's
CHIP = 'gpiochip0'  # The gpio chip of the RPi
PERIOD = 0.02       # 20ms  period time for Servo PWM timing
MIN_DUTY = 0.0005   # 0.5ms min duty time for Servo PWM timing
MAX_DUTY = 0.0025   # 2.5ms max duty time for Servo PWM timing

# Angles of Service Lid Servo's and Lid Servo when opened and closed (also serve as unique identifiers for logic)
LID_OPEN = 0
LID_CLOSED = 125
SERVICE_OPEN = 90
SERVICE_CLOSED = 180

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

# Converts 2 given distances to a trash level
def distance2Level(usDistance, irDistance):
    avg = (usDistance + irDistance) / 2
    return TRASH_LEVELS[2] if avg <= TRASH_LEVELS[2] else (TRASH_LEVELS[1] if avg <= TRASH_LEVELS[1] else TRASH_LEVELS[0])
    
# Sets the angle of a given servo to a given value [0, 180]
def set_angle(line, angle):
    # Calculate the target pulse for the given angle
    duty = MIN_DUTY + (MAX_DUTY - MIN_DUTY) * angle / 180
    on_time = duty
    off_time = PERIOD - on_time
    # Send the target pulse for about 1sec to the given line
    for _ in range(50):
        line.set_value(1)
        sleep(on_time)
        line.set_value(0)
        sleep(off_time)

# Open lid if it isn't yet, save timestamp (for closing) and return lid status
def openLid():
    set_angle(lidServo, LID_OPEN)
    return True

# Close lid and return lid status
def closeLid():
    set_angle(lidServo, LID_CLOSED)
    return False

# Convert IR Distance Sensor voltage to distance in cm
def voltage2Distance(voltage):
    if voltage < 0.4:
        return 100
    elif voltage > 3.3:
        return -1
    distance = 27.86 * math.pow(voltage, -1.15)
    return round(distance, 2)

# Clear the terminal screen
def clearScreen():
    os.system('cls' if os.name == 'nt' else 'clear')

# Lock the service lid and return service lid status
def lockServiceLid():
    set_angle(serviceServo, SERVICE_CLOSED)
    return False

# Unlock the service lid and return service lid status
def unlockServiceLid():
    set_angle(serviceServo, SERVICE_OPEN)
    return True

# Scan for available user input
def input_available():
    return select.select([sys.stdin], [], [], 0)[0]

# Get and return any input given by the user without blocking main loop
def non_blocking_input():
    if input_available():
        return sys.stdin.readline().rstrip()
    return None

# Set variables to default state
trashLevel = TRASH_LEVELS[0]
serviceOpen = False
lidBlocked = False
userDetected = False
lidOpen = False
lidTimestamp = 0
ledBlinking = False

# Set servo's to default state (closed)
set_angle(serviceServo, SERVICE_CLOSED)
set_angle(lidServo, LID_CLOSED)

# Start the main loop
try:
    while True:
        # Measure the distance of both distance sensors and get the trash level
        usDistance = usDistSens.distance * 100
        irDistance = voltage2Distance(irDistSens.voltage)
        trashLevel = distance2Level(usDistance, irDistance)

        # Block the lid if the trash level is full and the lid is not open
        if not lidOpen:
            lidBlocked = (trashLevel == TRASH_LEVELS[2])
        
        # Open lid if a user is present and lid is not blocked
        # if user is present and lid is blocked, indicate with LED, else turn it off
        if (not lidBlocked) and proxSens.is_pressed:
            if not lidOpen:
                lidOpen = openLid()
            lidTimestamp = time()
            led.off()
            ledBlinking = False
        elif proxSens.is_pressed and not ledBlinking:
            ledBlinking = True
            led.blink(0.3)
        else:
            if (time() - lidTimestamp < LID_OPEN_TIME) and (time() - lidTimestamp > LID_OPEN_TIME - 3) and not ledBlinking:
                ledBlinking = True
                led.blink(0.3)
            else:
                led.off()
                ledBlinking = False
                
        # Close lid if the lid open timeout has expired
        if lidOpen and (time() - lidTimestamp >= LID_OPEN_TIME):
            lidOpen = closeLid()

        # Check if user has given input to the script, if so toggle the service lid
        user_input = non_blocking_input()
        if user_input:
            if serviceOpen:
                serviceOpen = lockServiceLid()
            else:
                serviceOpen = unlockServiceLid()

        # Display system status
        clearScreen()
        print("WasteWatch Hardware Demo")
        print("Press Ctrl+C to exit\n")
        print("Trash Level: ", "Full" if trashLevel == TRASH_LEVELS[2] else ("Half empty" if trashLevel == TRASH_LEVELS[1] else "Empty"))
        print("User Detected: ", proxSens.is_pressed)
        timeoutStatus = "" if (time() - lidTimestamp >= LID_OPEN_TIME) else "\t[ " + str(round(7 - (time() - lidTimestamp), 1)) + "s ]"
        print("Lid Status: ", "Blocked" if  lidBlocked else ("Open" if lidOpen else "Closed"), timeoutStatus)
        print("Service Lid: ", "Open" if serviceOpen else "Closed")
        

        # Wait so the loop doesn't run too fast
        sleep(0.15)

except KeyboardInterrupt:
    print("\nProgram terminated by user")

finally:
    pass
