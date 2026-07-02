/*
  JSON Object

  This sketch demonstrates how to use various features
  of the Official Arduino_JSON library, in particular for JSON objects.

  This example code is in the public domain.
*/

#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h> 
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <WiFiManager.h>
#include <ArduinoJson.h>


WiFiManager wifiManager;

const char *ssid =  "ForsatTalab";     // replace with your wifi ssid and wpa2 key
const char *pass =  "00981920";
String chipId = String(ESP.getChipId());

IPAddress local_IP(192, 168, 37, 2);
IPAddress gateway(192, 168, 37, 250);
IPAddress subnet(255, 255, 255, 0);
IPAddress primaryDNS(8, 8, 8, 8);
IPAddress secondaryDNS(8, 8, 4, 4);

String serverName = "http://192.168.37.23:8000";

//This must change for NewVersion
String currentVersion = "1.1.15";

bool lastState;
void setup() {
  pinMode(5, OUTPUT);
  digitalWrite(5, HIGH);
  
  pinMode(4, OUTPUT);
  Serial.begin(115200);
  while (!Serial);
  // Configures static IP address
  //if (!WiFi.config(local_IP, gateway, subnet, primaryDNS, secondaryDNS)) {
  //  Serial.println("STA Failed to configure");
  //}

  //Serial.println("Connecting to ");
  //Serial.println(ssid); 
  //WiFi.begin(ssid, pass); 
  //while (WiFi.status() != WL_CONNECTED) 
  //{
  //  digitalWrite(5, HIGH);
  //  Serial.print(".");
  //}

  
  
wifiManager.setSTAStaticIPConfig(
    local_IP,
    gateway,
    subnet,
    primaryDNS
);
  wifiManager.autoConnect("NodeMCU-Setup");

  wifiConectedNotice();

  Serial.println("");
  Serial.println("WiFi connected"); 
  //lastState=nowState();
  sendMessageToBot("started");

}

void loop() {  
  delay(3000);

  Serial.print("chip ID is:" +chipId);
  String token;
  bool isAvailableNewVersion;
  token=getToken(chipId);
  Serial.println("Token is : "+token);
  isAvailableNewVersion=isNewVersionAvailable(token, chipId);
  if(isAvailableNewVersion){
    Serial.println("An Update is available");
    
    updateAvailableNotice();
    updateFirmware(chipId,token);

  }
  
  Serial.println(isNewVersionAvailable(token, chipId));

}
void waitingForWifiNotice(){
    digitalWrite(4, HIGH);
    delay(500);
    digitalWrite(4, LOW);
    delay(600);
}
void wifiConectedNotice(){
  delay(600);
  digitalWrite(4, HIGH);
  delay(100);
  digitalWrite(4, LOW);
  delay(100);
  digitalWrite(4, HIGH);
  delay(100);
  digitalWrite(4, LOW);
}
void changeStateAlert(){
  digitalWrite(4, HIGH);
  delay(500);
  digitalWrite(4, LOW);
}
void updateAvailableNotice(){
  digitalWrite(4, HIGH);
  delay(5000);
  digitalWrite(4, LOW);
}
//bool nowState(){
//  WiFiClientSecure client;
//  client.setInsecure(); //the magic line, use with caution
//  client.connect(serverName, 443);
//  HTTPClient http;
//  http.begin(client, serverName);
//
//  String payload;
//  if (http.GET() == HTTP_CODE_OK)    
//  payload = http.getString(); 
//  Serial.print(payload);
//  if(payload=="1")
//  {
//    Serial.print("HIGHT");
//    return false;
//  }
//  if(payload=="0")
//  {
//    Serial.print("LOW");
//    return true;
//  } 
//  return lastState;
//}
bool sendMessageToBot(String message)
{
  String urlServer="https://www.mobinaghashahi.ir/watering/sendMessageToBot.php?key=Mobin.mobin7060&message="+message;
  WiFiClientSecure client;
  client.setInsecure(); //the magic line, use with caution
  client.connect(urlServer, 443);
  HTTPClient http;
  Serial.print("send");
  http.begin(client, urlServer);

  if (http.GET() == HTTP_CODE_OK)
    return true; 
  else
    return false;
  
  
}


bool isNewVersionAvailable(const String& token, const String& uuid)
{
    WiFiClient client;
    HTTPClient http;

    String url = serverName+"/api/v1/update/get_last_version";

    if (!http.begin(client, url))
    {
        Serial.println("Failed to connect.");
        return false;
    }

    http.addHeader("Authorization", "Bearer " + token);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String body = "uuid=" + uuid;

    int httpCode = http.POST(body);

    if (httpCode != HTTP_CODE_OK)
    {
        Serial.printf("HTTP Error: %d\n", httpCode);
        http.end();
        return false;
    }

    String response = http.getString();
    http.end();

    Serial.println(response);

    JsonDocument doc;
    DeserializationError error = deserializeJson(doc, response);

    if (error)
    {
        Serial.print("JSON Error: ");
        Serial.println(error.c_str());
        return false;
    }

    if (!doc.is<JsonArray>() || doc.size() == 0)
    {
        Serial.println("No update information found.");
        return false;
    }

    int updateInstalled = doc[0]["update_installed"];

    return updateInstalled == 0;
}

bool updateFirmware(String chipId,const String& token) {

  WiFiClient client;
  HTTPClient http;

  String url = "http://192.168.37.200/" + chipId + ".bin";

  Serial.println("Checking firmware file...");
  Serial.println(url);

  // -----------------------------
  // 1. CHECK FILE EXISTS (HEAD)
  // -----------------------------
  http.begin(client, url);

  int httpCode = http.sendRequest("HEAD");

  Serial.print("HTTP Code: ");
  Serial.println(httpCode);

  http.end();

  if (httpCode != HTTP_CODE_OK) {
    Serial.println("Firmware file not found on server!");
    return false;
  }

  Serial.println("Firmware found. Starting update...");
  sendInstalledUpdate(chipId,token);
  // -----------------------------
  // 2. START OTA UPDATE
  // -----------------------------
  t_httpUpdate_return ret = ESPhttpUpdate.update(client, url);

  switch (ret) {

    case HTTP_UPDATE_FAILED:
      Serial.printf("UPDATE FAILED: %s\n",
        ESPhttpUpdate.getLastErrorString().c_str());
      return false;

    case HTTP_UPDATE_NO_UPDATES:
      Serial.println("No update available.");
      return false;

    case HTTP_UPDATE_OK:
      Serial.println("UPDATE SUCCESSFUL!");
      Serial.println("Device will restart...");
      return true; // بعدش ریست می‌شود
  }

  return false;
}

String getToken(const String& uuid)
{
    WiFiClient client;
    HTTPClient http;

    String url = serverName+"/api/v1/get_token";

    if (!http.begin(client, url))
    {
        Serial.println("Failed to connect");
        return "";
    }

    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String body = "uuid=" + uuid;

    int httpCode = http.POST(body);

    if (httpCode != HTTP_CODE_OK)
    {
        Serial.printf("HTTP Error: %d\n", httpCode);
        http.end();
        return "";
    }

    String response = http.getString();
    http.end();

    Serial.println(response);

    JsonDocument doc;
    DeserializationError error = deserializeJson(doc, response);

    if (error)
    {
        Serial.println("JSON Parse Error");
        return "";
    }

    return doc["token"].as<String>();
}

void sendInstalledUpdate(String uuid,const String& token) {

  WiFiClient client;
  HTTPClient http;

  String url = serverName+"/api/v1/update/installed_update";

  http.begin(client, url);

  // -----------------------------
  // Headers
  // -----------------------------
  http.addHeader("Authorization", "Bearer " + token);
  http.addHeader("Content-Type", "multipart/form-data; boundary=----iot");

  // -----------------------------
  // Body (form-data)
  // -----------------------------
  String body =
    "------iot\r\n"
    "Content-Disposition: form-data; name=\"uuid\"\r\n\r\n" +
    uuid +
    "\r\n------iot--\r\n";

  int httpCode = http.POST(body);

  Serial.print("HTTP Code: ");
  Serial.println(httpCode);

  String response = http.getString();
  Serial.println("Response:");
  Serial.println(response);

  http.end();
}
