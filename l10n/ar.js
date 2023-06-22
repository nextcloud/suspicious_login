OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "لم يُمكن تحديد الموقع الذي تمّ منه الدخول",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "تمّ اكتشاف دخول جديد إلى حسابك. تمّ اعتبار العنوان %s مشبوهاً. إذا كنت أنت الداخل، يمكنك تجاهل هذه الرسالة؛ و إلّا يلزمك تغيير كلمة السر.",
    "Suspicious Login" : "دخولٌ مشبوهٌ",
    "More information ↗" : "لمعلوماتٍ أكثر ↗",
    "You can get more info by pressing the button which will open %s and show info about the suspicious IP-address." : "يٌمكنك الحصول على معلومات أكثر بالضغط على الزر الذي سيفتح %s و يعرض عليك معلوماتٍ عن عنوان IP المشبوه.",
    "New login detected" : " لم يتم كشف أي دخولٍ جديدٍ",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "أكتشف و بلّغ عن أي عناوين مشبوهة تتم منها محاولة الدخول إلى نكست كلاود\n\t",
    "Suspicious login detection" : "كشف محاولة دخولٍ مشبوهٍ",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "تطبيق كشف الدخول المشبوه suspicious login تمّ تفعيله على هذا الخادوم؛ وهو سوف يقوم يمتابعة عنوان IP التي يدخل منها المستخدمون بنجاحٍ إى نكست كلاود و يقوم بتصنيفها بحيث يستطيع التحذير من أي محاولة دخولٍ تأتي من عنوان IP مشبوهٍ.",
    "Training data statistics" : "تدريب البيانات الإحصائية",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "حتى الآن، تمكّن التطبيق من التقاط {total} تسجيلة دخول ( بما في ذلك توصيلات الأجهزة العميلة). و كان منها {distinct} اتصالاً من توليفات (IP, UID) مع تجاهل التكرارات. ",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "إحصائيّات نموذج مُصنّف",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "لم يتم تدريب أي نموذجٍ مُصنِّفٍ بعدُ. و هذا يعني غالباً أنك قد مكّنت التطبيق للتّوّ. و بالنظر إلى أن التدريب يتطلب بياناتٍ جيدةٍ، فإن التطبيق سينتظر حتى يتم التقاط عمليات دخولٍ لعدد {days} يوماً على الأقل.  ",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "خلال التقييم، أظهر النموذج الأخير ( المُدرَّب {time}) أنّه استطاع التقاط {recall}% من جملة محاولات الدخول المشبوهة (تذكُّر)، بينما تبيّن أن {precision}% من المحاولات المُصنّفة مشبوهةٌ هي مشبوهةُ فعلاً (دقّة).  تجد أدناه شكلاً يوضح أداء النموذج تاريخيّاً.",
    "Precision" : "دقّة",
    "Recall" : "تذكُّر"
},
"nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 && n%100<=99 ? 4 : 5;");
