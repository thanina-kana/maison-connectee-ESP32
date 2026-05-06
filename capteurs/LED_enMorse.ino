void setup() {
 pinMode(2, OUTPUT);
 pinMode(27, INPUT_PULLUP);
 Serial.begin(9600);

}

void loop() {
 if (digitalRead(27) == HIGH) {
  digitalWrite(2, HIGH);
  Serial.println("Rien détecté");
 } else {
  digitalWrite(2, LOW);
  Serial.println("Obstacle ! LED allumée");
 }
 delay(100);
}