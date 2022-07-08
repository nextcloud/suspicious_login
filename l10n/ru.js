OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "Зарегистрировано новое местоположение при входе в учётную запись",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "Зарегистрирован новый вход в ваш аккаунт. IP-адрес %s считается подозрительным. Если это были вы, то можете проигнорировать это сообщение. Иначе, вам лучше сменить пароль.",
    "Suspicious Login" : "Подозрительный вход",
    "More information ↗" : "Дополнительная информация ↗",
    "You can get more info by pressing the button which will open %s and show info about the suspicious IP-address." : "Вы можете получить больше информации, нажав на кнопку, которая откроет %s и покажет информацию о подозрительном IP-адресе.",
    "New login detected" : "Зарегистрирован новый вход",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "Регистрировать и предупреждать о входах в Nextcloud с подозрительных IP-адресов\n\t",
    "Suspicious login detection" : "Обнаружение подозрительных входов",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "Приложение обнаружения подозрительных входов активировано для этого сервера. Оно будет отслеживать IP-адреса пользователей, которые успешно авторизовались в системе, и создавать классификатор, предупреждающий об авторизации с подозрительных IP-адресов.",
    "Training data statistics" : "Статистика обучающих данных",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "До настоящего времени приложение отследило {total} входов в систему (включая клиентские соединения), из которых {distinct) являются различными кортежами (IP, UID).",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "Статистика классификационной модели",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "Ни одна классификационная модель еще не обучена. Это означает, что вы, скорее всего, недавно включили приложение. Так как обучение модели требует наличия надёжных данных, приложение будет ожидать до тех пор, пока не будут отслежены входы в систему по крайней мере за {days} дней.",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "Во время оценки, последняя модель (обучена {time}) показала, что она отследила {recall}% всех подозрительных входов в систему (полнота), а {precision}% входов, классифицированных моделью как подозрительные, действительно являются таковыми (точность). Ниже вы видите временную диаграмму производительности модели.",
    "Precision" : "Точность",
    "Recall" : "Полнота"
},
"nplurals=4; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : n%10==0 || (n%10>=5 && n%10<=9) || (n%100>=11 && n%100<=14)? 2 : 3);");
