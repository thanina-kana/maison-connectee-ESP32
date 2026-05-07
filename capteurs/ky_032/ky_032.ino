void setup() {
  pinMode(2, OUTPUT);
  pinMode(27, INPUT_PULLUP);
  Serial.begin(115200);
}

void loop() {
  if (digitalRead(27) == LOW) {
    digitalWrite(2, HIGH);
    Serial.println("Obstacle détecté !");
  } else {
    digitalWrite(2, LOW);
    Serial.println("Aucun obstacle.");
  }
  delay(100);
}