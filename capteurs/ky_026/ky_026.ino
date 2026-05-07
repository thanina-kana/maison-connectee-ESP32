void setup() {
  Serial.begin(115200);
  pinMode(14, INPUT);
}

void loop() {
  int flamme = digitalRead(14);
  if (flamme == LOW) {
    Serial.println("Flamme détectée !");
  } else {
    Serial.println("Pas de flamme.");
  }
  delay(300);
}