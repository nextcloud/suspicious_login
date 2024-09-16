OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "Rilevata nuova posizione di accesso",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "È stato rilevato un nuovo accesso al tuo account. L'indirizzo IP 1 %s è stato classificato come sospetto. Se sei stato tu, puoi ignorare questo messaggio. Altrimenti dovresti cambiare la password.",
    "Suspicious Login" : "Accesso sospetto",
    "New login detected" : "Rilevato un nuovo accesso",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "Rileva e avvisa gli IP sospetti che accedono a Nextcloud\n\t",
    "More information ↗" : "Maggiori informazioni ↗",
    "You can get more info by pressing the button which will open %s and show info about the suspicious IP-address." : "Puoi avere maggiori informazioni premendo il pulsante che aprirà %s con informazioni sull'indirizzo IP sospetto.",
    "Suspicious login detection" : "Rilevamento di accessi sospetti",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "L'applicazione degli accessi sospetti è abilitata in questa istanza. Tiene traccia degli indirizzi IP da cui gli utenti accedono con successo e crea un classificatore che avvisa se un nuovo accesso proviene da un indirizzo IP sospetto.",
    "Training data statistics" : "Statistiche dei dati sull'addestramento",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "Finora l'applicazione ha catturato {total} accessi (incluse le connessioni client), di cui {distinct} sono tuple distinte (IP, UID).",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "Statistiche del modello di classificazione",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "Nessun modello di classificazione è ancora stato addestrato. Questo probabilmente significa che hai appena abilitato l'applicazione di recente. Poiché l'addestramento di un modello richiede buoni dati, l'applicazione attende fino a quando non sono stati catturati gli accessi di almeno {days} giorni.",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "Durante la valutazione, l'ultimo modello (addestrato {time}) ha dimostrato di catturare {recall}% di tutti gli accessi sospetti (recall), mentre il {precision}% degli accessi classificati come sospetti sono effettivamente sospetti (precision). Di seguito viene mostrata una visualizzazione delle prestazioni storiche del modello.",
    "Precision" : "Precisione",
    "Recall" : "Richiamo"
},
"nplurals=3; plural=n == 1 ? 0 : n != 0 && n % 1000000 == 0 ? 1 : 2;");
