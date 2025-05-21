OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "Otkrivena je nova lokacija prijave",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "Otkrivena je nova prijava u vaš račun. IP adresa %s klasificirana je kao sumnjiva. Ako ste to bili vi, možete zanemariti ovu poruku. U suprotnom biste trebali promijeniti zaporku.",
    "Suspicious Login" : "Sumnjiva prijava",
    "New login detected" : "Otkrivena je nova prijava",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "Otkrijte i upozorite na sumnjive IP adrese koje se prijavljuju u Nextcloud\n\t",
    "Suspicious login detection" : "Otkrivanje sumnjivih prijava",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "Aplikacija za otkrivanje sumnjivih prijava omogućena je u ovoj instanci. Aplikacija prati IP adrese s kojih se korisnici uspješno prijavljuju i gradi klasifikator koji upozorava ako nova prijava dolazi sa sumnjive IP adrese.",
    "Training data statistics" : "Statistika prikupljenih podataka",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "Do sada je aplikacija zabilježila {total} prijava (uključujući uspostavljene veze s klijentima) od kojih je {distinct} zasebnih (IP, UID) n-torki.",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "Statistika modela klasifikatora",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "Još uvijek nije uvježban model klasifikatora. To najvjerojatnije znači da ste tek nedavno omogućili aplikaciju. Budući da uvježbavanje modela zahtijeva dobre podatke, aplikacija čeka dok se ne zabilježe prijave tijekom najmanje {days} dana.",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "Tijekom evaluacije, najnoviji model (uvježban kroz {time}) zabilježi {recall} % svih sumnjivih prijava (opoziv), dok je {precision} % prijava klasificiranih kao sumnjivo zaista i sumnjivo (preciznost). U nastavku možete vidjeti vizualni prikaz povijesne učinkovitosti modela.",
    "Precision" : "Preciznost",
    "Recall" : "Opoziv"
},
"nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;");
