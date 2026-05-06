bool urgence = false; 
void setup() {
 pinMode(2, OUTPUT);
 pinMode(0, INPUT_PULLUP);
 Serial.begin(9600); 
} 

void ok() {
 for(int i=0; i<3; i++) {
 if(digitalRead(0) == LOW) return; // sort si bouton a
    digitalWrite(2, HIGH); delay(600); digitalWrite(2, LOW); delay(200);
 }
 delay(400);
 if(digitalRead(0) == LOW) return;
 digitalWrite(2, HIGH); delay(600); digitalWrite(2, LOW); delay(200); 
 if(digitalRead(0) == LOW) return;
 digitalWrite(2, HIGH); delay(200); digitalWrite(2, LOW); delay(200); 
 if(digitalRead(0) == LOW) return;
 digitalWrite(2, HIGH); delay(600); digitalWrite(2, LOW); delay(200);
 delay(2000); 
}

void sos() {
 for(int i=0; i<3; i++) {
 if(digitalRead(0) == LOW) return; 
 digitalWrite(2, HIGH); delay(200); digitalWrite(2, LOW); delay(200);
  }
  delay(400); 
  for(int i=0; i<3; i++) {
    if(digitalRead(0) == LOW) return; 
    digitalWrite(2, HIGH); delay(600); digitalWrite(2, LOW); delay(200);
  }
  delay(400); 
  for(int i=0; i<3; i++) {
    if(digitalRead(0) == LOW) return; 
    digitalWrite(2, HIGH); delay(200); digitalWrite(2, LOW); delay(200);
  }
  delay(1000); 
}
void loop() {
  if(digitalRead(0) == LOW) {
    urgence = !urgence; 
    delay(500);        
    if(urgence) {
      Serial.println("URGENCE - SOS activé !");
    } else {
      Serial.println("Tout va bien - OK !");
    }
  }
   if(urgence) {
    sos();
  } else {
 ok();
  }
}