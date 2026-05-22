#include <DHT.h>        // Bibliothèque capteur température/humidité
#include <WiFi.h>       // Bibliothèque connexion WiFi
#include <HTTPClient.h> // Bibliothèque envoi requêtes HTTP
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h" // Pour désactiver le brownout detector

#define LED_PIN 2       // LED sur GPIO 2
#define OBSTACLE_PIN 27 // KY-032 sur GPIO 27
#define FLAME_PIN 14    // KY-026 sur GPIO 14
#define DHTPIN 4        // KY-015 sur GPIO 4
#define DHTTYPE DHT11   // Type de capteur DHT

const char* ssid = "TestESP";        // Nom du réseau WiFi
const char* password = "12345678";   // Mot de passe WiFi
const char* serverURL = "http://10.38.120.7/maison_ESP32/insert.php"; // URL serveur PHP

DHT dht(DHTPIN, DHTTYPE); // Initialisation du capteur DHT

// Variables stockant les dernières valeurs envoyées
// pour éviter les doublons dans la BDD
float derniere_temp = -1;
float derniere_hum = -1;
int dernier_obstacle = -1;
int derniere_flamme = -1;

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); // Désactive le brownout
  Serial.begin(115200);
  delay(2000);

  // Configuration des broches
  pinMode(LED_PIN, OUTPUT);
  pinMode(OBSTACLE_PIN, INPUT_PULLUP); // Résistance interne activée
  pinMode(FLAME_PIN, INPUT);
  dht.begin();

  // Connexion WiFi
  WiFi.disconnect(true);
  delay(1000);
  WiFi.mode(WIFI_STA); // Mode client WiFi
  delay(500);
  Serial.println("Connexion WiFi...");
  WiFi.begin(ssid, password);

  // Attente connexion (30 tentatives max)
  int t = 0;
  while (WiFi.status() != WL_CONNECTED && t < 30) {
    delay(500);
    Serial.print(".");
    t++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi connecté !");
    Serial.print("IP ESP32 : ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("Échec WiFi");
  }
}

void loop() {
  // Lecture des capteurs
  int obstacle = digitalRead(OBSTACLE_PIN); // LOW = obstacle détecté
  int flamme = digitalRead(FLAME_PIN);      // LOW = flamme détectée
  float humidite = dht.readHumidity();
  float temperature = dht.readTemperature();

  // Contrôle LED selon obstacle
  if (obstacle == HIGH) {
    digitalWrite(LED_PIN, HIGH);
  } else {
    digitalWrite(LED_PIN, LOW);
  }

  // Affichage Serial Monitor
  Serial.println("------ DONNÉES ------");
  Serial.println(obstacle == HIGH ? "Obstacle : NON" : "Obstacle : OUI");
  Serial.println(flamme == LOW ? "Flamme : OUI ⚠️" : "Flamme : NON");

  if (!isnan(temperature) && !isnan(humidite)) {
    Serial.print("Température : "); Serial.print(temperature); Serial.println(" °C");
    Serial.print("Humidité : "); Serial.print(humidite); Serial.println(" %");

    // Conversion en 0/1 pour la BDD
    int obs_val = (obstacle == LOW ? 1 : 0);
    int flamme_val = (flamme == LOW ? 1 : 0);

    // Envoi seulement si une valeur a changé
    bool changement = (temperature != derniere_temp ||
                       humidite != derniere_hum ||
                       obs_val != dernier_obstacle ||
                       flamme_val != derniere_flamme);

    if (changement && WiFi.status() == WL_CONNECTED) {
      derniere_temp = temperature;
      derniere_hum = humidite;
      dernier_obstacle = obs_val;
      derniere_flamme = flamme_val;

      // Construction URL avec paramètres GET
      HTTPClient http;
      String url = String(serverURL) +
                   "?temperature=" + String(temperature) +
                   "&humidity=" + String(humidite) +
                   "&obstacle=" + String(obs_val) +
                   "&flamme=" + String(flamme_val);

      // Envoi requête HTTP GET vers insert.php
      http.begin(url);
      int httpCode = http.GET();
      Serial.println(httpCode == 200 ? "Envoi BDD : OK" : "Envoi BDD : ERREUR");
      http.end();
    }
  } else {
    Serial.println("Erreur DHT !"); // DHT n'a pas renvoyé de valeur
  }

  Serial.println("--------------------");
  delay(1000);
}