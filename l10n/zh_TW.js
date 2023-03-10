OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "偵測到新的帳號登入位置",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "偵測到您帳號有新的帳戶登入活動。IP 位址 %s 被歸類為可疑。如果是您本人的登入活動，您可以忽略此訊息。否則您應變更密碼。",
    "Suspicious Login" : "可疑的帳號登入活動",
    "More information ↗" : "更多資訊 ↗",
    "You can get more info by pressing the button which will open %s and show info about the suspicious IP-address." : "您可以透過按下按鈕開啟 %s 並顯示關於可疑 IP 地址的資訊來取得更多資訊。",
    "New login detected" : "偵測到新的帳號登入活動",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "偵測並警告登入 Nextcloud 的可疑 IP\n\t",
    "Suspicious login detection" : "偵測到可疑的帳號登入活動",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "此站台啟用了可疑的帳號登入活動應用程式。其將追蹤成功登入帳號的 IP 位置，並建置分類器，若新帳號登入來自可疑的 IP 位置，則該分類器將會發出警告。",
    "Training data statistics" : "訓練資料統計分析",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "目前此應用程式已取用 {total} 個登入紀錄（包含客戶端連線），其中 {distinct} 個是不同的 (IP, UID) 元素組合。",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "分類器模型統計分析",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "尚無訓練好的分類器模型。這很可能是因為您剛啟用此應用程式。因為訓練模型需要品質良好的資料，因此應用程式將會等待，直到取得至少{days}天的登入資訊為止。",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "在評估模型的過程中，最新模型（已訓練{time}）已顯示捕獲所有可疑登入的 {recall}%（擷取紀錄），而被歸類為可疑登入的 {precision}% 是確實可疑的（準確度），您可以瀏覽下方顯示的視覺化歷史模型表現。",
    "Precision" : "準確度",
    "Recall" : "擷取紀錄"
},
"nplurals=1; plural=0;");
