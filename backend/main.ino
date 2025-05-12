#include <Wire.h>
#include <ESP8266WiFi.h>
#include <SoftwareSerial.h>
#include "nodemcu_arduino_pins.h"
#include "iot_functions.h"
#include "parse_message.h"
#include "config.h"

// Software Serial Setup
SoftwareSerial softSerial(D5, D1); // RX, TX

// Constants and Global Variables
const char* host = SERVER_HOST;
String location = "0.0,0.0", sleep_status = "Awake", alcohol_status = "Sober";
unsigned long last_data_upload_time, time_entered, sleep_time;
int time_set, eye_close, speed, accident_occured;

// Sensor Pin Assignments
int ACCIDENT_SENSOR = D2; // Vibration sensor
int ALCOHOL_SENSOR = D6;
int EYE_BLINK_SENSOR = D7;
int SPEED_SENSOR = A0;
int HORN_BUTTON = D3; // Horn button input
int BUZZER = D4;      // Buzzer output

// No-Horn Zone Configuration
bool in_no_horn_zone = false; // Determined by location or server response

// Function: NeoGSMInit
void NeoGSMInit() {
    softSerial.print("AT");
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("AT+CREG=1");  
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("AT+CSMS=1"); 
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("AT+CMGF=1"); 
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("AT+CNMI=2,2,0,0,0");
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("AT+CSCS=\"GSM\""); 
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);

    softSerial.print("ATE1");
    softSerial.write('\r');
    softSerial.println('\n');
    delay(300);
}

// Function: GetLocationFromGSM
String GetLocationFromGSM() {
    String response = "";
    softSerial.flush();
    softSerial.print("AT+CIPGSMLOC=1,1\r\n");
    delay(2000); // Wait for response

    while (softSerial.available()) {
        char c = softSerial.read();
        response += c;
    }

    // Parse response for location (format: +CIPGSMLOC: 0,longitude,latitude,date,time)
    int locIndex = response.indexOf("+CIPGSMLOC:");
    if (locIndex != -1) {
        int comma1 = response.indexOf(',', locIndex);
        int comma2 = response.indexOf(',', comma1 + 1);
        int comma3 = response.indexOf(',', comma2 + 1);
        if (comma2 != -1 && comma3 != -1) {
            String longitude = response.substring(comma1 + 1, comma2);
            String latitude = response.substring(comma2 + 1, comma3);
            return latitude + "," + longitude;
        }
    }
    return "0.0,0.0"; // Default if location fetch fails
}

// Function: SendMsg
void SendMsg(String number, String msg) {
    softSerial.print("AT+CMGS=\"");
    softSerial.print(number);
    softSerial.print("\"");
    softSerial.print('\r');
    delay(200);
    softSerial.print(msg);
    softSerial.write(26); // Send CTRL+Z
    delay(200);
}

// Function: CheckNoHornZone
void CheckNoHornZone() {
    // Simulate checking with server or use hardcoded zones
    // For simplicity, assume location-based check
    String url = "/checkNoHornZone.php?location=" + location;
    String response = requestURL(host, url);
    
    in_no_horn_zone = (response.indexOf("true") != -1);
}

// Function: setup
void setup() {
    Serial.begin(9600);
    delay(10);
    softSerial.begin(9600);

    pinMode(ACCIDENT_SENSOR, INPUT);
    pinMode(ALCOHOL_SENSOR, INPUT);
    pinMode(EYE_BLINK_SENSOR, INPUT);
    pinMode(HORN_BUTTON, INPUT_PULLUP); // Pull-up for button
    pinMode(BUZZER, OUTPUT);
    digitalWrite(BUZZER, LOW); // Buzzer off initially

    initWiFi(WIFI_SSID, WIFI_PASSWORD, 1);

    requestURL(host, "/setVariables.php?keywords>connection_status=1");

    NeoGSMInit();
}

// Function: loop
void loop() {
    // a) Speed Measurement
    speed = analogRead(SPEED_SENSOR) / 9;

    // b) Alcohol Level Detection
    if (digitalRead(ALCOHOL_SENSOR) == 1) {
        delay(300);
        alcohol_status = "Sober";
    } else {
        delay(300);
        alcohol_status = "Drunk";
        digitalWrite(BUZZER, HIGH); // Alert driver
        delay(1000);
        digitalWrite(BUZZER, LOW);
    }

    // c) Eye Blink / Sleep Detection
    if (digitalRead(EYE_BLINK_SENSOR) == 1 && eye_close == 0) {
        eye_close = 1;
        sleep_time = millis();
    } else if (digitalRead(EYE_BLINK_SENSOR) == 0) {
        eye_close = 0;
        sleep_time = millis();
    }

    if (millis() > sleep_time + 3000 && eye_close == 1) {
        sleep_time = millis();
        sleep_status = "Sleeping";
        eye_close = 0;
        digitalWrite(BUZZER, HIGH); // Alert driver
        delay(1000);
        digitalWrite(BUZZER, LOW);
    } else if (eye_close == 0) {
        sleep_status = "Awake";
    }

    // d) Accident Detection and Handling
    if (digitalRead(ACCIDENT_SENSOR) && accident_occured != 1) {
        delay(300);

        // Fetch location from GSM module
        location = GetLocationFromGSM();

        String url = "/addBlock.php?data=";
        url += String(speed);
        url += "*";
        url += location;
        url += "*";
        url += alcohol_status;
        url += "*";
        url += sleep_status;
        url += "*";
        url += MCU_ID;

        Serial.println(url);

        requestURL(host, url);
        SendMsg(PHONE_NUMBER, "Accident at: \n " + location + " (from " + MCU_ID + ")");

        accident_occured = 1;
        digitalWrite(BUZZER, HIGH); // Alert environment
        delay(2000);
        digitalWrite(BUZZER, LOW);
    }

    // e) Horn Control
    if (digitalRead(HORN_BUTTON) == LOW) { // Button pressed
        CheckNoHornZone();
        if (!in_no_horn_zone) {
            // Activate horn (simulated here, actual horn control depends on hardware)
            Serial.println("Horn activated");
        } else {
            digitalWrite(BUZZER, HIGH); // Warn driver
            delay(500);
            digitalWrite(BUZZER, LOW);
            Serial.println("Horn disabled in no-horn zone");
        }
    }

    // f) Final Delay
    delay(10);
}