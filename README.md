# Maison Connectée ESP32

Projet de stage réalisé à l'IETR (Institut d'Électronique et des Technologies du numéRique) de Nantes.

## Description
Système de maison connectée basé sur un ESP32 avec détection d'obstacles, de flammes et relevé de température/humidité. Les données sont envoyées via WiFi vers une base de données MySQL et affichées sur une interface web accessible à distance via ngrok.

## Capteurs utilisés
- KY-032 : Détecteur infrarouge d'obstacle
- KY-026 : Détecteur de flamme
- KY-015 : Température et humidité (DHT11)
- LED : Indicateur visuel

## Technologies
- Arduino C++ (ESP32)
- PHP / HTML / CSS
- MySQL / phpMyAdmin
- ngrok

## Structure du projet
- capteurs/ : Codes individuels de chaque capteur
- projet-final/ : Code complet du projet maison connectée
- interface-web/ : Interface PHP de visualisation des données
