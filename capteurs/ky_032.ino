#include <DHT.h>
DHT dht(4, DHT11);
void setup() {
 Serial.begin(9600);
 dht.begin();
 Serial.println("Démarrage capteur DHT11...");
}
  
void loop() {
 delay(2000);
 float humidite = dht.readHumidity();
 float temperature = dht.readTemperature()
 if (isnan(humidite) || isnan(temperature)) {
  Serial.println("Erreur de lecture du capteur !");
  return;
 }

 Serial.print("Humidité : ");
 Serial.print(humidite);
 Serial.println(" %");
 Serial.print("Température : ");
 Serial.print(temperature);
 Serial.println(" °C");
 Serial.println("----------");
}