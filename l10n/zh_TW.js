OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "偵測到新的帳號登入位置",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "偵測到您帳號有新的帳戶登入活動。此IP位址%s歸類為可疑。 如果是您本人的登入活動，可以忽略此訊息。否則您應更改密碼。",
    "Suspicious Login" : "可疑的帳號登入活動",
    "More information ↗" : "更多資訊 ↗",
    "You can get more info by pressing the button which will open %s and show info about the suspicious IP-address." : "您可以透過按下按鈕開啟 %s 並顯示關於可疑 IP 地址的資訊來取得更多資訊。",
    "New login detected" : "偵測到新的帳號登入活動",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "對關於IP來源登入Nextcloud活動進行偵測與警告",
    "Suspicious login detection" : "偵測到可疑的帳號登入活動",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "此為偵測可疑帳號登入活動的實用App。它將追蹤成功登錄帳戶的IP位址，並構建分類器，如果新帳號登入來自可疑IP位址，則該分類器將發出警示。",
    "Training data statistics" : "訓練資料統計分析",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "目前此App已取用{total}個登入紀錄(包括客戶端的連線)，其中{distinct}個是不同的（IP，UID）元素組。",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "分類器模型統計分析",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "尚未訓練分類器模型。 這很可能因為您剛剛啟用此應用程序。由於訓練模型需要品質良好的數據資料，因此該應用程序將等待，直到獲取到至少{days}天的登錄信息為止。",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "在評估模型過程中，已顯示最新模型（經過{time}的訓練）已獲取新的（或提取過往的）所有可疑登錄信息中的{recall}％，而被分類為可疑登錄紀錄的{precision}％是確實可疑的（精確度）。 您可以瀏覽下方顯示的視覺化歷史模型表現。",
    "Precision" : "準確度",
    "Recall" : "提取紀錄"
},
"nplurals=1; plural=0;");
