OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "检测到新的登录地点",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "检测到您账号的一个新的登录行为。IP地址 %s 被归类为可疑。如果这是您自己，您可忽略此消息。否则您应该修改您的密码。",
    "Suspicious Login" : "可疑登录行为",
    "New login detected" : "检测到新的登录行为",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "检测并警告登录到 Nextcloud 的可疑 IP 地址\n\t",
    "Suspicious login detection" : "可疑登录行为检测",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "可疑登录行为应用在此实例上被启用。它将跟踪成功登录用户的源IP地址并创建一个分类器，用于对来自可疑IP地址的新登录行为发出警告。",
    "Training data statistics" : "训练数据统计",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "到目前为止应用已捕获 {total} 个登录行为（包括客户端连接），其中 {distinct} 个为显著的 (IP, UID) 元组。",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "分类器模型统计",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "还没有训练过的分类器模型。这可能说明您最近才启用此应用。因为模型训练需要好的数据，应用将在捕获至少 {days} 天的登录行为之后才会开始训练。",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "在评价过程中，最新的模型（训练 {time} 次）捕获到全部可疑登录行为（召回率）中的 {recall}%，被归类为可疑的登录行为中，{precision}%是确实可疑的（精准率）。下面您将看到历史模型性能的可视化图像。",
    "Precision" : "精准率",
    "Recall" : "召回率"
},
"nplurals=1; plural=0;");
