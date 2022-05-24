OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "Foi detectada uma nova localização do inicio de sessão",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "Foi detetado um novo acesso à sua conta. O endereço IP %s foi classificado como suspeito. Se foi você a aceder, pode ignorar esta mensagem. Caso contrário, deve proceder à alteração da sua palavra-passe.",
    "Suspicious Login" : "Acesso suspeito",
    "New login detected" : "Detetado novo acesso",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "Detetar e avisar sobre acessos ao Nextcloud a partir de endereços IP suspeitos ",
    "Suspicious login detection" : "Deteção de acessos suspeitos",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "A aplicação de acessos suspeitos está ativa nesta instância. Regista os endereços IP dos utilizadores que acederam e classifica-os de forma a avisar se o acesso provém de um endereço IP suspeito.",
    "Training data statistics" : "Estatísticas sobre os dados recolhidos",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "Até ao momento, a aplicação capturou {total} acessos (inclui ligações cliente), dos quais {distinct} são  registos (IP, UID) distintos.",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "Estatísticas do módulo que classifica os acessos",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "O módulo que classifica ainda não recolheu dados. Provavelmente significa que a aplicação foi ativada muito recentemente. Dado que é necessária bastante informação para a aprendizagem, a aplicação espera que todos os acessos dos últimos {days} sejam registados.",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "Durante a avaliação, o último modelo  (processou {time}) mostrou que capturou  {recall}% do total de acessos suspeitos (reprocessar), e uma percentagem de {precision}% sobre os acessos suspeitos que efectivamente o eram (precisão). Abaixo é mostrado um histórico da performance do modelo.",
    "Precision" : "Precisão",
    "Recall" : "Reprocessar"
},
"nplurals=3; plural=(n == 0 || n == 1) ? 0 : n != 0 && n % 1000000 == 0 ? 1 : 2;");
